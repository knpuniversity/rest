Exposing more Error Details
===========================

In our app, we trigger 404 pages by calling the ``throw404`` shortcut function
in a controller. Behind the scenes, this throws a ``NotFoundHttpException``,
which extends that very special ``HttpException`` class. The result is a
response with a 404 status code. That magic is part of Silex. But without
this, we could have built in our own logic in the exception listener to map
certain exception classes to specific status codes.

All PHP exception have an optional message, which we can populate by passing
an argument to ``throw404``. Let's do that and include some details that
might help explain the 404 to the client::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if (!$programmer) {
            $this->throw404(sprintf('The programmer %s does not exist!', $nickname));
        }
        // ...
    }

    // ... repeat for other throw404 lines

If you go to ``/api/programmers/FooBar`` in the browser, we get a 404 JSON
response, but we don't see that message. Look back at our exception-handling
function. The ``NotFoundHttpException`` is not an instance of ``ApiProblemException``,
so we fall into the situation where we create the ``ApiProblem`` by hand.
We're not using the exception's message anywhere, so it makes sense we don't
see it.

    // src/KnpU/CodeBattle/Application.php

    // ...
    } else {
        $apiProblem = new ApiProblem(
            null,
            $statusCode
        );
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
would be terrible.

Instead, let's *only* expose the message if the exception is an instance
of ``HttpException``::

    // src/KnpU/CodeBattle/Application.php
    // ...

    if ($e instanceof ApiProblemException) {
        $apiProblem = $e->getApiProblem();
    } else {
        $apiProblem = new ApiProblem(
            null,
            $statusCode
        );

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
internals. Make sure whatever logic you use here is very solid.
