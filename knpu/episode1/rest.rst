REST: Resources and Representations
===================================

REST: Representational state transfer. The term was coined famously by `Roy Fielding`_
in his doctoral dissertation in 2000. It's complex, and a lot of what makes
a REST API hard is understanding and debating the many rules, or constraints
laid out in his document.

When you think about an API, it's pretty common to think about its endpoints,
in other words the URLs. With REST, if you have a URL, then you have
a resource. So, ``/programmers/Namespacinator`` is probably the address to
a single programmer resource and ``/programmers`` is probably the address
to a collection resource of programmers. So even a collection of programmers
is considered one resource.

But we already build URLs that work like this on the web, so this is nothing
new.

Representations
---------------

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
ways, like in XML, HTML or even in JSON with a different format.

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
of any resource. We're just crazy enough that we'll do some of that.

A representation is a machine readable explanation of the current state of
a resource.

Yes, I said the current "state" of the resource, and that's another important
and confusing term. What REST calls state, you probably think of as simply
the "data" of a resource. When the client makes a GET request to ``/programmer/Namespacinator``,
the JSON is a representation of its current state, or current data. And if
the client makes a request to update that programmer, the client is said to
be sending a representation in order to update the "state" of the resource.

In REST-speak, a client and server exchange representations of a resource,
which reflect its current state or its desired state. REST, or Representational state
transfer, is a way for two machines to transfer the state of a resource
via representations.

I know I know. We just took an easy idea and made it insane! But if you can
understand *this* way of thinking, a lot of what you read about REST will
start to make sense.

.. _`Roy Fielding`: https://twitter.com/fielding
