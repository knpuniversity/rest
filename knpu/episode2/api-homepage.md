# A Homepage for your API?

If I tell you to go to a webpage, I'll probably tell you to go to its homepage -
like KnpUniversity.com - because I know once you get there, you'll be able
see links, follow them and find whatever you need. And if you think about
it, there's no reason an API has to be any different. And this is an idea
that's catching on.

So right now, if I go to just `/api` in the Hal browser we get a 404 response,
because we haven't built anything for this yet. I'm thinking, why not build
a homepage that people can go to and get information about how to use our
API?

## API Homepage: Start Simple

I'll build this in `ProgrammerController` just for convenience. Let's add the
new `/api` route and point it at a new method called `homepageAction`:

[[[ code('05dbbd786f') ]]]

Inside of here, I'm not going to return anything yet - let's just reuse that
same `createApiResponse`, and I'll pass it an empty array:

[[[ code('28addb83fc') ]]]

If we try this, we get an empty response, but it has a valid `application/hal+json`.
So that's all we really need to do to get an endpoint working.

## A Model Class for Every Resource

We know that every URL is a resource in the philosophical sense: `/api/programmers`
is a collection resource and `/api/programmers/Fred` represents a programmer
resource. And really, the `/api` endpoint is no different. By the way, I
did not  mean to leave the `/` off of my path - it doesn't matter if you
have it, but I'll add it for consistency.

[[[ code('d7bc6c6400') ]]]

So far, every time we have a resource, we have a model class for it. Why not
do the same thing for the Homepage resource? Create a new class called `Homepage`:

[[[ code('38e965823f') ]]]

And without doing anything else, I'll create a new object called `$homepage`.
Don't forget to add your `use` statement whenever you reference a new
class in a file. And instead of the empty array, we'll pass `createApiResponse()`
the `Homepage` object:

[[[ code('4c1cf6b96a') ]]]

So every time we have a resource, we have a class for it. It doesn't really
matter if the class is being pulled from the database, being created manually
or being populated with data from something like Elastic Search. 

## Homepages Love Links

If we hit Go on the browser, we get the exact same response back: no difference
yet. But now that we have a model class, we can start adding things to it.
And since every resource has a `self` link, let's add that to `Homepage`
too. I'll grab the `Relation` from programmer for convenience. And of course,
grab the `use` statement for it: I know I'm repeating myself over and over
again!

Now, we need the name of the route. So let's go give this new route a name:
`api_homepage`:

[[[ code('0caa3988e7') ]]]

We'll use this in the `Relation`. And because there aren't
any wildcards in the route, we don't need any `parameters`.

[[[ code('80b75e49b5') ]]]

Cool!

Let's try this out! We have a link! Now, I know this doesn't seem very useful
yet, because we have an API homepage that's linking to itself, but now if
we wanted to, we could link back to the homepage from other resources. So
if we were on the `/api/programmers` resource, we could add a link back
to the homepage. When an API client GET's that URL, they'll see that there's
a place to get more information about the entire API.

## Other Link Attributes (like title)

When you look at the Links section, there are a few other columns like title,
name/index and docs. One of the things that this is highlighting is that
your links can have more than just that `href`. So in `Homepage`, let's give
the link a title of "The API Homepage":

[[[ code('069e156214') ]]]

Let's go back to the browser to see that title. And now we can give any link
a little bit more information.

## Hi, Welcome! Homepage Message

Let's keep going! Since this is the API homepage, you probably want to give
the client a nice welcome message and maybe even link to the human-readable
documentation. Even though this `Homepage` class isn't being pulled from the
database doesn't mean that we can't have properties. Let's create a `$message`
property and set that to some hard-coded text. You could even put your documentation
URL here:

[[[ code('ec31a8ed8b') ]]]

And now, our API homepage is getting interesting! But the *real* purpose of
this homepage is to have links to the actual resources that the API client
will want. This is really easy as well. The most obvious resource the client
may want is the programmers collection. So let's do this here. We'll say 
`programmers` and we'll link to `/api/programmers`. That route doesn't have
a name yet, so let's call it `api_programmers_list`:

[[[ code('b532721f71') ]]]

And now we can use it in the `Relation`. To be super friendly, we'll give
this link a nice title as well:

[[[ code('43efee73ef') ]]]

So let's hit Go. Now we have a really great homepage. We can come here, we
can see the message with a link to the documentation, we can visit all of
the programmers, and now we're dangerous. From there we can follow links to
a programmer, to that programmer's battles, and anything else our links
lead us to.
