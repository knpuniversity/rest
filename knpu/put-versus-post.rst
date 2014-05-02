PUT Versus POST
===============

PUT versus POST: one of those conversations you try *not* to have. It leads
to broken friendships, rainy picnics, and sad-looking kittens. People are
passionate about REST, and this is one of the really sensitive topics.

First, you can read the technical descriptions in the `rfc2616`_ document
I mentioned earlier. It's actually pretty cool stuff. But this is more than
theory: you'll need to know when to choose PUT and when to choose POST, so
listen up!

Safe Methods
------------

Each HTTP method is said to be "safe" or "unsafe". An HTTP method is "safe"
if using it doesn't modify anything on the server. Ok, yes, logs will be
written and analytics collected, but "safe" methods should never
modify any real data. GET and HEAD are "safe" methods.

Making a request with unsafe methods - like POST, PUT and DELETE - *does*
change data. Actually, if you make a request with an unsafe method it *may not*
change anything. For example, if I try to update a programmer's ``avatarNumber``
to the value it already has, nothing happens.

The point is that if a client uses an unsafe method, it knows that this method
may have consequences. But if it uses a safe method, that request won't ever
have consequences. You could of course write an API that violates this. But
that's dishonest - like showing a picture of ice cream and then giving people
broccoli. I like brocolli, but don't be a jerk.

Being "safe" affects caching. Safe requests can be cached by a client, but
unsafe requests can't be. But caching is a whole different topic!

Idempotent
----------

Within the unsafe methods, we have to talk about the famous term: "idempotency".
A request is idempotent if the side effects of making the request 1 time
is the same as making the request 2, 3, 4, or 1072 times. PUT and DELETE
are idempotent, POST is not.

For example, if we make the PUT request from our test once, it updates the
``avatarNumber`` to 2. If we make it again, the ``avatarNumber`` will still
be 2. If we make the PUT request 1 time or 10 times, the server always results
in the same state.

Now think about the POST request in our test, and imagine for a second that
the ``nickname`` doesn't have to be unique, because that detail clouds things
here unnecessarily.

If we make the request once, it would create a programmer. If we make it again,
it would create *another* programmer. So making the request 12 times is not
the same as making it just once. This is *not* idempotent.

Now you can see why it *seems* right to say that POST creates resources and
PUT updates them.

POST or PUT? The 2 Rules of PUT
-------------------------------

Other than PATCH, which is an edge case we'll discuss next, if you're building
an endpoint that will modify data, it should use a POST or PUT method.

Deciding between POST and PUT is easy: use PUT if and only if the endpoint
will follow these 2 rules:

1. The endpoint must be idempotent: so safe to redo the request over and
   over again;

2. The URI must be the address to the resource being updated.

When we use PUT, we're saying that we want the resource that we're sending
in our request to be stored at the given URI. We're literally "putting" the
resource at this address.

This is what we're doing when we PUT to ``/api/programmers/CowboyCoder``.
This results in an update because ``CowboyCoder`` already exists. But imagine
if we changed our controller code so that if ``CowboyCoder`` didn't exist,
it would be created. Yes, that should *still* be a PUT: we're putting the
resource at this URI. Because of this, PUT is usually thought of as an update,
but it could be used to create resources. You may never choose to use PUT
this way, but technically it's ok.

Heck, we *could* even support making a PUT request to ``/api/programmers``.
But if we did - and we followed the rules of PUT - we'd expect the client to
pass us a collection of programmers, and we'd replace the existing collection
with this new one.

POST and Non-Idempotency
------------------------

One last thing. POST is not idempotent, so making a POST request more than
one time *may* have additional side effects, like creating a second, third
and fourth programmer. But the key word here is *may*. Just because an endpoint
uses POST doesn't mean that it *must* have side effects on every request.
It just *might* have side effects.

When choosing between PUT and POST, don't just say "this request is idempotent,
it must be PUT!". Instead, look at the above 2 rules for put. If it fails
one of those, use POST: even if the endpoint is idempotent.

.. _`rfc2616`: http://www.w3.org/Protocols/rfc2616/rfc2616.html
