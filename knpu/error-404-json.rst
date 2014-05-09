Handling 404 Errors
===================

We're handling validation errors and invalid JSON errors. The last big thing
is to properly handle 404 errors. In ``showAction`` and ``updateAction``,
we're throwing a special type of exception class to trigger a 404 response.
But in reality, the 404 response isn't JSON: it's a big HTML page. You can
see this by browsing to a made-up programmer:

    http://localhost:8000/api/programmers/fake

And actually, if we go to a completely made-up URL, we also see this same
HTML page:

    http://localhost:8000/api/foo/bar

Internally, Silex throws that same exception to cause this 404 page.

Somehow, we need to be able to return JSON for *all* exceptions, not just
our fancy ``ApiProblemException``. In fact, since we want to be consistent,
we *really* want to return a response with a ``application/problem+json``
``Content-Type``.

Writing the Test
----------------

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
says we should use this. I'm also going to check that ``title`` equals ``Not Found``.
Again, the spec says that if we use ``about:blank`` for ``type``, then ``title``
should contain the standard status code's description. 404 means "Not Found".  

Using the Exception Listener on all /api URLs
---------------------------------------------

Now let's get to work! To make this work, we'll go back to the exception
listener function. Now, we want to handle *any* exception, as long as the
URL starts with ``/api``. We can pass a handle to this object into my anonymous
function in order to  get Silex's ``Request``. With it, the ``getPathInfo``
function gives us a clean version of the URL that we can check::

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
see if it's for your API. Alternatively, you may have some other logic to
know if the current request is for your API.

Always Create an ApiProblem
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, we need an ``ApiProblem`` object so we can create our ``application/problem+json``
response. If the exception is an instance of ``ApiProblemException``, then
that's easy! If not, we need to do our best to create one::

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
Next, Silex's ``Response`` class has a nice map of status codes and their
short description. We can use to get a consistent title.

.. tip::

    It's a bit ugly to make ``$type`` (the first argument) optional, but
    ``$statusCode`` required. You may want to re-order these arguments.

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
Or we can let them happen naturally, like when a 404 page occurs. We also
have a very systematic way to create error responses, so that they stay consistent.

One last problem is that the ``type`` should be a URL, not just a string.
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
