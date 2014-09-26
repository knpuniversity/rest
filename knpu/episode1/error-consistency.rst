Enforcing Consistency with ApiProblem
=====================================

We'll be returning a lot of ``application/problem+json`` responses, like
for validation errors, 404 pages and really any error response.

And of course, I want us to *always* be consistent. To make this really easy,
why not create a new ``ApiProblem`` class that holds all the fields we need?

Start by creating a new ``Api`` directory and class called ``ApiProblem``::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    namespace KnpU\CodeBattle\Api;

    class ApiProblem
    {
    }

.. note::

    The `Apigility`_ project has a similar class, which I liked and stole
    the basic idea :).

By looking at the spec, I've decided that I want my problem responses to
always have ``status``, ``type`` and ``title`` fields, so I'll create these
three properties and a ``__construct`` function that requires them. I'll also
create a ``getStatusCode`` function, which we'll use in a moment::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    namespace KnpU\CodeBattle\Api;

    class ApiProblem
    {
        private $statusCode;

        private $type;

        private $title;

        public function __construct($statusCode, $type, $title)
        {
            $this->statusCode = $statusCode;
            $this->type = $type;
            $this->title = $title;
        }

        public function getStatusCode()
        {
            return $this->statusCode;
        }
    }

Finally, since I'll need the ability to add additional fields, let's create
an ``$extraData`` array property and a ``set`` function that can be used to
populate it. We can use this to set the ``errors`` key when we're creating
a validation error response::

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
            400,
            'validation_error',
            'There was a validation error'
        );
        $apiProblem->set('errors', $errors);

        // ...
    }

Now, if we could turn the ``ApiProblem`` into an array, then we could just
pass it to the new ``JsonResponse`` and be done. To do that, add a new ``toArray``
function to ``ApiProblem``. We need to include the ``type``, ``title`` and
``status`` properties as well as any extra things we set on ``extraData``::

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
                    'status' => $this->statusCode,
                    'type' => $this->type,
                    'title' => $this->title,
                ]
            );
        }
    }

Cool! Use it and the ``getStatusCode`` function to create the ``JsonResponse``::

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
to model them. Very nice!

Constants: More Consistency
---------------------------

The ``type`` field is the unique identifier of an error, and we're supposed
to have documentation for each type. So it's really important to keep track
of these and never misspell them.

That sounds like a perfect use-case for constants! Add a cosntant on``ApiProblem``
for the ``validation_error`` key::

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
            400,
            ApiProblem::TYPE_VALIDATION_ERROR,
            'There was a validation error'
        );

        // ...
    }

Awesomely enough that's one less spot for me to screw up.

Mapping title to type
~~~~~~~~~~~~~~~~~~~~~

But we can go further. According to the spec, the ``title`` field is the
description of a given ``type``. In other words, we should have the exact
same ``title`` everywhere that we use the ``validation_error`` ``type``.

To force this consistency, create an array map on ``ApiProblem`` from
``type`` to its human-description::

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

    You can also choose to translate the ``title``. If you need this, you'll
    need to run the key through your translator before returning it.

And instead of passing the ``$title`` as the third argument to the constructor,
we can just look it up by the ``$type``. And like the good programmers we
are, we'll throw a huge, ugly and descriptive exception if we don't find
a title::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    // ...

    class ApiProblem
    {
        // ...

        public function __construct($statusCode, $type)
        {
            $this->statusCode = $statusCode;
            $this->type = $type;

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
            400,
            ApiProblem::TYPE_VALIDATION_ERROR
        );

        // ...
    }

Bam! We have an ``ApiProblem`` class to keep things consistent, a constant
for the one problem ``type`` we have so far, and a ``title`` that's automatically
chosen from the type.

.. _`Apigility`: http://www.apigility.org/
