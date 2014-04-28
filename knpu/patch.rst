PATCH: The Other Edit
---------------------

The rules of HTTP say that if you want to edit a resource, you should make
a PUT rquest to it and include the new representation you want.

To get kinda technical, PUT says "take this representation and entirely put
it at this URI". This means that a REST API should require the client to
send *all* of the data for a resource when updating it. If the client *doesn't*
send a field, a REST API is supposed to interpret that as a blank value.

Right now, our API follows this rule. As a result, if a client sends a PUT
request to ``/api/programmers/ObjectOrienter`` to update the ``avatarNumber``
field but forgets to include the ``tagLine``, the ``tagLine`` will updated
to be blank. So PUT is really more of a *replace* than an *update*.

This is how PUT is *supposed* to work. But not all API's follow this rule,
because it's kind of *not* the behavior we expect, and it *could* cause accidental
lost data. We're going to follow the rules and keep our PUT implementation
as a replace. So how can we allow the client to update something without
sending every field?

Introducing PATCH: The Update Method
------------------------------------

With PATCH of course! The main HTTP methods like GET, POST, DELETE and PUT
were introduced in that famous RFC 2616 document. But because PUT has this
limitation, PATCH was born in `RFC 5789`_.

Writing the Test
----------------

Typically, PATCH is used exactly like PUT, except that it allows for missing
fields. It's used as the true update: if we forget to include the ``tagLine``
field with a PATCH request, no problem! The API is supposed to just leave
the existing ``tagLine`` untouched.

Let's write a scenario for PATCH, one that looks like our PUT scenario, but
*only* sends the ``tagLine`` data in the representation. Of course, we'll
want to check that the ``tagLine`` was updated, but also that the ``avatarNumber``
is unchanged:

    TODO: PATCH Programmer: Write the test

Coding the Endpoint
-------------------

Let's head into our controller and add another routing line that matches
the same ``/api/programmers/{nickname}`` URI, but only on PATCH requests.
This looks a little bit different only because Silex doesn't natively support
PATCH request. But the result is the same as the other routing lines::

    TODO - PATCH Programmer: Adding the controller

You've probably noticed that I've made this route use the same ``updateAction``
function as the PUT route. That's not on accident: these two HTTP methods
are so similar, that I bet we can save some code by re-using the controller
function.

The only difference with PATCH is that if we don't see a field in the JSON
that client is sending, we should just skip updating it. Let's add an ``if``
statement in this function that checks to see if this is a ``PATCH`` method
and also if the field is missing from the JSON. If both are ``true``, let's
do nothing::

    TODO - PATCH Programmer: Adding the controller

And just like that, we *should* have a working PATCH endpoint. And if we
somehow broke our PUT endpoint, our tests will tell us!

But we're in luck! When we run Behat, everything still comes back green.
We now have 2 methods a client can use to update a resource: PUT and PATCH.

But honestly, all of this is a little bit controversial. A lot of people
just support one of these HTTP verbs, and commonly make PUT act like PATCH.
Do what's best for your API clients, be consistent, and then make sure it's
perfectly clear how things work. But remember, the more you bend the rules,
the weirder your API will look when people are learning it.

The Truth Behind PATCH
----------------------

Ok, I've lied to you a little bit. We're *technically* using PATCH incorrectly.
Let's go back to `RFC 5789`_ where it describes PATCH with a little more
detail:

    In a PUT request, the enclosed entity is considered to be a modified
    version of the resource stored on the origin server, and the client is
    requesting that the stored version be replaced. With PATCH, however,
    the enclosed entity contains a set of instructions describing how a resource
    currently residing on the origin server should be modified to produce
    a new version.

Let me paraphrase for you. With PUT, we send a representation of the resource.
But with PATCH, we send a set of instructions on what to edit. So instead
of a JSON programmer, we might instead create some JSON structure with details
on what to update:

    [
        { "op": "replace", "path": "avatarNumber", "value": "5" },
        { "op": "remove", "path": "tagLine" }
    ]

In fact, even this little structure here comes from another proposed standard,
`RFC 6902`_. If you want to know more about this, read the blog post
`Please. Don't Patch Like An Idiot`_ from this tutorial's co-author William.

So what should you do in your API? It's tough, because we live in a world
where the most popular API's are still behind in following the rules. Try
to follow the rules for PUT and PATCH the best you can, while still making
your API very easy for your clients. And above everything, be consistent
and outline your implementation in your docs.

.. _`RFC 5789`: http://tools.ietf.org/html/rfc5789
.. _`Please. Don't Patch Like An Idiot`: http://williamdurand.fr/2014/02/14/please-do-not-patch-like-an-idiot/
