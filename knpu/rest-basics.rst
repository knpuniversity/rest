REST Basics
===========

We need to lay out a little bit of groundwork. Stick with me through just
a *little* bit of theory and it'll pay off huge later when we're building
the project.

HTTP
----

Everything starts with HTTP: the acronym that describes the format of the
request message that a client sends and the response that our server sends
back. If you think of an API like a function, the request is the input and
the response is our output. It's that simple.

HTTP Request
~~~~~~~~~~~~

.. code-block:: text

    GET /api/programmers HTTP/1.1
    Host: CodeBattles.io

This is a basic request and it has 3 important pieces:

1. First, ``/api/programmers`` is the URI: uniform resource identifier. Notice
   I said **resource**: each URI is a unique address to a resource, just
   like you have a unique address to your house. If you have 5 URI's you're
   saying you have 5 resources.

2. Second, ``GET`` is the HTTP method and basically describes what *action*
   we want to take against the resource. You're probably already familiar
   with GET and POST, and possibly also with DELETE, PUT and the infamous
   PATCH. There are others, but these are the most important ones.

3. Finally, every line after the first is just a colon-separated list of
   headers. This request only has a ``Host`` header, but a client could send
   anything.

With that in mind, a POST request might look like this:

.. include:: includes/_post_programmer.rst.inc

Ok, so same basic idea except the method is POST and we're sending data
in the body of the request. We also have 2 extra headers, one for authentication
and one that tells the server to expect JSON data in the body.

HTTP Response
~~~~~~~~~~~~~

The response our API will return looks pretty similar:

.. code-block:: text

    HTTP/1.1 200 OK
    Content-Type: application/json
    Cache-Control: no-cache, private

    {
        "nickname": "Namespacinator",
        "avatarNumber": 5,
        "tagLine": "",
        "powerLevel": 25
    }

The 200 status code is the first important piece, and of course means that
everything went just fine. Status codes are a *big* part of APIs, but they're
usage is commonly argued.

The headers tell the client that the response is JSON and that the response
shouldn't be cached. And finally, we send back a JSON body.

Good news: HTTP is pretty simple. Got it? Great, let's move on to some harder
stuff.

Resources and Representations
-----------------------------

REST: Representational state transfer. The term was coined famously by Roy
Fielding in a doctoral dissertation in 2000. So yea, it's complex, and a lot
of the challenges around building a RESTful API are in understanding and
debating the many rules, or constraints laid out in the dissertation.

When you build an API, it's pretty common to think in terms of endpoints,
or the URLs your API will have. And when you give something a URL, it turns
that thing into a resource. So, ``/programmers/Namespacinator`` is probably
the address to a programmer resource and ``/programmers`` is probably the address
to a resource that's actually a collection of programmers posts. This is already
how we build the web, so we get this.

But instead of thinking about resources, I want to think about representations.
Suppose a client makes a GET request to ``/programmers/Namespacinator`` and
gets back a JSON response:

.. code-block:: json

    {
        "nickname": "Namespacinator",
        "powerLevel": 5
    }

That's the programmer resource, right? Wrong! No!

This is a representation of the programmer resource. It happens to be in
JSON, but the server could have represented the programmer in other ways,
like in XML, YAML or even in JSON with a different format.

The same applies when a client sends a request that contains programmer data:

.. include:: includes/_post_programmer.rst.inc

The client doesn't send a programmer resource, it just sends a representation.
The server's job is to interpret this representation and update the resource.

Representation State
--------------------

This is exactly how browsing the web works. An HTML page is *not* a resource,
it's just one representation. And when we fill out a form, we're just sending
a different representation back to the server.

A representation is a machine readable explanation of the current state of
a resource.

Yes, I said the current "state" of the resource, and that's another important
and confusing term. What REST calls state, you probably think of as simply
whatever data a resource has. Like earlier, when the client makes a GET request
to ``/programmer/Namespacinator``, the JSON is a representation of its current
state, or current data. And if the client made a request to update that programmer,
the client is said to be sending a representation in order to update the
state of the resource.

So a client and server exchange representations of a resource, which reflect
its current state or its desired state. So REST, or Representational state
transfer, is a way for a two machines to transfer the state of a resource
via representations.

We just took an easy idea and turned it upside down. But if you can understand
*this* way of thinking, a lot or what you read about REST will make a lot
more sense.

Transitions
-----------

Ok, just one more thing: state transitions. We already know about resource
state, and how a client can transition the resource state by sending a representation
with its new state, or data.

There's also application state. When you browse the web, you're always only
just *one* page of a site. That's your application state. When you click
a link, you transition your application state to a different page. Whatever
state we're in, or page we're on, helps us get to the next state or page,
because it shows us the most relevant links. Oh, and HTML forms are also
links. When we submit a form, it's just like a link, it POST's to a URL,
which is our new app state. If the server redirects us, that's once again
our new app state. Application state is kept in our browser, but the server
helps guide us by sending us links, in the form of ``a`` tags, HTML forms,
or even redirect responses.

The same is true for an API, though maybe not the API's that you're used to.
We won't talk about it initially, but a big part of building a RESTful API
is sending back links along with your data. These tell you the most likely
URLs you'll want your API to follow and are meant to be a guide. When you
think about an API client following links, you can start to see how there's
application state, even in an API. And so our job is to help the client with
links.

Richardson Maturity Model
-------------------------

We just accidentally talked through something called the `Richardson Maturity Model`_,
which describes the different levels of RESTfulness. If your API is built
where each resource has a specific URL, you've reached level 1. If you take
advantage of HTTP verbs, like GET, POST, PUT and DELETE, congats: you're
level 2! And if you take advantage of these links I've been talking about,
that means you've reached hypermedia, a term we'll discuss later. But anyways,
hypermedia means you're a Richardson Maturity Model grand master, or something.

We'll keep this model in mind, but for now, let's start building!



NOTES
-----

- too much theory? Should I wait and explain much of this later?
