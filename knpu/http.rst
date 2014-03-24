HTTP Basics
===========

Yep, we need cover a bit of theory. Wait, come back! This stuff is *super*
important and *fascinating* too. Put on your thinking cap and let's
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
   against the resource. You already know about GET and POST, and possibly
   also DELETE, PUT and the infamous PATCH. There are others, but mostly
   we don't care about those.

3. Every line after the first is just a colon-separated list of headers.
   This request only has two, but a client could send anything.

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
