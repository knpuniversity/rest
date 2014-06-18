Exposing more Error Details
===========================

In our app, we trigger 404 pages by calling the ``throw404`` shortcut function
in a controller. Behind the scenes, this throws a ``NotFoundHttpException``,
which extends that very special ``HttpException`` class. The result is a
response with a 404 status code. That magic is part of Silex. But without
this, we could have built in our own logic in the exception listener to map
certain exception classes to specific status codes.

All exceptions have an optional message, which we can populate by passing
an argument to ``throw404``. We're already including some details that might
help the API client.

If you go to ``/api/programmers/bumblebee`` in the browser, we get a 404 JSON
response, but we don't see that message. Look back at our exception-handling
function. The ``NotFoundHttpException`` is not an instance of ``ApiProblemException``,
so we fall into the situation where we create the ``ApiProblem`` by hand.
We're not using the exception's message anywhere, so it makes sense we don't
see it::

    // src/KnpU/CodeBattle/Application.php

    // ...
    } else {
        $apiProblem = new ApiProblem($statusCode);
    }

Before we fix this, let's update the 404 scenario. We can use the standard
``details`` field to store the exception message:

.. code-block:: gherkin

    Scenario: Proper 404 exception on no programmer
      # ...
      And the "detail" property should equal "The programmer fake does not exist!"

To get this working, we're going to set the ``details`` property to be the
exception object's message. But wait! We need to be very very careful. We
don't *always* want to expose the message. What if some deep exception is
thrown from the database? We might be exposing our database structure. That
would be awkward.

Instead, let's *only* expose the message if the exception is an instance
of ``HttpException``::

    // src/KnpU/CodeBattle/Application.php
    // ...

    if ($e instanceof ApiProblemException) {
        $apiProblem = $e->getApiProblem();
    } else {
        $apiProblem = new ApiProblem($statusCode);

        if ($e instanceof HttpException) {
            $apiProblem->set('detail', $e->getMessage());
        }
    }

This will now include only exceptions that are for things like 404 and 403
responses, which *we* are usually in charge of creating and throwing. If
a deep database exception occurs, it won't be an ``HttpException`` message,
so nothing will get exposed.

Run the test to try it out. Awesome! That's a much more helpful error response.

In your application, you'll still want to be careful with this. We want to
be helpful to the client, but we absolutely don't want to expose any of our
internals. Make sure whatever logic you use here is very solid. #security

Even *our* logic is a bit loose. For example, if we go to a URL that just
doesn't exist, the client sees "No route found" in the details, which is
a bit more than I want to show the user. To fix this, you could show messages
from an even smaller set of exception classes. The `FOSRestBundle`_ for Symfony
has a feature like this.

Big Errors When Developing
--------------------------

Let's throw a big exception from inside the ``showAction`` controller method
and see how the tests look::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function showAction($nickname)
    {
        throw new \Exception('I made a mistake!');
        // ...
    }

Let's run *just* one of the scenarios that uses ``showAction``. To do this,
we can point Behat directly at the ``.feature`` file and include the line
number where the word ``Scenario:`` appears:

.. code-block:: bash

    $ php vendor/bin/behat features/api/programmer.feature:64

When the test fails, it prints out what the client will see, which is our
API Problem media type response... but with absolutely no details beyond
the 500 status code.

While developing, that's not helpful. Instead, for 500 errors, I want to
continue seeing the big beautiful, normal error page, because it includes
the exception message and stacktrace.

Go back to the ``Application.php`` file where our exception handler function
lives. Most applications have some variable that says whether you're in debug
mode or not. If we *are*, and the status code is 500, let's *not* handle
the exception here. Instead, the normal big error page will show::

    // src/KnpU/CodeBattle/Application.php
    // ...

    $this->error(function(\Exception $e, $statusCode) use ($app) {
        // only act on /api URLs
        if (strpos($app['request']->getPathInfo(), '/api') !== 0) {
            return;
        }

        // allow 500 errors to be visible to us in debug mode
        if ($app['debug'] && $statusCode == 500) {
            return;
        }
        // ...
    }

For Silex, there's a ``debug`` key on this ``$app`` variable, which I set
in a ``bootstrap.php`` file. You should have something similar in your app's
bootstrap or configuration. Use that! Not seeing your exception information
is no fun.

Ok, be sure to remove our Exception message from ``showAction`` so our app
works again.

.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/doc/4-exception-controller-support.md#step-4-exceptioncontroller-support
