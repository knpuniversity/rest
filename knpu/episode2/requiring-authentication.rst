Requiring Authentication
========================

Let's talk about API Authentication. The web part of our site *does* have
authentication. I can login as ``ryan@knplabs.com`` password ``foo``. By the way,
if that ever doesn't work for you, go back and just delete the SQLite database
we're using:

.. code-block:: bash

    rm data/code_battles.sqlite

This is because when we run our Behat tests, it's messing with our database.
That's not something we'd stand for. In a real project, we'd want to fix that!

When we're logged in as Ryan, any programmer we create is attached to the Ryan
user in the database. The web side has authentication and we know who created 
which programmers.

In our API, that's not the case at all: we have no authentication. And so
we're kind of faking the attachment of a programmer to a user. In ``newAction``
we call this function ``handleRequest``,  which lives right in this same class.
Then down here on the bottom, you can see *how* we fake it::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleRequest(Request $request, Programmer $programmer)
    {
        // update the programmer from the request data
        // ...

        $programmer->userId = $this->findUserByUsername('weaverryan')->id;
    }

We assume there's a user called ``weaverryan`` in the database, grab it's
``id`` and attach that to every programmer. So obviously that's just hardcoded
silliness and we need to get rid of it! We need some way for the API request
to authenticate itself as a user.

Denying Access with a 401 (in Behat)
------------------------------------

We're going to do this with tokens. You can create your own simple token
system or you can do something bigger like OAuth. The important thing to
realize is that the end results are the same: the request will send a token
and you'll look that up in some database and figure out which user the token
belongs to and if they have access. Using OAuth is just a different way to
help you handle those tokens and get those to your users.

Before we start anything let's write a new Behat feature file, because we haven't
done anything with authentication yet. For Behat newbies, this will feel
weird, but stick with me. I'm going to describe here the business value of
our authentication feature and who benefits from it, which is the API client.
That part is not important technically, but we'd pity the fool who doesn't
think about why they're building a feature:

.. code-block:: gherkin

    # features/api/authentication.feature
    Feature: Authentication
      In order to access protected resource
      As an API client
      I need to be able to authenticate


Below that, let's add our first scenario, which is going to be one where we
try to create a programmer without sending a token. Ultimately we want to
require authentication on most endpoints.

.. code-block:: gherkin

    # features/api/authentication.feature
    Feature: Authentication
      # ...

      Scenario: Create a programmer without authentication
        When I request "POST /api/programmers"
        Then the response status code should be 401
        And the "detail" property should equal "Authentication Required"

If I just post to ``/api/programmers`` with no token, the response I get
should be 401. We don't know what the response is going to look like yet,
but let's imagine that it's JSON and has some ``detail`` key on it which
gives us a little bit of information about what went wrong. 

So there we go! No coding yet, but we know how things should look if we don't
send a token. If we try this right now - which is always a good idea - we
should see it fail. By the way we can run one feature file at a time, and
that's going to be really useful for us:

.. code-block:: bash

    php vendor/bin/behat features/api/authentication.feature

And there we go! You can see it's giving us a 400 error because we're sending
invalid JSON instead of the 401 we want.

Denying Access in your Controller
---------------------------------

So let's go into ``ProgrammerController`` and fix this as easily as possible
up in ``newAction``. I already have a function called ``getLoggedinUser()``,
which will either give us a ``User`` object or give us ``null`` if the request
isn't authenticated. What we can do is check if ``!getLoggedInUser()`` and
then return the access denied page. In Silex, you do this by throwing a special
exception called ``AccessDeniedException``. Make sure you have the right
``use`` statement for this, which is in the ``Security`` component::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...
    
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;
    // ...

    public function newAction(Request $request)
    {
        if (!$this->isUserLoggedIn()) {
            throw new AccessDeniedException();
        }
        // ..
    }

And there we go! Since that should deny access, let's try our test:

.. code-block:: bash

    php vendor/bin/behat features/api/authentication.feature

Boom! And this time you can see that we *are* passing the 401 test. Get outta
here ya tokenless request! The only issue is that the response body is coming
back slightly  different than we expected. There *is* a ``detail`` property,
but instead of it  being set to "authentication required", it's set to this
``not privileged to request the resource`` string. And yea, it's not really
obvious where that's coming from.

What Happens when you Deny Access Anyways?
------------------------------------------

But first, how is this even working behind the scenes? If you were with us
for episode 1, we did some pretty advanced error handling stuff at the bottom
of this ``Application`` class. What we basically did was add a function that's
called anytime there's an exception thrown *anywhere*. Then, we transform
that into a consistent JSON response that follows the `API problem`_ format.
So any exception gives us a nice consistent response::

    src/KnpU/CodeBattle/Application.php
    // ...
    
    $this->error(function(\Exception $e, $statusCode) use ($app) {
        // ...

        $data = $apiProblem->toArray();

        $response = new JsonResponse(
            $data,
            $apiProblem->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');
        
        return $response;
    });

And hey, when we deny access, *we're throwing an exception*! But bad news,
our exception is the *one* weird guy in the *whole* system: instead of being
handled here, it's handled somewhere else entirely.

ApiEntryPoint: Where Security Responses are Created
---------------------------------------------------

Without getting too far into things, I've already written *most* of the logic
for our token authentication system, and I'll show you the important parts
but not cover how this all hooks up. If you have more detailed questions,
just ask them in the comments.

Let's open up a class in my ``Security/Authentication`` directory called
``ApiEntryPoint``. When we deny access, the ``start()`` method is called.
Here, it's our job to return a Response that says "hey, get outta here!"::

    // src/KnpU/CodeBattle/Security/Authentication
    // ...

    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
    // ...

    class ApiEntryPoint implements AuthenticationEntryPointInterface
    {
        // ...

        public function start(Request $request, AuthenticationException $authException = null)
        {
            $message = $this->getMessage($authException);

            $response = new JsonResponse(array('detail' => $message), 401);

            return $response;
        }

        /**
         * Gets the message from the specific AuthenticationException and then
         * translates it. The translation process allows us to customize the
         * messages we want - see the translations/en.yml file.
         */
        private function getMessage(AuthenticationException $authException = null)
        {
            $key = $authException ? $authException->getMessageKey() : 'authentication_required';
            $parameters = $authException ? $authException->getMessageData() : array();

            return $this->translator->trans($key, $parameters);
        }
    }

So *that's* where the ``detail`` key is coming from, and it's set to some
internal message that comes from the deep dark core of Silex's security that
describes what went wrong.

Fortunately, I'm also translating that message, and we setup translations
in episode 1. in this ``translations/`` directory. This is just a little
key value pair. So let's copy that unhelpful "this not privileged request"
thing that Silex's gives us and translate it to something nicer:

.. code-block:: yaml

    # translations/en.yml
    authentication_required: Authentication Required
    "Not privileged to request the resource.": "Authentication Required"

Ok, rerun the tests!

.. code-block:: bash

    php vendor/bin/behat features/api/authentication.feature

Fantastic!

.. _`API problem`: https://apigility.org/documentation/api-primer/error-reporting
