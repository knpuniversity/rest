We had one failure - let's check it out! You can
see that there is one strange problem. The "GET one programmer" scenario
says it's expecting a ``tagLine`` property, but you can see that the JSON
response has a lot of other fields, but not ``tagLine``. I know what the
issue is, so we'll fix it in a second.

But first, one thing the test didn't show yet, is that we're losing the
``Content-Type`` of ``application/json``. In general, we need to centralize
the logic that creates our Response as much as possible so that all of our
responses are very very consistent. For example, right now - we're creating
the ``Response`` in every controller, and so every controller method is responsible
for remembering to set the ``Content-Type`` header. That's pretty error-prone.

Instead, let's go into our BaseController, which our controller extends.
Let's create a couple of new functions. First, back in ``ProgrammerController``,
cut the ``serialize``, move it into the ``BaseController`` and change it
to be ``protected``. Now, when we have multiple controllers in the future,
we can just re-use this method to serialize other resources, like Battles.

Second, create a new function called ``createApiResponse`` with 2 arguments:
the data and the status code. And the data could be a ``Programmer`` a ``Battle``
or anything else. And we'll let *it* call the ``serialize`` function. Finally,
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
if some of the data is null.

Go back to the ``serialize`` function. To configure the serializer, create
a ``SerializationContext`` object. Next, call the ``setSerializeNull`` and
pass ``true``. Now now, pass this context as the 3rd argument to ``serialize``.
There's not a lot you can customize in this way, but ``serializeNull`` happens
to be one of them.

Back to the tests! Boom, everything passes! We've changed to use the serializer,
and it's now taking care of all of the heavy-lifting for us. This will be
really powerful as we serialize more and more objects.

Let me show you one other powerful thing about the serializer, and that's
the control you have over your objects. By default, it serializes *all* of
your properties. But let's say we *don't* want the ``userId`` to be serialized.
This is the actual ``id`` of the user who created the ``Programmer``, and
it's not really a detail you need to know about.

To start, open up the feature file and add a line to test that this property
isn't in the response. We're using a custom Behat context that I created
for this project with a lot of these nice sentences. To see a list of all
of them, you can run behat with the ``-dl`` option, which stands for "definition list":

.. code-block:: bash

    php vendor/bin/behat -dl

Try running the tests again. It *should* fail, and it does - saying that
the ``userId`` property should not exist, but we can see that it does.

As soon as you want to take control over what properties are returned, we're
going to use annotations. Let's look at their documentation first and find
the `Annotation Reference`_ section - this is by far the most interesting
section. The first on the list is what we need, but there's a huge list of
annotations that give you all sorts of control.

But remember, whenever you use an annotation, you need a ``use`` statement
for them. I'll use PHPStorm to help me auto-complete the ``ExclusionPolicy``
class, but then remove the last part and alias this to ``Serializer``. This
will allow us to use any of the JMS serializer annotations by starting with
``@Serializer``. For example, on top of the class, we can say
``@Serializer\ExclusionPolicy("all")``. We've now told the serializer to,
by default, not serialize *any* of the properties in this class. Whereas before,
it was serializing *everything*.

To actually include things, we whitelist them with the ``@Serializer\Expose``
annotation. I'll copy this and use it on ``nickname``, ``avatarNumber``,
``tagLine`` and ``powerLevel``. This is just *one* of the customizations
you can make with annotations.

Now let's re-run the test. Success! This time the ``userId`` is *not* returned
in our JSON.

If you want to know more, check out that annotation reference section. But
we're also going to do more in the next videos.
