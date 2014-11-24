HAL Browser
===========

By using HAL, it gives our responses a nice, predictable structure. And while
that's *really* awesome, I want to show you something that's even *more* awesome
that we get for free by using Hal.

I'll google for "Hal Browser". If you haven't seen this before, I'm not going
to tell you want it is until we see it in action. I'll copy the Git URL.
Move into the ``web/`` directory of the project and clone this into a browser/
directory. Great!

Inside of this directory is a ``browser.html`` file - so let's surf to that
instead of our actual project. And let me show you the directory - it's just
that HTML file, some CSS and some JS. What we get is the HAL Browser. This
is an API client that knows all about dealing with HAL responses. We can
put in the URL. Let's guess that there's a battle whose id is 1 in the database.
And makes a *real* request to that URL, and we're actually getting a 404
right now, because apparently there's no battle with id 1. I'll re-run a
test that puts battles into the database so we have something to play with.

Perfect, this time if finds a battle. On the right you see the raw response
headers and raw JSON. But on the left, it's actually parsing through those
a little bit. It sees that *these* are the properties. So even though it
has this big response body on the right, the properties are *only* the ``id``,
``didProgrammerWin`` type of fields. It also parses out the links and puts
them in a nice "Links" section. And it even lets us follow that link, which
I'll do in a second. Down here, it also has the embedded resource. It shows
us that we have an embedded programmer, so let's click that and then it does
the same thing. It says: here are the properties, and even says, this embedded
resource has a link to itself.

Let's follow the link to the programmer. This actually makes a new GET request
to this URL - which is aweosme - parses out the properties, and once again,
we see the ``self`` link. So just by using HAL, we get this for free, which
makes it really easy to learn your own API and you can even expose this if
you want to your end users on your site. If you want to try our API, just
go to this URL and naviate your way around it.

Let's go to one other endpoint - ``/api/programmers``. Here, things don't
quite work out as well. The response body on the right is the same as the
properties on the left. That might not look wrong to you, but the format for
our collectio nis not correct accorindg to HAL yet.

If we look back at the HAL Primer doc, you'll see that *this* is what a collection
resource should actually look like. By teh way, we've talked about resources
like Programmer and Battle, but with the collection endpoints - like
``/api/programmers`` - the collection itself is known as a resource. So this
is actually a collection resource. And in HAL, the way this works is taht
all of the items in that collection are considered to be embedded resources.
If you imagine that this is ``/api/users``, all the users live under the
``_embedded`` key. The only true properteis - if you have any - relate to
the collection itself: things like the total number of items in the collection
or what page you're on.

And above, you'll see that this collection has pagination and it's using
the links to help the client know how to get to other pages. And we're going
to do this exact same thing later, which is awesome and it'll be really easy.

But for now, I want to get things into this structure. But first, before we
implement it, you guys know what we're going to do, we're going to update
our test. In our ``programmer.feature``, the scenario for the collection *was*
looking for a ``programmers`` property to be the array. But now, it's going
to be ``_embedded.programmers``. Let's go even one step further and say that
the first item in this should be the UnitTester. So its nickname should equal
UnitTester. This is line 84, so let's run this test first to make sure it
fails. And it does.

The HATEOAS library has its own way of dealing with collection. If you scroll
to the top of its docs, you'll see a section all about this. First of all,
don't worry about this PaginatedRepresentation thing, we're going to talk
about that later - it's not nearly as scary as it looks here. If we have
a collection that doesn't need pagination, all we need to do is create this
``CollectionRepresentation`` object. 

Open ProgrammerController and go down to listAction. Right now we're taking
the programmers, putting them onto a programmer key and serializing that.
Instead, create a new ``CollectionRepresentation`` - I'll use my IDE to auto-complete
that, which automatically adds a ``use`` statement at the top of the file.
The ``CollectionRepresentation`` takes two arguments: the items that are
inside the collection and the key that these should live under. I could use
anything here, but I'll use ``programmers``. Just choose something that makes
sense, then be consistent so that whenever your API clients see a ``programmers``
key, they know what it contains. 

Now, we'll just pass this new object directly to ``createApiResponse``. And
hey, that's it! Let's try the test, and this time it passes! So whenever
you have a collection resource, use this object. If it's paginated, stay
tuned.

And now that we're following the rules of HAL, if we go back to the HAL browser
and re-send the request, you actually get a completely different page. This
time is says we have no properties, since our collection has no keys outside
of the embedded stuff, and below it shows our embedded programmers and we
can see the data for each. And each even has a ``self`` link, which we can
follow to move our client to that new URL and see the data for that one
programmer. If it has any other links, we could keep going by following those.
So hey, this is getting kind of fun! And as you'll see, the HAL browser can
help figure out what new links can be added to make life better.
