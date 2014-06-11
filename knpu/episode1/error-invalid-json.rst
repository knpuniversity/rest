Error in Invalid JSON
=====================

Beyond validation errors, what else could go wrong? Well what if a tricky client
tries to mess with us by sending invalid JSON? Right now, that would result
in a cryptic 500 error message. We can't let them catch us off guard, so we need
a 400 status code with a clear explanation to tell them that we're always watching.

Let's write a test! I'll copy the validation error scenario, but remove a
quote so that the JSON is invalid:

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: Error response on invalid JSON
      Given I have the payload:
        """
        {
          "avatarNumber" : "2
          "tagLine": "I'm from a test!"
        }
        """
      When I request "POST /api/programmers"
      Then the response status code should be 400

For now, let's just continue to check that the status code is 400. take that 
shifty client! If we run the test immediately, it fails with a 500 error instead.

Handling Invalid JSON
---------------------

In our controller, we're already checking to see if the JSON is invalid, but
right now, we're throwing a normal PHP Exception message, which results in
the 500 error::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleRequest(Request $request, Programmer $programmer)
    {
        // ...

        if ($data === null) {
            throw new \Exception(sprintf('Invalid JSON: '.$request->getContent());
        }

        // ...
    }

To make this a 400 error, we could do 2 things. First, we could create a
new ``Response`` object and set its status code to 400. That's what we're
already doing with the validation error.

Second, in Silex, we could throw a special ``HttpException``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleRequest(Request $request, Programmer $programmer)
    {
        // ...

        if ($data === null) {
            throw new HttpException(
                400,
                sprintf('Invalid JSON: '.$request->getContent())
            );
        }

        // ...
    }

In most frameworks, if you throw an exception, it results in a 500 status
code. That's true in Silex too, unless you throw this very special type of
exception where the status code is the first argument.

First, make sure it's working by running the test:

.. code-block::: bash

    $ php bin/vendor/behat

.. tip::

    Silex/Symfony has a `collection of exception classes`_, one for each of
    the most common 400 and 500-level responses. For example, :symfonyclass:`Symfony\\Component\\HttpKernel\\Exception\\BadRequestHttpException`
    is a sub-class of ``HttpException`` that sets the status code to 400.
    The result is the same: throwing these "named" exception classes is just
    a bonus to give you code more consistency and clarity.

Awesome! So why am I throwing an exception instead of just returning a normal
400 response? The problem is that we're inside ``handleRequest``, so if I
return a ``Response`` object here, it won't actually be sent back to the
user unless we also return that value from ``newAction`` and ``updateAction``.
That just gets confusing and a bit ugly.

Instead, if we throw an exception, the normal execution will stop immediately
and the user will *definitely* get the 400 response. So being able to throw
an exception like this makes my code easier to write and understand. Double threat!

The disadvantage is complexity. When I throw an exception, I need to have
some other magic layer that is able to convert it into a proper response. 
In Silex, that magic layer is smart enough to see my ``HttpException``
and create a response with a 400 status code instead of 500.

If this doesn't make sense yet, keep following along.

ApiProblem for Invalid JSON
---------------------------

Since invalid JSON is a "problem", we should really send back an ``application/problem+json``
response. Let's first update the test to look for this ``Content-Type`` header
and then look for a ``type`` field that's equal to ``invalid_body_format``:

    # features/api/programmer.feature
    # ...

    Scenario: Error response on invalid JSON
      # the rest of the scenario
      # ...
      And the "Content-Type" header should be "application/problem+json"
      And the "type" property should equal "invalid_body_format"

To make this work, we'll create a new ``ApiProblem`` object. But first, let's
add the new ``invalid_body_format`` type as a constant to the class and give
it a title::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    // ...

    class ApiProblem
    {
        // ...
        const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';

        static private $titles = array(
            // ...
            self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',
        );

        // ...
    }

Next, instantiate the new ``ApiProblem`` in the controller::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleRequest(Request $request, Programmer $programmer)
    {
        // ...

        if ($data === null) {
            $problem = new ApiProblem(
                400,
                ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT
            );

            // ...
        }

        // ...
    }

But now what? When we had validation errors, we just created a new ``JsonResponse``,
passed ``$problem->toArray()`` as data, and returned it. But here, we
want to throw an exception instead so that the normal flow stops.

We're going to fix this in two steps. First, we *will* throw an Exception,
but we'll put the ``ApiProblem`` inside of it. Second, we'll hook into the
magic layer that handles exceptions and extend it so that it transforms the
exception into a ``Response`` with a 400 status code. Again, this is a little
more complicated, so if it doesn't make sense yet, watch our implementation.

.. _`collection of exception classes`: https://github.com/symfony/symfony/tree/master/src/Symfony/Component/HttpKernel/Exception
