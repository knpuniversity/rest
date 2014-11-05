Serializer Annotations
======================

Alright, now let me show you one other powerful thing about the serializer. 
The control you have over your objects! By default, it serializes *all* of
your properties. But let's say we *don't* want the ``userId`` to be serialized.
Afterall this isn't really an important field for the API, it's just the id
of the user that created the programmer.

To start, open up the feature file and add a line to test that this property
isn't in the response. We're using a custom Behat context that I created
for this project with a lot of these nice sentences. To see a list of all
of them, you can run behat with the ``-dl`` option, which stands for "definition list":

.. code-block:: bash

    php vendor/bin/behat -dl

Try running the tests again. It *should* fail, and it does - saying that
the ``userId`` property should not exist, but we can see that it in fact is there.

As soon as you want to take control over what properties are returned, we're
going to use annotations. Let's look at their documentation first and find
the `Annotation Reference`_ section - this is by far the most interesting
page. The first item on the list is what we need, but there's a huge list of
annotations that give you all sorts of control.

Remember, whenever you use an annotation, you need a ``use`` statement
for it. PHPStorm is tagging in to help me auto-complete the ``ExclusionPolicy``
class. Then I'll remove the last part and alias this to ``Serializer``. This
will allow us to use any of the JMS serializer annotations by starting with
``@Serializer``. For example, on top of the class, we can say
``@Serializer\ExclusionPolicy("all")``. We've now told the serializer to
not serialize *any* of the properties in this class, unless we tell it to
specifically. Whereas before, it was serializing *everything*.

To actually include things, we whitelist them with the ``@Serializer\Expose``
annotation. I'll copy this and use it on ``nickname``, ``avatarNumber``,
``tagLine`` and ``powerLevel`` fields. This is just *one* of the customizations
you can make with annotations.

Now let's re-run the test. Success! This time the ``userId`` is *not* returned
in our JSON.

If you want to know more, check out that annotation reference section. But
we're also going to do more in the next videos.
