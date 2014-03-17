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

.. code-block:: text

    POST /api/programmers HTTP/1.1
    Host: CodeBattles.io
    Authorization: Bearer b2gG66D1Tx6d89f3bM6oacHLPSz55j19DEC83x3GkY
    Content-Type: application/json

    {
        "nickname": "Namespacinator"
    }

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

- what is a resource
- what is a representation
- REST
- RMM

PLAN
----

- a bit of intro theory - but not too much to overdo it!
- mention Richardson Maturity Model (RMM) 0 and 1 
- Very basic REST introduction - the HTTP message, the GET method, status code
- Resources and representations (but not too heavy)