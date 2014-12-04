Authentication Error Format
===========================

It's finally time to create a scenario to check and see what happens if we 
send an *invalid* token. So let's do that right now.

In this case, I'm *not* going to add a user to the database with this token.
I'm just going to send a token and that doesn't exist:

.. code-block:: gherkin

    # features/api/authentication.feature
    # ...

    Scenario: Invalid token gives us a 401
      Given I set the "Authorization" header to be "token ABCDFAKE"
      When I request "POST /api/programmers"
      Then the response status code should be 401
      And the "detail" property should equal "Invalid Credentials"

Then, we'll just make any request that requires authentication. The response
status code should be 401 and remember we're always returning that API problem
format that has a ``detail`` property on it. And here, we can say whatever
we want. To be nice to the users, let's set it to "Invalid Credentials" so
they know what went wrong. It's not like they forgot the token, it just wasn't
valid.

Let's try this out. Again we can run Behat on one particular scenario. This
one starts on line 11:

.. code-block:: bash

    php vendor/bin/behat features/api/authentication.feature:11

In fact you can see it *almost* passed, so out of the box things are working.
We are denying access, sending a 401, and because of our security error handling
in that ``ApiEntryPoint`` class, we're sending a nice api problem format with
the actual ``detail`` set to "Invalid Credentials." Like before, this message
comes from deep inside Silex and is describing what's going wrong.

And because I want people to be excited about our API, I'm even going to
add an exclamation point to this:

.. code-block:: gherkin

    # features/api/authentication.feature
    # ...

    Scenario: Invalid token gives us a 401
      # ...
      And the "detail" property should equal "Invalid Credentials"

We'll see the difference this makes. We're expecting "Invalid Credentials!"
and we are getting it with a period. So let's go find our translation file
and and change this to our version:

.. code-block:: yaml

    # translations/en.yml
    # ...

    "Invalid credentials.": "Invalid credentials!""

That should do it! Let's rerun things. Woops! I made a mistake - take that
extra quote off. And I made one other mistake: it's catching me on a case
difference. So this is why it is good to have tests, they have closer eyes
than we do. So I'll say "Invalid Credentials!" and a capital letter:

.. code-block:: yaml

    # translations/en.yml
    # ...

    "Invalid credentials.": "Invalid Credentials!""

Perfect! 

Returning application/problem+json for Security Errors
------------------------------------------------------

Next, we need all of our errors to *always* return that same API problem
response format. And when we return this format, we should always send back
its special ``Content-Type`` so let's make sure it's correct:

.. code-block:: gherkin

    # features/api/authentication.feature
    # ...

    Scenario: Create a programmer without authentication
      # ...
      And the "Content-Type" header should be "application/problem+json"

Ahh! It's not coming back with that. We are getting an application/problem-like
format, but without the right ``Content-Type`` header. It's coming back as
a simple ``application/json``.

In our app, when an exception is thrown, there are 2 different places that
take care of things. Most errors are handled in the ``Application`` class.
We added this in episode 1. But security errors are handled in ``ApiEntryPoint``,
and it's responsible for returning some helpful response::

    // src/KnpU/CodeBattle/Security/Authentication/ApiEntryPoint.php
    // ...

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $message = $this->getMessage($authException);

        $response = new JsonResponse(array('detail' => $message), 401);

        return $response;
    }

So for example here, you can see why we get the ``detail`` and why we get
the 401. If I change this to 403, this proves that this class is responsible
for the error responses. Let's add the ``application/problem+json`` 
``Content-Type`` header::

    // src/KnpU/CodeBattle/Security/Authentication/ApiEntryPoint.php
    // ...

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $message = $this->getMessage($authException);

        $response = new JsonResponse(array('detail' => $message), 401);
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }

Using the ApiProblem Class For Security Errors
----------------------------------------------

For consistency, one of the things we did in Episode 1 is actually create
an ``ApiProblem`` class. The idea was whenever you had some sort of error
response you needed to send back, you could create this ``ApiProblem`` object,
which will help you structure things and avoid typos in any keys.

Right now inside of the ``ApiEntryPoint``, we're kind of creating the API
problem structure by hand, which is something I don't want to do. Let's leverage
our ``ApiProblem`` class instead.

So first, I'm closing a couple of these classes. Inside ``ApiProblem`` there
is a ``type`` property. The `spec document`_ that describes this format says
that we should have a ``type`` field and that it should be a unique string
for each error in your application. Right now we have two: ``validation_error``
as one unique thing that can go wrong and ``invalid_body_format`` as another::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    // ...

    class ApiProblem
    {
        const TYPE_VALIDATION_ERROR = 'validation_error';
        const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';

        // ...
    }

That's if the client sends us json, but the json is malformed. Now we have
a third type of error, which is when you send us bad credentials. So let's
add a new constant here called ``authentication_error``. And I'm just making
up this string, it's not terribly important. And then down here is a map
from those types to a human readable text that will live on the ``title``
key::

    // src/KnpU/CodeBattle/Api/ApiProblem.php
    // ...

    class ApiProblem
    {
        // ...
        const TYPE_AUTHENTICATION_ERROR = 'authentication_error';

        private static $titles = array(
            // ...
            self::TYPE_AUTHENTICATION_ERROR => 'Invalid or missing authentication',
        );
    }

The purpose of this is that when we create a new ``ApiProblem``, we are forced
to pass in a ``type`` and then that has a nice little map to the title. So
given a certain ``type``, you always get this nice same identical human readable
explanation for it. You don't have to duplicate the titles all around your
codebase. 

Back in ``ApiEntryPoint``, instead of this stuff, you can create a new ``ApiProblem``
object. Add our ``use`` statement for that. The status code we know is 401
and the ``type`` is going to be our new ``authentication_error`` type::

    // src/KnpU/CodeBattle/Security/Authentication/ApiEntryPoint.php
    // ...

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $message = $this->getMessage($authException);

        $problem = new ApiProblem(401, ApiProblem::TYPE_AUTHENTICATION_ERROR);
        $problem->set('detail', $message);

        $response = new JsonResponse($problem->toArray(), 401);
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }

So it's a nice way to make sure we don't just invent new types all over the place.

And then, we set the ``detail``. The ``detail`` is going to be the message
that comes from Silex whenever something goes wrong related to security.
Based on what went wrong, we will get a different message here and we can
use the translator to control it.

Then down here for the response, we can say just ``new JsonResponse``. For
the content, we can say ``$problem->toArray()``. This is a function we used
earlier: it just takes all those properties and turns them into an array.
Now we'll use ``$problem->getStatusCode()``. And we'll keep the response
headers already set.

So this is a small improvement. I'm more consistent in my code, so my API
will be more consistent too. If I need to create an api problem response,
I won't do it by hand. The ``ApiProblem`` class does some special things
for us, attaching the title and making sure we have a few defined types. If we
try this, we should get the same result as before and we do. Perfect. 

.. _`spec document`: https://tools.ietf.org/html/draft-nottingham-http-problem-07
