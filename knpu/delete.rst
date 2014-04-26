Deleting Resources
==================

The only thing an API client *can't* do to a programmer resource is delete
it. So let's fix that!

Once again, we're going to leverage HTTP methods. We have GET to retrieve
a representation, PUT to update the resource, and DELETE to, ya know, blow
the resource up! HTTP gives us these HTTP verbs so that we don't need to
do silly things like have a ``/api/programmers/delete`` URI. Remember, every
URI is a resource, so that URI wouldn't really make sense.

Writing the Test
----------------

Where to start! Why, with the test of course! Open up our feature file and
add yet another scenario, this time for deleting a programmer resource. We
need to use a ``Given`` like in the other scenarios to first make sure that
we have a programmer in the database to delete:

    TODO - features/api/programmer.feature

After deleting a resource, what should the endpoint return and what about
the status code? There's not total agreement on this, but one common approach
is to return a 204 status code, which means "No Content". It's the server's
way of saying "I completed your request, but I really don't have anything
to say back to you". In other words, the response will have an empty body:

    TODO - features/api/programmer.feature

Coding the Endpoint
-------------------

To make this work, we'll need to create a route that responds to the HTTP
``DELETE`` method. Make sure the URL is the same as what we use to GET one
programmer, because we want to take the DELETE action on that resource:

    TODO - src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php

Next, create the ``deleteAction`` method. We can copy a little bit of code
that queries for a programmer and throws a 404 error if one doesn't exist::

    TODO - src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php

.. note::

    Some people say that you sould return a success status code, even if
    what you're trying to delete doesn't exist (afterall, if it never existed,
    that's the same end result as deleting it). You can do that, but I'm
    not convinced it's clear for the client.

Now, just delete the programmer. I've created a shortcut method for this
called ``delete`` in my project. Your code will be different, but fortunately,
deleting things is pretty easy::

    TODO - src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php

And finally, we just need to send a Response back to the user. The important
part is the 204 status code and the blank content, which is what 204 means::

    TODO - src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php

Dang, that was really easy! Execute Behat to make sure we didn't mess anything
up. Awesome! Like with everything else, be consistent with how resources
are deleted. Whether you return a 204 status code, or some sort of JSON message,
return the same thing for all resources when they're deleted.
