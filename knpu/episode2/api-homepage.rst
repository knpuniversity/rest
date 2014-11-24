API Homepage
============

If I tell you to go to a webpage,  I'll probably tell you to go to its homepage -
like KnpUniversity.com - because I know once you get there, you'll be able
see links, follow the links and find whatever you need. And if you think
about it, there's no reason an API has to be any different. And this is an
idea that's catching on.

So right now, if I go to just ``/api`` in the Hal browser we get a 404 response,
because we haven't built anything for this yet. I'm thinking, why not build
a homepage that people can go to and get information about how to use our
API?

I'll build this in ProgrammerController just for convenience. Let's add the
new ``/api`` route and point it at a new method called ``homepageAction``.
Inside of here, I'm not going to return anything yet - let's just reuse that
same ``createApiResponse``, and I'll pass it an empty array.

If we try this, we get an empty response, but it has a valid ``application/hal+json``.
So that's all we really need to do to get an endpoint working.

We know that every URL is a resource in the philosophical sense: ``/api/programmers``
is a collection resource and ``/api/programmers/Fred`` represents a programmer
resource. And really, the ``/api`` endpoint is no different. Philosophically,
this is the address to a resource. By the way, I did not mean to leave the
``/`` off of my path - it doesn't matter if you have it, but I'll add it
for consistency

So far, every time we have a resource, we have a mode class for it. Why not
do the same thing for the Homepage resource? Create a new class called ``Homepage``.
And without doing anything else, I'll create a new object called ``$homepage``.
Don't forget to add  your ``use`` statement whenever you reference a new
class in a file. And instead of the empty array, we'll pass createApiResponse
the ``Homepage`` object.

So every time we have a resource, we have a class for it. It doesn't really
matter if the class is being pulled from the database, being created manually
or being populated with data from Elastic Search. 

If we hit Go on the browser, we get the exact same response back: no difference
yet. But now that we have a mode class, we can start adding things to it.
And since every resource has a ``self`` link, let's add that to Homepage
too. I'll grab the Relation from programmer for convenience. And of course,
every time you reference an annotation, grab the ``use`` statement for it.
I know I'm repeating myself over and over again!

Now, we need the name of the right. So let's go give this new route a name:
``api_homepage``. We'll use this in the ``Relation``. And because there aren't
any wildcards in the route, we don't need any ``parameters``. Cool!

Let's try this out! We have a link! Now, I know this doesn't seem very useful
yet, because we have an API homepage that's linking to itself, but now if
we wanted to, we could link back to the homepage from other resources. So
if we were on the ``/api/programmers`` resource, we could add a link back
to the homepage. So when an API client GET's that URL, they'll see that there's
a place to get more information about the entire API.

When you look at the Links section, there are a few other columns like title,
name/index and docs. One of the things that this is highlighting is that
your links can have more than just that ``href``. So in ``Programmer``, let's
give the link a title of "The API Homepage". Let's go back to the browser
to see that title. And now we can give any link a little bit more information.

Let's keep going! Since this is the API homepage, you probably want to give
the client a nice welcome message and maybe even link to the human-readable
documentation. Even though this Homepage class isn't being pulled from the
database doesn't mean that we can't have properties. Let's create a ``$message``
property and set that to some hard-coded text. You could even put your documentation
URL here.

And now, our API homepage is getting interesting! But the *real* purpose of
this API homepage is to have links to the actual resources that the API client
will want. And this is really easy as well. The most obvious resource the
client may want is the programmers collection resource. So let's do this here.
We'll say ``programmers`` and we'll link to ``/api/programmers``. That route
doesn't have a name yet, so let's call it ``api_programmers_list``. And now
we can use it in the Relation. To be super nice, we'll give this link a nice
title as well.

So let's hit Go. Now we have a really nice homepage. We can come here, we
can see the message wit ha link to the documentation, we can visit all of
the programmers, and now we're dangerous. From there we can follow links to
a programmer, to that programmer's battles, and anything else our links
lead us to.
