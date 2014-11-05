The Serializer: Swiss-Army Knife of APIs
========================================

Hey guys! Welcome to episode 2 of our RESTFUL API’s in the real world series.
In episode1 we covered a lot of the basics and also a lot of the really important
confusing terms like representations, resources idempotency, GET, POST, PUT,
PATCH and a lot of other things that really make it difficult to learn REST
because you have know idea what anybody's talking about. At This point I hope
that you feel like you have a nice base because we're going to take that base
and start doing a lot more interesting things like talking about serializers,
hypermedia HATEOAS, documentation and a lot more - including things that
maybe you shouldn't worry about because they're more theoretical than useful
in your real API.

I've already started by downloading the code for Episode 2 onto my computer.
So I'm going to move over to the ``web/`` directory, which is the document
root for our project, and use the built-in PHP Web server to get things running.
Let's try that out in our browser... and there we go.

You can login as ryan@knplabs.com with password ``foo`` - very secure -
and see the web side of our side. There's a web side of our site and an API
version of our site, which we created in Episode 1.

If you're not familiar with the project, it's pretty simple, you create programmers,
give them a tag line, select from of our avatars and once you've done that,
you can battle a project. So we'll start a battle here, fight against
"InstaFaceTweet"... some magic happens, and boom! Our happy code has defeated
the InstaFaceTweet project.

So from an API perspective, you can see that we have a number of resources:
we have programmers, we have projects and we also have battles. And they all
relate to each other, which will be really important for episode 2.

The API code all lives inside of the ``src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php``
file. And in episode 1, we created endpoints for listing programmers, showing
a single programmer, updating a programmer and deleting a programmer. We
also used Behat to make some nice scenarios for this. This makes sure that
our API doesn't break. It also lets us design our API, before we think about
implementing it.

Whenever I have an API, I like to have an object for each resource. Before
we started in episode 1, I created a ``Programmer`` object. And this is actually
what we're allowing the API user to update, and we're using this object to
send data back to the user.

So one of the key things we were doing was turning objects into JSON. For
example, let's look in ``showAction``. We're querying the database for a
``Programmer`` object, using a simple database-system I created in the background.
Then ultimately, we pass that object into the ``serializeProgrammer`` function,
which is a simple function we wrote inside this same class. It just takes
a ``Programmer`` object and manually turns it into an array. This transformation
from an object into an array is really important because we're going to be
doing it for every resource across all of our endpoints.

The first thing we're going to talk about is a library that makes this a lot
easier and a lot more powerful, called a serializer. The serializer I like
is called `jms/serializer`_, so let's Google for that. I'll show you how
this works as we start using it. But the first step to installing library
is to bring it in via Composer.

I'll open a new tab, go back into the root of my project, and then copy
that command.

If you get a "command not found" for ``composer``, then you need to install
it globally in your system. Or, you can go and download it directly, and
you'll end up with a ``composer.phar`` file. In that case, you'll run
``php composer.phar require`` instead.

While we're waiting, let's go back and look at the usage a little bit. What
we'll do is create an object called a "serializer", and there's this ``SerializerBuilder``
that helps us with this. Then we'll pass it some data, which for us will
be a ``Programmer`` object or a ``Battle`` object. And then it returns to
you the actual JSON string. So it takes an object and turns it into a string.

Now this is a little bit specific to Silex, which is the framework we're
building our API on, but in Silex, you have a spot where you can globally
configure objects that you want to be able to use. They're called services.
I'll create a new global object called ``serializer`` and we'll use code
similar to what you just saw to create the ``serializer`` object. We're doing
this because it will let me access that object from any of my controllers.

Before I start typing anything here, I'll make sure everything is done downloading.
Yes, it is - so I should be able to start referencing the serializer classes.
Start with the ``SerializerBuilder`` that we saw. We also need to set a cache
directory, because this library caches annotations that we'll use a bit later.
This is a fancy way in my app to tell it to use a ``cache/serializer`` directory
at the root of my project.

There's also a debug flag, and when that's true, it'll rebuild the cache
automatically. Finally, the last step tells the serializer to use the same
property names that are on the ``Programmer`` object as the keys on the JSON.
In other words, don't try to transform them in any way. And that's it!

The important thing here is that we have a ``serializer`` object and we can
access it from any of our controllers. Let's open our ``ProgrammerController``
and rename ``serializeProgrammer`` to just ``serialize``, since it can serialize
anything. I've setup my application so that I can reference any of those
global objects by saying ``$this->container['serializer']``. This will look
different for you: the important point is that we need to access that object
we just configured.

Now, just call ``serialize()`` on it, just like the documentation told us.
I'll put ``json`` as the second argument so we get JSON. The serializer can
also give us another format, like XML.

Perfect! Now let's look to see where we used the ``serializeProgrammer``
function before. That old function return an array, but ``serialize`` now
returns the JSON string. So now we can return a normal ``Response`` object
and just pass the JSON string that we want. The one thing we'll lose is the
``Content-Type`` header being set to ``application/json``, but we'll fix
that in a second. Let's go and make similar changes to the rest of our code.

In fact, in the spot where we were serializing the collection of ``Programmer``
objects, things get much easier. We can pass the entire array of objects
and it's smart enough to know how to serialize that. You can already start
to see some of the benefits of using a serializer.

Compared with to what we had before, not a lot should have changed, because
the serializer should give us a JSON structure with all the properties in
``Programmer``. That's practically the same as we were doing before.

So let's run our tests! We had one failure - let's check it out! You can
see that there is one strange problem. The "GET one programmer" scenario
says it's expecting a ``tagLine`` property, but you can see that the JSON
response has a lot of other fields, but not ``tagLine``. I know what the
issue is, so we'll fix it in one second.

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
