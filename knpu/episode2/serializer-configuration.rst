Time to tackle that test failure! You can see that there is one strange 
problem. The "GET one programmer" scenario says it's expecting a ``tagLine`` 
property, but you can see that the JSON response has a lot of other fields, 
but not ``tagLine``. I know what the issue is, so we'll fix it in a second.

But first, one thing the test didn't show yet, is that we're losing the
``Content-Type`` of ``application/json``. We need to centralize
the logic that creates our Response as much as possible so that all of our
responses are very very consistent. For example, right now - we're creating
the ``Response`` in every controller, and so every controller method is responsible
for remembering to set the ``Content-Type`` header. That's pretty error-prone,
which means I am guaranteed to mess it up.

Instead, let's go to =BaseController the parent class of ``ProgrammerController``.
Let's create a couple of new functions. First, back in ``ProgrammerController``,
cut the ``serialize`` function, move it into the ``BaseController`` and change it
to be ``protected``. Now, when we have multiple controllers in the future,
we can just re-use this method to serialize other resources, like Battles.

Second, create a new function called ``createApiResponse`` with 2 arguments:
the data and the status code. The data could be a ``Programmer`` a ``Battle``
or anything else. Then we'll let *it* call the ``serialize`` function. And finally,
create the ``Response`` and make sure the ``Content-Type`` header is set
perfectly.

Back in ``ProgrammerController``, we can simplify a lot of things. Let's
search for ``new Response``, because we can replace these. In ``newAction``
we can say ``$response = $this->createApiResponse`` and pass it the ``$programmer``
object and the 201 status code. And we can still add any other headers we
need.

I'll copy this code and change the other spots in this controller. The ``deleteAction``
returns a 204, which is the blank response. So there's no need to use this
fancy serialization stuff here.

Now let's try the tests again, so we can make sure we see *just* that same
one failure. And we do!

This failure is caused by something specific to the serializer. In this test,
the programmer doesn't actually have a ``tagLine`` - we could see this if
we looked in the database. When the serializer sees ``null`` values, it has
2 options: return the property with a ``null`` value, or omit the property
entirely. By default, the serializer actually omits ``null`` properties. We'll
fix this, because I like always having the same fields on a response, even
if some of the data is empty.

Go back to the ``serialize`` function. To configure the serializer, create
a ``SerializationContext`` object. Next, call ``setSerializeNull`` and
pass ``true``. Now, pass this context as the 3rd argument to ``serialize``.
There's not a lot you can customize in this way, but ``serializeNull`` happens
to be one of them.

Back to the tests! Boom, everything passes! We've changed to use the serializer,
and it's now taking care of all of the heavy-lifting for us. This will be
really powerful as we serialize more and more objects.
