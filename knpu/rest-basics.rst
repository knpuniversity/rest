REST Basics
===========

Yep, we need cover a bit of theory. Wait, come back! This stuff is *super*
important stuff and *fascinating* too. Put on your thinking cap and let's
get to it!

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
    Accept: application/json,text/html

This is a basic request and it has 3 important pieces:

1. ``/api/programmers`` is the URI: uniform resource identifier. I said
   **resource**! Each URI is a unique address to a resource, just like you
   have a unique address to your house. If you have 5 URI's you're saying
   you have 5 resources.

2. ``GET`` is the HTTP method and describes what *action* you want to take
   against the resource. You're already know about GET and POST, and possibly
   also DELETE, PUT and the infamous PATCH. There are others, but mostly
   we don't care about those.

3. Every line after the first is just a colon-separated list of headers.
   This request only has two headers, but a client could send anything.

With that in mind, a POST request might look like this:

.. include:: includes/_post_programmer.rst.inc

It's the same, except the method is POST and we're sending data in the body
of the request. We also have 2 extra headers, one for authentication and
one that tells the server that the body has JSON-formatted stuff in it.

HTTP Response
~~~~~~~~~~~~~

The response message is similar:

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
everything went just great. Status codes are a *big* part of APIs. But people
also like to argue about them. We'll see the important ones as we build.

The headers tell the client that the response is JSON and that the response
shouldn't be cached. And of course, the JSON body is at the end.

HTTP is awesome and really simple. Got it? Great, let's move onto something
harder.

REST: Resources and Representations
-----------------------------------

REST: Representational state transfer. The term was coined famously by `Roy Fielding`_
in his doctoral dissertation in 2000. It's complex, and a lot of what makes
a REST API hard is understanding and debating the many rules, or constraints
laid out in the dissertation.

When you think about an API, it's pretty common to think about its endpoints,
or the URLs the API will have. With REST, if you have a URL, then you have
a resource. So, ``/programmers/Namespacinator`` is probably the address to
a single programmer resource and ``/programmers`` is probably the address
to a collection resource of programmers. So even a collection or programmers
is considered one resource.

But we already build URLs that work like this on the web, so this is nothing
new.

Representations
~~~~~~~~~~~~~~~

Now that you understand resources, I want to think about representations.
Suppose a client makes a GET request to ``/programmers/Namespacinator`` and
gets back this JSON response:

.. code-block:: json

    {
        "nickname": "Namespacinator",
        "powerLevel": 5
    }

That's the programmer resource, right? Wrong! No!

This is just a *representation* of the programmer resource. It happens to
be in JSON, but the server could have represented the programmer in other
ways, like in XML, YAML or even in JSON with a different format.

The same applies when a client sends a request that contains programmer data:

.. include:: includes/_post_programmer.rst.inc

The client doesn't send a programmer resource, it just sends a representation.
The server's job is to interpret this representation and update the resource.

Representation State
--------------------

This is exactly how browsing the web works. An HTML page is *not* a resource,
it's just one representation. And when we submit a form, we're just sending
a different representation back to the server

One resource could have many representations. Heck, you could get crazy and
have an API where you're able to request the XML, JSON *or* HTML representations
of every resource. We're just crazy enough that we'll do some of that.

A representation is a machine readable explanation of the current state of
a resource.

Yes, I said the current "state" of the resource, and that's another important
and confusing term. What REST calls state, you probably think of as simply
the "data" of a resource. When the client makes a GET request to ``/programmer/Namespacinator``,
the JSON is a representation of its current state, or current data. And if
the client made a request to update that programmer, the client is said to
be sending a representation in order to update the "state" of the resource.

In REST-speak, a client and server exchange representations of a resource,
which reflect its current state or its desired state. REST, or Representational state
transfer, is a way for two machines to transfer the state of a resource
via representations.

I know I know. We just took an easy idea and made it crazy! But if you can
understand *this* way of thinking, a lot or what you read about REST will
start to make sense.

Transitions and Client State
----------------------------

Ok, just one more thing: state transitions. We already know about resource
state, and how a client can change the resource state by sending a representation
with its new state, or data.

In addition to resource state, we also have application state. When you browse
the web, you're always on just *one* page of a site. That page is your application's
state. When you click a link, you transition your application state to a
different page. Easy.

Whatever state we're in, or page we're on, helps us get to the next state
or page, by showing us links. A link could be an anchor tag, or an HTML form.
If I submit a form it POST's to a URL with some data, and that URL becomes
our new app state. If the server redirects us, *that* now becoes our new app
state.

Application state is kept in our browser, but the server helps guide us by
sending us links, in the form of ``a`` tags, HTML forms, or even redirect
responses. HTML is called a hypermedia format, because it supports having
links along with its data.

The same is true for an API, though maybe not the API's that you're used to.
We won't talk about it initially, but a big part of building a RESTful API
is sending back links along with your data. These tell you the most likely
URLs you'll want your API to follow and are meant to be a guide. When you
think about an API client following links, you can start to see how there's
application state, even in an API.

Richardson Maturity Model
-------------------------

We just accidentally talked through something called the `Richardson Maturity Model`_.
It describes differentlevels of RESTfulness. If your API is built where each
resource has a specific URL, you've reached level 1. If you take advantage
of HTTP verbs, like GET, POST, PUT and DELETE, congrats: you're level 2!
And if you take advantage of these links I've been talking about, that means
you've reached "hypermedia", a term we'll discuss later. But anyways, hypermedia
means you're a Richardson Maturity Model grand master, or something.

We'll keep this model in mind, but for now, let's start building!
