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
