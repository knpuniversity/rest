Handling Errors
===============

Things are starting to look nice, but in reality, 

Things are looking nice, but our API is still pretty unusable? 

We're missing a really important piece to our API: errors! Without properly
handling errors, our API will be hard to use because the client won't know
what it's doing wrong. If we deployed right now and a client tried to create
a programmer with a nickname that already exists, it would get get a 500
error with no details. Bummer!

Writing the Test
----------------

Let's start with a simple case: when a client POST's to ``/api/programmers``,
we should add validation logic to guarantee that a ``nickname`` is passed.
Like always, let's write the test first.

We can copy the working scenario for creating a programmer, but remove the
``nickname`` field from the request payload. Obviously, the status code won't
be 201. Because this is a *client* error, we'll need a 400-level status code.
But which one? Ah, this is another spot of wonderful debate! The most common
is probably 400, which means simply "Bad Request". If we look back at the
`RFC 2616`_ document, the description of the 400 status code seems to fit
our situation:

    The request could not be understood by the server due to malformed syntax.
    The client SHOULD NOT repeat the request without modifications.

The other common choice is 422: Unprocessable Entity. 422 comes from a different
RFC and is used when the format of the data is ok, but semantically, it has
errors. We add validation that reflects our business rules, which is totally
semantic. So even though 422 seems to be less common than 400 for validation
errors, it may be a more proper choice:

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: Validation errors
      Given I have the payload:
        """
        {
          "avatarNumber" : "2",
          "tagLine": "I'm from a test!"
        }
        """
      When I request "POST /api/programmers"
      Then the response status code should be 400

Now, what should the response content look like? Obviously, it'll be JSON
for our API, but let me suggest a structure that looks like this:

.. code-block:: json

    {
        "type": "valdiation_error",
        "title": "There was a validation error",
        "errors": {
            "nickname": "Please enter a nickname"
        }
    }

Just trust me on the structure for now. In the test, we can look for these
3 fields and also check that we have a ``nickname`` errors field but not
an ``avatarNumber`` error.

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: Validation errors
      Given I have the payload:
        """
        {
          "avatarNumber" : "2",
          "tagLine": "I'm from a test!"
        }
        """
      When I request "POST /api/programmers"
      Then the response status code should be 400
      And the following properties should exist:
        """
        type
        title
        errors
        """
      And the "errors.nickname" property should exist
      But the "errors.avatarNumber" property should not exist

Adding Validation
-----------------

Test, check! Next, let's actually add some validation. This is one of those
spots that may be different based on your framework, but the important thing
is how we *communicate* those errors to the client.

In Silex, to make the ``nickname`` field required, we need to open up the
``Programmer`` class itself. Here, add a ``NotBlank`` annotation with a nice
message::

    // src/KnpU/CodeBattle/Model/Programmer.php
    // ...

    class Programmer
    {
        // ...

        /**
         * @Assert\NotBlank(message="Please enter a nickname")
         */
        public $nickname;

        // ...
    }

Cool! Next, open up the ``ProgrammerController`` class. In ``newAction``,
we should check the validation before saving the new ``Programmer``. I've
created a shortcut method called ``validate`` that does this::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...
        $this->handleRequest($request, $programmer);

        $errors = $this->validate($programmer);

        $this->save($programmer);
        // ...
    }

It uses the annotation we just added to the ``Programmer`` class and returns
an array of errors: one error for each field. If you *are* using Silex or
Symfony, you can re-use my shortcut code on your project. If you're not, just
make sure you have some way of getting back an array of error.

If the ``$errors`` array isn't empty, we've got a problem! And since we already
wrote the test, we know *how* we want to tell the user. Create an array with
the ``type``, ``title`` and ``errors`` fields. The ``$errors`` variable is
already an associative array of messages, where the keys are the field names.
So we can just set it on the ``errors`` field::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...

        $errors = $this->validate($programmer);
        if (!empty($errors)) {
            $data = array(
                'type' => 'validation_error',
                'title' => 'There was a validation error',
                'errors' => $errors
            );

            return new JsonResponse($data, 400);
        }

        $this->save($programmer);
        // ...
    }

Just like with any other API response, we can create a ``JsonResponse`` class,
pass it our data. The only difference with this endpoint is that it has a
status code of 400.

Let's try it!

.. code-block::: bash

    $ php bin/vendor/behat

Awesome, all green!

Validation on Update
--------------------

What's that? You want to add validation when updating too? Good idea!

To avoid duplication, create a new private function in the controller called
``handleValidationResponse``. We'll pass it an array of errors and it will
transform it into the proper 400 JSON response::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleValidationResponse(array $errors)
    {
        $data = array(
            'type' => 'validation_error',
            'title' => 'There was a validation error',
            'errors' => $errors
        );

        return new JsonResponse($data, 400);
    }

Now that we have this, use it in ``newAction`` and ``updateAction``::

    // newAction and updateAction

    $this->handleRequest($request, $programmer);

    if ($errors = $this->validate($programmer)) {
        return $this->handleValidationResponse($errors);
    }

    $this->save($programmer);

To make sure we didn't break anything, we can run our tests:

.. code-block::: bash

    $ php bin/vendor/behat

We could have also added another scenario to test the validation when updating.
How detailed you get with your tests is up to you.

The application/problem+json Content-Type
-----------------------------------------

Sometimes, there are clear rules in the API world. When we create a new resource,
returning a 201 status code is understood as the *right* thing to do.

But other times, there isn't real consistency. What we're working on right
now is a good example: there's no standard for *how* an API response should
be structured that has error information.

I used a structure with ``type``, ``title``, and ``errors`` fields. And actually,
I didn't invent this: it's part of a young, potential standard called API
Problem, or Problem Details. When we google for it, we find an RFC document
of course! Actually, this is technically an "Internet Draft": a work-in-progress
document that *may* eventually be a standard. If you use this standard, then
you should understand that it may change in the future or be discarded entirely
for something different. But in the API world, sometimes we can choose to
follow a draft standard like this, or invent our own. In othe words, we can
choose to make our API consistent with at least *some* other API's, or consistent
with nobody else.

Oh, and when you're reading one of these documents, make sure you're on the
latest version - they're updated all the time.

If we read a little bit, we can see that this standard outlines a response
that typically has a ``type`` field, a ``title`` and sometimes a few others.
The ``type`` field is  the internal, unique identifier for an error and the
title is a short human description for the ``type``. If you look at our ``type``
and ``title`` values, they fit this description pretty well.

And actually, the ``type`` is supposed to be a URL that I can copy into my
browser to get even more information about the error. We'll fix this later.

The spec also allows you to add any additional fields you want. Since we
need to tell the user what errors there are, we've added an ``errors`` field.

This means that our error response is *already* following this specification,
which means that we're a bit more consistent with other API's. And since
someone has already defined the meaning of some of our fields, we can use
this document as part ouf or API's documentation.

Setting the Content-Type Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

But right now, there's no way for an API client to know that we're leveraging
this draft spec, unless they happen to recognize the structure. But it would
be much better of the response somehow screamed "I'm using the Problem Details
spec!".

And of course, this is totally possible. When you follow this spec, your
``Content-Type`` header should be ``application/problem+json``. This stills
says that the actual format is ``json``. But additionally, it tells the client
that it can find out more about the underlying meaning or semantics of the
data by researching the ``application/problem+json`` Content-Type.

We definitely want to do this, so first let's update the test to look for
this ``Content-Type`` header:

    # features/api/programmer.feature
    # ...

    Scenario: Validation errors
      # all the current scenario lines
      # ...
      And the "Content-Type" header should be "application/problem+json"

Next, add the to our response. We've added plenty of response headers already,
so this is no different::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleValidationResponse(array $errors)
    {
        // ...

        $response = new JsonResponse($data, 400);
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }

When we try the tests, they still pass!

.. code-block::: bash

    $ php bin/vendor/behat

And now the client knows a bit more about our error response, without us
writing any documentation.

Enforcing Consistency with ApiProblem
-------------------------------------

We'll be returning a lot of ``application/problem+json`` responses, and I
want us to *always* be consistent. To make this really easy, why not create
a new ``ApiProblem`` class that can hold all the fields?

Start by creating a new ``Api`` directory and class called ``ApiProblem``::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    namespace KnpU\CodeBattle\Api;

    class ApiProblem
    {
    }

By looking at the spec, I've decided that I want my problem responses to
always have ``status``, ``type`` and ``title`` fields, so I'll create these
three properties and a ``__construct`` function that requires them. I also
create a ``getStatusCode`` function, which we'll use in a moment::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    namespace KnpU\CodeBattle\Api;

    class ApiProblem
    {
        private $type;

        private $title;

        private $statusCode;

        public function __construct($type, $statusCode, $title)
        {
            $this->type = $type;
            $this->statusCode = $statusCode;
            $this->title = $title;
        }

        public function getStatusCode()
        {
            return $this->statusCode;
        }
    }

Finally, since I'll need the ability to add additional fields, let's create
a ``$extraData`` array property and a ``set`` function that can be used to
populate it::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    namespace KnpU\CodeBattle\Api;

    class ApiProblem
    {
        // ...

        private $extraData = array();

        // ...

        public function set($name, $value)
        {
            $this->extraData[$name] = $value;
        }
    }

Back in the controller, instead of creating an array, we can now create a
new ``ApiProblem`` object and set the data on it. This helps us enforce the
structure and avoid typos::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleValidationResponse(array $errors)
    {
        $apiProblem = new ApiProblem(
            'validation_error',
            400,
            'There was a validation error'
        );
        $apiProblem->set('errors', $errors);

        // ...
    }

Now, if we can turn the ``ApiProblem`` into an array, then we could pass
it to the new ``JsonResponse``. To do that, we can just add a new ``toArray``
function to ``ApiProblem``::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    namespace KnpU\CodeBattle\Api;

    class ApiProblem
    {
        // ...

        public function toArray()
        {
            return array_merge(
                $this->extraData,
                [
                    'type' => $this->type,
                    'title' => $this->title,
                    'status' => $this->statusCode
                ]
            );
        }
    }

Use it and the ``getStatusCode`` function to create the ``JsonResponse``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleValidationResponse(array $errors)
    {
        // ...
        $apiProblem->set('errors', $errors);

        $response = new JsonResponse(
            $apiProblem->toArray(),
            $apiProblem->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }

Ok! This step made no difference to our API externally, but gave us a solid
class to use for errors. This will make our code more consistent and easy
to read, especially since we'll probably need to create problem responses
in many places.

To try it out, just re-run the tests:

.. code-block::: bash

    $ php bin/vendor/behat

Now, just like each resource, our error responses have a PHP class that helps
to model them. Nice!

Constants: More Consistency
---------------------------

The ``type`` field a problem response is the unique identifier of an error,
and we're supposed to have documentation for each type. So it's really important
that we keep track of *all* of our types and ever misspell them.

Instead of typing ``validation_error`` manually when we create an ``ApiProblem``,
let's create a new constant on the class itself::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    // ...

    class ApiProblem
    {
        const TYPE_VALIDATION_ERROR = 'validation_error';

        // ...
    }

Now, just reference the constant when instantiating ``ApiProblem``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleValidationResponse(array $errors)
    {
        $apiProblem = new ApiProblem(
            ApiProblem::TYPE_VALIDATION_ERROR,
            400,
            'There was a validation error'
        );

        // ...
    }

That's one less spot where we can accidentally mess something up.

Mapping title to type
~~~~~~~~~~~~~~~~~~~~~

But we can go further. According to the spec, the ``title`` field should
always be the same for any ``spec``. In othe words, we should have the exact
same ``title`` everywhere that we have the ``validation_error`` ``type``.

To force this consistency, let's create an array map on ``ApiProblem`` from
the ``type``, to its human-description.

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    // ...

    class ApiProblem
    {
        const TYPE_VALIDATION_ERROR = 'validation_error';

        static private $titles = array(
            self::TYPE_VALIDATION_ERROR => 'There was a validation error'
        );

        // ...
    }

.. note::

    You can also choose to translate the ``title``. If you do this, you'll
    need to run the key through your translator before returning it.

And instead of passing the ``$title`` as the second argument to the constructor,
we can just look it up by the ``$type``. And like the good programmers we
are, we'll throw a huge ugly and descriptive error if we don't find a title::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    // ...

    class ApiProblem
    {
        // ...

        public function __construct($type, $statusCode)
        {
            $this->type = $type;
            $this->statusCode = $statusCode;

            if (!isset(self::$titles[$type])) {
                throw new \InvalidArgumentException('No title for type '.$type);
            }

            $this->title = self::$titles[$type];
        }
    }

Back in the controller, we can now safely remove the last argument when
constructing the ``ApiProblem`` object::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleValidationResponse(array $errors)
    {
        $apiProblem = new ApiProblem(
            ApiProblem::TYPE_VALIDATION_ERROR,
            400
        );

        // ...
    }

Great work! We have an ``ApiProblem`` class to keep things consistent, a
constant for the one problem type we have so far, and a ``title`` that's
automatically chosen from the type.

Error in Invalid JSON
---------------------

Beyond validation errors, what else could go wrong? What if the client makes
a mistake and sends us invalid JSON? Right now, that would probably result
in a cryptic 500 error message. But really, this should just be another 400
status code with a clear explanation.

Let's write a test! I'll can copy valdiation error scenario, but remove a
quote so that the JSON is invalid:

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

For now, let's just continue to check that the status code is 400. If we
run the test immediately, it fails with a 500 error instead.

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
new ``Response`` object and make sure its status code is 400. That's what
we're already doing with the validation error.

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

Awesome! So why am I throwing an exception instead of just returning a normal
400 response? The problem is that we're inside ``handleRequest``, so if I
return a ``Response`` object here, it won't actually be sent back to the
user unless we return it from ``newAction`` and ``updateAction``.

But if we throw an exception, then the normal execution will stop and the
user will *definitely* get the 400 response. So being able to throw an exception
like this makes my code easier to write.

The disadvantage is complexity. When I throw an exception, I need to have
some other magic layer that is able to convert that exception into a proper
response. In Silex, that magic layer is smart enough to see my ``HttpException``
and create a response with a 400 status code instead of 500.

If this doesn't make sense yet, keep following along with me.

ApiProblem for Invalid JSON
---------------------------

Since invalid JSON is a "problem", we should really send back an ``application/problem+json``
response. Let's first update the test to look for this ``Content-Type`` header
and a ``type`` field that's equal to a new type called ``invalid_body_format``:

    # features/api/programmer.feature
    # ...

    Scenario: Error response on invalid JSON
      # the rest of the scenario
      # ...
      And the "Content-Type" header should be "application/problem+json"
      And the "type" property should equal "invalid_body_format"

To make this work, we'll create a new ApiProblem object. But first, let's
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

Now create the new ``ApiProblem`` in the controller::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleRequest(Request $request, Programmer $programmer)
    {
        // ...

        if ($data === null) {
            $problem = new ApiProblem(
                ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT,
                400
            );

            // ...
        }

        // ...
    }

But now what? When we had validation errors, we just created a new ``JsonResponse``,
passed ``$problem->toArray()`` to it as data, and returned it. But here, we
want to throw an exception instead so that the normal flow stops.

We're going to fix this in two steps. First, we *will* throw an Exception,
but we'll put the ``ApiProblem`` inside of it. Second, we'll hook into the
magic layer that handles exceptions and extend it so that it transforms the
exception into a Response with a 400 status code. Again, this is a little
more complicated, so if it doesn't make sense yet, watch our implementation.

The ApiProblemException
~~~~~~~~~~~~~~~~~~~~~~~

First, create a new class called ``ApiProblemException`` and make it extend
that special ``HttpException`` class::

    // src/KnpU/CodeBattle/Api/ApiProblemException.php
    namespace KnpU\CodeBattle\Api;

    use Symfony\Component\HttpKernel\Exception\HttpException;

    class ApiProblemException extends HttpException
    {
    }

The purpose of this class is to act like a normal exception, but also to
hold the ``ApiProblem`` inside of it. So let's add an ``$apiProblem`` property
and override the ``__construct`` method so we pass the ApiProblem in when
creating it::

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

The object still needs an exception and I'm calling ``getTitle`` on the ``ApiProblem``
object to get it. Make sure to add this function there::

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

Finally, add a ``getApiProblem`` function, which we'll use later::

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
                ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT,
                400
            );

            throw new ApiProblemException($problem);
        }

        // ...
    }

Exception Listener
~~~~~~~~~~~~~~~~~~

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
function. Use an ``error`` function and pass it an anonymous function with
a debug statement::

    // src/KnpU/CodeBattle/Application.php
    // ...

    private function configureListeners()
    {
        $this->error(function() {
            die('hallo!');
        });
    }

This is specific to Silex, but the end result is that Silex will call this
function whenever there's an exception thrown anywhere in the system. To
try it out, just open up the app in your browser and go to any 404 page,
since a 404 is a type of exception:

    http://localhost:8000/foo/bar

Awesome! We see the ``die`` statement.

Filling in the Exception Listener
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When Silex calls the function, it passes the function 2 arguments: the exception
that was thrown and the status code we should use::

    // src/KnpU/CodeBattle/Application.php
    // ...

    private function configureListeners()
    {
        $this->error(function(\Exception $e, $statusCode) {
            die('hallo!');
        });
    }

Here's the cool part: if the exception is an ``ApiProblemException``, then
we can automatically transform it into the proper ``JsonResponse``. Let's
first check for this - if it's not an ``ApiProblemException``, we won't do
any special processing. And if it is, we can create a ``JsonResponse`` like
we might normally do in a controller::

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
                $statusCode
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

This is really powerful. If we need to return a "problem" anywhere in our
API, we only need to create an ``ApiProblem`` object and throw an ``ApiProblemException``.

Let's take advantage of this for our validation errors. Find ``handleValidationResponse``
and throw a new ``ApiProblemException`` instead of creating and returning
a response. And to keep things clear, let's also rename this function to
``throwApiProblemValidationException``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function throwApiProblemValidationException(array $errors)
    {
        $apiProblem = new ApiProblem(
            ApiProblem::TYPE_VALIDATION_ERROR
        );
        $apiProblem->set('errors', $errors);

        throw new ApiProblemException(400, $apiProblem);
    }

Now, just update ``newAction`` and ``updateAction`` to use the new function
name. We can also remove the ``return`` statements from each: we don't need
that anymore::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    // newAction() and updateAction()
    if ($errors = $this->validate($programmer)) {
        $this->throwApiProblemValidationException($errors);
    }

And when we run the tests, all green! Piece by piece, we're making our code
more consistent so that we guarantee that our API is consistent.

Handling 404 Errors
-------------------

We're handling validation errors and invalid JSON errors. The last big thing
is to properly handle 404 errors. In ``showAction`` and ``updateAction``,
we're throwing a special type of exception class to trigger a 404 response.
But in reality, the 404 response is a big HTML page. You can see this by
browsing to a made-up programmer:

    http://localhost:8000/api/programmers/fake

And actually, if we go to a completely made-up URL, we also see this same
HTML page. Internally, Silex throws that same exception to cause this 404
page.

Somehow, we need to be able to return JSON for *all* exceptions, not just
our fancy ``ApiProblemException``. In fact, since we want to be consistent,
we want to return a ``application/problem+json`` response.

First, let's write a test!

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: Proper 404 exception on no programmer
      When I request "GET /api/programmers/fake"
      Then the response status code should be 404
      And the "Content-Type" header should be "application/problem+json"
      And the "type" property should equal "about:blank"
      And the "title" property should equal "Not Found"

For the ``type`` field, I'm going to use ``about:blank``. Why? When we don't
have any extra information about an error beyond the status code, the spec
says we should use this, which is the default value for ``type``. I'm also
going to check that ``title`` equals ``Not Found``. Again, the spec says
that if we use ``about:blank`` for ``type``, then ``title`` should contain
the standard status code's description.    

Now let's get to work! The fix for this will be back inside our exception
listener function. Now, we want to handle *any* exception if the URL starts
with ``/api``. I can use this object to get Silex's ``Request`` object and
use an ``if`` statement to check for this::

    // src/KnpU/CodeBattle/Application.php
    // ...

    public function configureListeners()
    {
        $app = $this;

        $this->error(function(\Exception $e, $statusCode) use ($app) {
            // only act on /api URLs
            if (strpos($app['request']->getPathInfo(), '/api') !== 0) {
                return;
            }
        
            // ...

            return $response;
        });
    }

If you're not using Silex, just make sure you can check the current URL to
see if it's for your API.

Next, we need an ``ApiProblem`` object so we can create our ``application/problem+json``
response. If the exception is an instance of ``ApiProblemException``, then
we can get it easily. If not, then we have to do our best to create one::

    // src/KnpU/CodeBattle/Application.php
    // ...

    $this->error(function(\Exception $e, $statusCode) use ($app) {
        // only act on /api URLs
        if (strpos($app['request']->getPathInfo(), '/api') !== 0) {
            return;
        }

        if ($e instanceof ApiProblemException) {
            $apiProblem = $e->getApiProblem();
        } else {
            $apiProblem = new ApiProblem(
                null,
                $statusCode
            );
        }
        
        // ...
    });

In this second case, the only information we have is the status code. This
is where we should use ``about:blank`` as the type. But instead of doing
that here, let's add a bit of logic into ``ApiProblem``::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    // ...

    public function __construct($type = null, $statusCode)
    {
        $this->type = $type;
        $this->statusCode = $statusCode;

        if (!$type) {
            // no type? The default is about:blank and the title should
            // be the standard status code message
            $this->type = 'about:blank';
            $this->title = isset(Response::$statusTexts[$statusCode])
                ? Response::$statusTexts[$statusCode]
                : 'Unknown HTTP status code :(';
        } else {
            if (!isset(self::$titles[$type])) {
                throw new \InvalidArgumentException('No title for type '.$type);
            }

            $this->title = self::$titles[$type];
        }
    }

First, make ``$type`` optional. Then, if nothing is passed, set it to ``about:blank``.
Silex's ``Response`` class has a nice map of status codes and their short description,
which we can use to get a consistent title.

Back in ``configureListeners``, the rest is exactly like before: use ``ApiProblem``
to create a ``JsonResponse`` and set the ``application/problem+json`` ``Content-Type``
header on it. Now, if an exception is thrown from *anywhere* in the system
for a URL beginning with ``/api``, the client will get back an API problem
response. It took a little bit of work, but this is huge!

    // src/KnpU/CodeBattle/Application.php
    // ...

    $this->error(function(\Exception $e, $statusCode) use ($app) {
        // ...

        $response = new JsonResponse(
            $apiProblem->toArray(),
            $statusCode
        );
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    });

To make sure it's working, head back to the terminal and run the tests:

.. code-block::: bash

    $ php bin/vendor/behat

The green lights prove that even the 404 page is being transformed into a
proper API problem response.

The type key should be a URL
----------------------------

We're now returning an API problem response in every problem scenario in
our app. We can create these manually, like we did for validation errors.
or let them happen naturally, like when a 404 page occurs. We also have a
very systematic way to create error responses, so that they stay consistent.

One remaining problem is that the ``type`` should be a URL, not just a string.
One simple solution would be to prefix the ``type`` with the URL to some
documentation page and use our code as the anchor. Let's do this inside our
anonymous function::

    // src/KnpU/CodeBattle/Application.php
    // ...

    $data = $apiProblem->toArray();
    $data['type'] = 'http://localhost:8000/docs/errors#'.$data['type'];
    $response = new JsonResponse(
        $apiProblem->toArray(),
        $statusCode
    );

Of course, creating that page is still up to you. But we'll talk more about
documentation in the next episode.
