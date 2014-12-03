Centralizing Error Response Creation
====================================

Let's go further! Even the creation of the response is error prone. So right
now, in both the ``Application`` class where we have our error handler *and*
inside of this ``ApiEntryPoint``, we create the ``JsonResponse`` and we set
the ``Content-Type`` header to ``application/problem+json`` by hand. I don't
want to have a lot of these laying around: I want to go through one central
spot.

The fix for this has nothing to do with Silex or API's: we're just going
to do a bit of refactoring and repeat ourselves a little bit less.

Lets create a new PHP class called ``APIProblemResponseFactory`` and its
job will be to create API problem responses::

    // src/KnpU/CodeBattle/Api/ApiProblemResponseFactory.php
    namespace KnpU\CodeBattle\Api;

    class ApiProblemResponseFactory
    {
    }

So we'll create a single function called ``createResponse`` and it will take
in an ``ApiProblem`` object and create the response for that. And most of
this we can just copy from our error handler code::

    // src/KnpU/CodeBattle/Api/ApiProblemResponseFactory.php
    // ...
    use Symfony\Component\HttpFoundation\JsonResponse;

    class ApiProblemResponseFactory
    {
        public function createResponse(ApiProblem $apiProblem)
        {
            $data = $apiProblem->toArray();
            // making type a URL, to a temporarily fake page
            if ($data['type'] != 'about:blank') {
                $data['type'] = 'http://localhost:8000/docs/errors#'.$data['type'];
            }
            $response = new JsonResponse(
                $data,
                $apiProblem->getStatusCode()
            );
            $response->headers->set('Content-Type', 'application/problem+json');

            return $response;
        }
    }

I'll make sure that I add a couple of ``use`` statements here. Perfect,
it takes in the ``ApiProblem``, transforms that into json, and makes sure
that the ``Content-Type`` header is set. So if we can use this instead of
repeating that logic elsewhere, it's going to save us some trouble.

Creating an api.response_factory Service
----------------------------------------

Like we saw before, inside of Silex there is a way to create global objects
called services. We did this for the ``serializer``, which let us use it
in multiple  places. So I'm going to do the same thing with the ``api.response_factory``.
And we'll just ``return new ApiProblemResponseFactory``. Of course, like
anything else don't forget to add the ``use`` statement for that::

    // src/KnpU/CodeBattle/Application.php
    // ...
    
    use KnpU\CodeBattle\Api\ApiProblemResponseFactory;
    // ...

    private function configureServices()
    {
        // ...

        $this['api.response_factory'] = $this->share(function() {
            return new ApiProblemResponseFactory();
        });
    }

Yes, this class is getting a little crazy. And that's it!

Down inside this class we'll use that key to access the object and make use
of it. I have that same ``$app`` variable, so I can get rid of all this stuff
here. Pass the  ``ApiProblem`` object to ``createResponse`` and there we
go!

.. code-block:: php

    private function configureListeners()
    {
        $app = $this;

        $this->error(function(\Exception $e, $statusCode) use ($app) {
            // $apiProblem = ...
            // existing code ...

            /** @var \KnpU\CodeBattle\Api\ApiProblemResponseFactory $factory */
            $factory = $app['api.response_factory'];

            return $factory->createResponse($apiProblem);
        });
    }    

Injecting ApiProblemResponseFactory into ApiEntryPoint
------------------------------------------------------

We can do the same thing inside the ``ApiEntryPoint``. I need to practice
a little bit of dependency injection, and if this is a new idea to you
or going over your head, we have a free tutorial about `dependency injection`_.
I highly recommend you check it out, it's going to change the way you code. 

So in ``Application``, I'm going to find the entry point and I'm actually going
to go past that new factory object right to it as the second argument to
the ``__construct`` function of our ``ApiEntryPoint``::

    // src/KnpU/CodeBattle/Application.php
    // ...
    
    private function configureSecurity()
    {
        $app = $this;

        // ...

        $this['security.entry_point.'.$name.'.api_token'] = $app->share(function() use ($app) {
            return new ApiEntryPoint($app['translator'], $app['api.response_factory']);
        });

        // ...
    }

This means here I will now have a second argument. Don't forget the ``use``
statement for that and we'll just set that on a new property::

    // src/KnpU/CodeBattle/Security/Authentication/ApiEntryPoint.php
    // ...

    use KnpU\CodeBattle\Api\ApiProblemResponseFactory;

    class ApiEntryPoint implements AuthenticationEntryPointInterface
    {
        private $translator;

        private $responseFactory;

        public function __construct(Translator $translator, ApiProblemResponseFactory $responseFactory)
        {
            $this->translator = $translator;
            $this->responseFactory = $responseFactory;
        }

        // ...
    }

So now, when this object is created we're going to have access to this
``ApiProblemResponseFactory``. Down below, we can just use it::

    // src/KnpU/CodeBattle/Security/Authentication/ApiEntryPoint.php
    // ...

    class ApiEntryPoint implements AuthenticationEntryPointInterface
    {
        private $responseFactory;

        // ...
        
        public function start(Request $request, AuthenticationException $authException = null)
        {
            $message = $this->getMessage($authException);

            $problem = new ApiProblem(401, ApiProblem::TYPE_AUTHENTICATION_ERROR);
            $problem->set('detail', $message);

            return $this->responseFactory->createResponse($problem);
        }
    }

So we still create the ``ApiProblem`` object, but I don't want to do any
of this other stuff. And that's it! We just reduced duplication, let's try
our tests:

.. code-block:: bash

    php vendor/bin/behat features/api/authentication.feature

Those pass too! Let's try all of our tests for the programmer.

.. code-block:: bash

    php vendor/bin/behat features/api/programmer.feature

Sahweet! They're passing too! So there's no chance of duplication because
everything is going through that same class.

.. _`dependency injection`: https://knpuniversity.com/screencast/dependency-injection
