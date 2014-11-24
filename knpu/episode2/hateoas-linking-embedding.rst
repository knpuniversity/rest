HATEOAS Linking and Embedding
=============================

Let's add more links! Back in ``battle.feature``, we're returning a ``programmerUri``,
which was our way of creating a link before we knew there was a good standard
to follow. So now we can say: And the "_links.programmer.href" property should
equal "/api/programmers/Fred". This time, instead of using ``self``, we're
using ``programmer``. There are some special names like self that mean something,
but when you're linking from a battle to a programmer, we'll just invent
something new. We'll want to use this consitently in our API: whenever we're
linking to a programmer, we'll use that same string ``programmer`` so taht
our API clients learn that whenever they see a link ``programmer``, what
type of resource to expect on teh other side.

First, let's run our test - line 26 - and make sure that fails. Let's go
in and add that relation. Open up Battle and also open up Programmer so we
can steal the Relation from there as promised. And don't forget, every time
you use an annotation for the first time in a class, you need a ``use`` statement
for it.

And also, since we have this relationship now, I'm going to remove our ``VirtualProperty``
down below. So this is really good - we're linking to a Programmer like before.
So the route name is good and the nickname is good. The only thing that needs
to change is that in order to get the nickname of the programmer for this
Battle, we need to say ``object.programmer.nickname`` so that it uses the
``programmer`` field below. Let's try our test. Ah, and it fails! I got caught
by copying and pasting - we *do* have a link, but its name is ``self``. Change
that to be ``programmer``. And now, we'll get that to pass. Awesome.

Because we're *always* putting links under a ``_links`` key, I have a new
piece of language that we can use in Behat to check for links: And the link
"programmer" should exist and its value should be "/api/programmers/Fred".
Why would I do this? It's just proving how consistent we are. This new sentence
will look for the ``_links`` property, so there's no reason to repeat it
in all of our scenarios. So let's try the test again - perfect.

We can repeat this same thing over in ``programmer.feature`` when we're checking
the ``self`` link. I'll comment out the old line for reference. If we run
our entire test suite, things keep passing. I love to see all of that green.

It's time to talk about a big term in REST called hypermedia. It's one of
those terms that seems like it was invented to scare people, but it's really
an underwhelming idea. We all know that every response has a ``Content-Type``,
like ``text/html`` or ``application/json``. So when you here "media" or
"media types" and "content types", they're referring to the same idea.

We have two things: media and we have hypermedia.

Media is any format: ``text/html``, ``text/plain``, ``application/json``.
These all contain data, and it's as simple as that.

*Some* of these formats are also called *hypermedia*. What's the difference
between media and hypermedia? Hypermedia is a format that has a place for
*links* to live. And that's it. The class case you'll hear people talk about
for hypermedia is HTML. HTML is the original hypermedia format. It contains
data - like we see here - but it also has a way to express links, via anchor
tags and forms are also a type of link. So these are links here and these
are links. So implicit in the HTML format is a way to separate links from
the rest of your data.

So JSON is **not** hypermedia. That may seem confusing, because you might
be thinking "but didn't we just add links to our JSON - isn't that hypermedia?"
And that answer is no, because if you read the official JSON specification,
all it will talk about is where you curly braces, quotes, colons and commas
should go. JSON is about the structure of the data - it says nothing about
what's actually inside of the data. So by itself, JSON is just a media type,
because there's nothing in there that says where your links should live.

But what's cool is that we've adopted this HAL JSON. This is something that's
built on *top* of JSON: it starts with the JSON structure and then adds extra
rules about where links should live. So when you talk about JSON, that's
a media format. But when you talk about HAL, that's a hypermedia format, because
it's spec tells you that links live below ``_links``. 

So that's really it: hypermedia is just a way to say: I have a structure
that returns links, and there are rules about where those links live.

As soon as you adopt a hypermedia format, instead of returning a ``Content-Type``
of ``application/json``, you can return a ``Content-Type`` of something different,
like ``application/hal+json``. At the bottom of this page, there's an example
of what a response looks like. And you can see that the response comes back
with a ``Content-Type`` of ``application/hal+json``. This is a signal to the
client that the response has a JSON structure but has some additional semantic
rules on top of it. What's awesome is that if we return this in our API and
someone looks at that header, they're going to say "Oh, what's this application/hal+json"
format?". If they haven't heard of it, they'll can Google it and find and
they'll be able to read about the structure and say: oh, they're using a format
where the links live in an ``_links`` key, along with some other rules. 

Because we're already following HAL, returning this ``Content-Type`` header
on all of our endpoints is an easy win. In ``battle.feature``, let's add
a new scenario line to test this. My editor isn't happy with my language here.
If you can't remember your definitions, run behat with the ``-dl`` option.
I'll grep this for ``header`` because I know I have a definition. Ah, and
my language is slightly off. And now PHPStorm is really happy. Oh, and we
actually want to look for ``application/hal+json``. I'll run the test first,
and it is failing.

Remember, this is served from ``BattleController``, so let's go back there.
And all of our endpoints call this same ``createApiResponse`` method. If
we click into this, it opens up the ``BaseController`` and this is a method
we created earlier. It uses the serializer then creates a Response. So let's
just update that ``Content-Type`` header. Run the test, and it passes perfectly.
Now, API clients can see this header and know that we're using some extra
rules on top of the JSON structure.

----------------

When we made our Battle endpoint, we decided it might be convenient to have
a link to Programmer. It's just a nice thing to help our API clients. But
to get that programmer's information, they're going to need to make a second
request out to that URI. Sometimes, you may choose instead to *embed*
one resource into another. If it's *really* common to need the programmer
information when you GET a battle, you may *choose* to put the Programmer's
data, right inside the Battle's response.

What's really nice here is that HAL already has rules about how this should
work. There's the ``_links`` section, but there's also an ``_embedded`` section.
And our HATEOAS library will help us put stuff there.

So let's try to embed our programmer into the battle. First, let's add a
line to the scenario that looks for this. Let's look for ``_embedded``,
and we know it's going to be called ``programmer``, and the data will live
below this and we know one of the fields on a programmer is nickname. And
we know this should be equal to Fred.

Let's make sure that fails first - and it does.

To make this work, we'll add more annotations to ``Programmer``. When you
think of one resource relating to another - like how our Battle relates to
the Programmer resource - there are 2 ways to express that relation. You
can either link to it *or* you can embed it. Those are both just valid ways
to think about expressing a link between 2 resources. This ``@HATEOAS\Relation``
lets you do whichever you want. For a link, use the ``href``. To embed something,
use the ``embedded`` key and set it to an expression that points to which
object you want to embed. And actually, if you include both ``href`` and
``embedded``, it'll create a link *and* embed it. 

Before we run the test, add a "And print last response", because I like to
see how my endpoints look. Let's run our test, and awesome it passes. If
you look - HATEOAS is doing all the work for us. We still have ``_links``,
but we also have ``_embedded``. What's cool is that it goes out to the ``Programmer``
resource and serializes it. You end up with all the same properties as normal,
and you even end up with its links. So a lot of things are falling into place
accidentally. 

And just like with links, since embedded data always lives under ``_embedded``,
I have a built-in definition you can choose to use if you want to. Behind
the scenes, this knows to look for all of this on the ``_embedded`` property.

And the test still passes. Now I'll take out the print last response. When
it comes to linking and embedding, I hope you're feeling dangerous!
