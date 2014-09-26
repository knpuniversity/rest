ApiProblemException and Exception Handling
==========================================

In order to be able to throw an exception that results in a JSON response,
we need to hit the gym and first create a new class called ``ApiProblemException``. 
Make it extend that special ``HttpException`` class::

    // src/KnpU/CodeBattle/Api/ApiProblemException.php
    namespace KnpU\CodeBattle\Api;

    use Symfony\Component\HttpKernel\Exception\HttpException;

    class ApiProblemException extends HttpException
    {
    }

The purpose of this class is to act like a normal exception, but also to
hold the ``ApiProblem`` inside of it. To do this, add an ``$apiProblem`` property
and override the ``__construct`` method so that an ``ApiProblem`` object
is the first argument::

    // src/KnpU/CodeBattle/Api/ApiProblemException.php
    namespace KnpU\CodeBattle\Api;

    use Symfony\Component\HttpKernel\Exception\HttpException;

    class ApiProblemException extends HttpException
    {
        private $apiProblem;

        public function __construct(ApiProblem $apiProblem, \Exception $previous = null, array $headers = array(), $code = 0)
        {
            $this->apiProblem = $apiProblem;

            parent::__construct(
                $apiProblem->getStatusCode(),
                $apiProblem->getTitle(),
                $previous,
                $headers,
                $code
            );
        }
    }

The exception still needs a message and I'm calling ``getTitle()`` on the ``ApiProblem``
object to get it. Open up the ``ApiProblem`` class and add this ``getTitle()``
function so we can access it::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    // ...

    class ApiProblem
    {
        // ...

        public function getTitle()
        {
            return $this->title;
        }
    }

Finally, go back to ``ApiProblemException`` and add a ``getApiProblem`` getter
function. Hang tight, we'll use this in a few minutes::

    // src/KnpU/CodeBattle/Api/ApiProblemException.php
    namespace KnpU\CodeBattle\Api;

    use Symfony\Component\HttpKernel\Exception\HttpException;

    class ApiProblemException extends HttpException
    {
        // ...

        public function getApiProblem()
        {
            return $this->apiProblem;
        }
    }

Back in the controller, throw a new ``ApiProblemException`` and pass the
``ApiProblem`` object into it::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...
    use KnpU\CodeBattle\Api\ApiProblemException;
    // ...

    private function handleRequest(Request $request, Programmer $programmer)
    {
        // ...

        if ($data === null) {
            $problem = new ApiProblem(
                400,
                ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT
            );

            throw new ApiProblemException($problem);
        }

        // ...
    }

Exception Listener
------------------

If we run the tests now, they still fail. But notice that the status code
*is* still 400. Our new exception class extends ``HttpException``, so we
really have the same behavior as before.

When an exception is thrown anywhere in our app, Silex catches it and gives
us an opportunity to process it. In fact, this is true in just about every
framework. So if you're not using Silex, just find out how to extend the
exception handling in your framework and repeat what we're doing here.

Open up the ``Application.php`` class in the ``src/KnpU/CodeBattle/`` directory.
This is the heart of my application, but you don't need to worry about it
too much. At the bottom of the class, I've created a ``configureListeners``
function. By calling ``$this->error``, we can pass it an anonymous function
that will be called whenever there is an exception anywhere in our app. Add
a debug statement so we can test it::

    // src/KnpU/CodeBattle/Application.php
    // ...

    private function configureListeners()
    {
        $this->error(function() {
            die('hallo!');
        });
    }

To try it out, just open up the app in your browser and go to any 404 page,
since a 404 is a type of exception:

    http://localhost:8000/foo/bar

Awesome! We see the ``die`` code.

Filling in the Exception Listener
---------------------------------

When Silex calls the function, it passes it 2 arguments: the exception that
was thrown and the status code we should use::

    // src/KnpU/CodeBattle/Application.php
    // ...

    private function configureListeners()
    {
        $this->error(function(\Exception $e, $statusCode) {
            die('hallo!');
        });
    }

.. tip::

    Silex passes a ``$statusCode`` argument, which is equal to the status
    code of the HttpException object that was thrown. If some other type
    of exception was thrown, it will equal 500.

Here's the cool part: if the exception is an ``ApiProblemException``, then
we can get the embedded ``ApiProblem`` object and use it to create the proper
``JsonResponse``.

Let's first check for this - if it's not an ``ApiProblemException``, we won't
do any special processing. And if it is, we'll create the ``JsonResponse``
just like we might normally do in a controller::

    // src/KnpU/CodeBattle/Application.php
    // ...

    private function configureListeners()
    {
        $this->error(function(\Exception $e, $statusCode) {
            // only do something special if we have an ApiProblemException!
            if (!$e instanceof ApiProblemException) {
                return;
            }

            $response = new JsonResponse(
                $e->getApiProblem()->toArray(),
                $e->getApiProblem()->getStatusCode()
            );
            $response->headers->set('Content-Type', 'application/problem+json');

            return $response;
        });
    }

That's it! If we throw an ``ApiProblemException``, this function will transform
it into the ``JsonResponse`` we want. Don't believe me? Try running the tests
now:

.. code-block::: bash

    $ php bin/vendor/behat

ApiProblemException for Validation
----------------------------------

This is *really* powerful. If we need to return a "problem" anywhere in our
API, we only need to create an ``ApiProblem`` object and throw an ``ApiProblemException``.

Let's take advantage of this for our validation errors. Find ``handleValidationResponse``
and throw a new ``ApiProblemException`` instead of creating and returning
a ``JsonResponse`` object. And to keep things clear, let's also rename this
function to ``throwApiProblemValidationException``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function throwApiProblemValidationException(array $errors)
    {
        $apiProblem = new ApiProblem(
            ApiProblem::TYPE_VALIDATION_ERROR
        );
        $apiProblem->set('errors', $errors);

        throw new ApiProblemException($apiProblem);
    }

Now, update ``newAction`` and ``updateAction`` to use the new function name.
We can also remove the ``return`` statements from each: we don't need that
anymore::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    // newAction() and updateAction()
    if ($errors = $this->validate($programmer)) {
        $this->throwApiProblemValidationException($errors);
    }

And when we run the tests, all green! Piece by piece, we're making our *code*
more consistent so that we can guarantee that our *API* is consistent.
