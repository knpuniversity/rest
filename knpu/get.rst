GET'ing Resources, and Content-Type
===================================

Since we can create programmer resources, let's make an endpoint that's able
to GET an individual programmer representation. Let's start by adding *how*
we want this endpoint to look to our ``testing.php`` script::

    // testing.php
    // ...

    // 2) GET a programmer resource
    $request = $client->get('/api/programmers/'.$nickname);
    $response = $request->send();

    echo $response;
    echo "\n\n";

The URL is ``/api/programmers/{nickname}``, where the ``nickname`` part changes
based on which programmer you want to get. In a RESTful API, the URL structures
don't actually matter. But to keep your sanity, if you ``/programmers`` returns
the collection of programmers, then make ``/programmers/{id}`` the URI to
a single programmer, where ``{id}`` is an id or something else unique. And
be consistent: if your collection resources are plural - like ``/programmers``,
use the plural form for all collection resources. If you want to make a frustrating
API, being inconsistent is the best way.

Basic Routing and Controller
----------------------------

To make this endpoint work, go back to ``ProgrammerController``, add another
routing line, but make this page respond to the ``GET`` method::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));

        $controllers->get('/api/programmers/{nickname}', array($this, 'showAction'));
    }

The ``{nickname}`` in the URL means that this route will be matched by any
GET request that has ``/api/programmers/*``. Next, make a ``showAction`` method
and just return a simple message::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function showAction($nickname)
    {
        return new Response('Hello '.$nickname);
    }

If we go to ``/api/programmers/foo``, the ``$nickname`` variable will be
equal to ``foo``. This is special to Silex, but you can do this kind of stuff
with any framework.

Ok, try out the testing script:

.. code-block:: bash

    $ php testing.php

.. code-block:: text

    HTTP/1.1 200 OK
    # ...
    Content-Type: text/html; charset=UTF-8

    Hello ObjectOrienter366

Returning a JSON Response
-------------------------

Our goal is to return a representation of the programmer resource. This could
be in JSON, XML or some invented format if you really hate your API users.
We'll use JSON, because it's easily to create and easily understood by all
clients. To do this, I'll first look up the programmer by its nickname using
my simple ORM. This gives us a ``Programmer`` object, which we can easily
turn into an array. Now, just create a new ``Response`` object and ``json_encode``
the array so that we're returning a nice JSON string::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        $data = array(
            'nickname' => $programmer->nickname,
            'avatarNumber' => $programmer->avatarNumber,
            'powerLevel' => $programmer->powerLevel,
            'tagLine' => $programmer->tagLine,
        );

        return new Response(json_encode($data), 200);
    }

The status code here is 200 Ok. We're going to learn about several other
status codes, but you'll still use this in most cases, especially for GET
requests.

Test it out!

.. code-block:: bash

    $ php testing.php

.. code-block:: text

    HTTP/1.1 200 OK
    # ...
    Content-Type: text/html; charset=UTF-8

    {"nickname":"ObjectOrienter135","avatarNumber":"5","powerLevel":"0","tagLine":"a test dev!"}

But what's the Content-Type
---------------------------

Perfect! Except that we're still telling the client that the content is written
in HTML. That's the job of the ``Content-Type`` response header, and it defaults
to ``text/html``. Our response is just dishonest right now, and we risk confusing
an API client.

Fix this by manually setting the ``Content-Type`` header on the ``Response``
before returning it::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function showAction($nickname)
    {
        // ...

        $response = new Response(json_encode($data), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

*How* you set a header may be different in your app, but there is definitely
a way to do this. And because the ``Content-Type`` header is so important,
you may even have a shortcut method for it.

.. note::

    For example, in Laravel, you can return a JSON response (with correct
    ``Content-Type``) with::
    
        Response::json(array('name' => 'Steve', 'state' => 'CA'));

We're now returning a JSON representation of the resource, setting its ``Content-Type``
header correctly and using the right status code. Great work.

404 Pages
---------

But let's not forget to return a 404 if we're passed a bad nickname. In our
app, I've created a shortcut for this called ``throw404``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if (!$programmer) {
            $this->throw404();
        }

        // ...
    }

Under the surface, this throws a special type of exception that's converted
by Silex into a 404 response. In your app, just return a 404 page however
you normally do.

Try it out by temporarily changing our testing script to point to a made-up
nickname::

    // testing.php
    // ...

    // 2) GET a programmer resource
    $request = $client->get('/api/programmers/abcd'.$nickname);
    $response = $request->send();

    echo $response;
    echo "\n\n";

When we run the script now, we *do* see a 404 page, though it's a big ugly
HTML page instead of JSON. We'll talk about properly handling API errors
later.

Updating the Location Header
----------------------------

Hey, we have a working endpoint to view a single programmer! Remember the
``Location`` header we return after creating a new programmer? Let's update
that to be a real value.

To do this, first add a ``bind`` function to our programmer route::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));

        $controllers->get('/api/programmers/{nickname}', array($this, 'showAction'))
            ->bind('api_programmers_show');
    }

This gives the route an internal name of ``api_programmers_show``. We can
use that below to generate a proper URL to the new programmer resource::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...

        $response = new Response('It worked. Believe me - I\'m an API', 201);
        $programmerUrl = $this->generateUrl(
            'api_programmers_show',
            ['nickname' => $programmer->nickname]
        );
        $response->headers->set('Location', $programmerUrl);

        return $response;
    }

The ``generateUrl`` method is a shortcut I added for our app, and it combines
the ``nickname`` with the rest of the URL for the route. This will be different
outside of Silex, but very similar.

.. tip::

    The ``generateUrl`` method is just a shortcut for doing this:
    
        $programmerUrl = $this->container['url_generator']->generate(
            'api_programmers_show',
            ['nickname' => $programmer->nickname]
        );

Let's update the ``testing.php`` script to print out the response from the
original POST so we can check this out::

    // testing.php
    // ...

    // 1) Create a programmer resource
    $request = $client->post('/api/programmers', null, json_encode($data));
    $response = $request->send();

    echo $response;
    echo "\n\n";
    die;

    // 2) GET a programmer resource
    // ...

And when we run it again, we've got a working ``Location`` header:

.. code-block:: text

    HTTP/1.1 201 Created
    ... 
    Location: /api/programmers/ObjectOrienter330

    It worked. Believe me - I'm an API

Using the Location Header
-------------------------

The purpose of the ``Location`` header is to help the client know where to
go next without needing to hardcode URLs or URL patterns. In fact, we can
update our testing script to read the ``Location`` header and use it for
the next request::

    // testing.php
    // ...

    // 1) Create a programmer resource
    $request = $client->post('/api/programmers', null, json_encode($data));
    $response = $request->send();

    $programmerUrl = $response->getHeader('Location');

    // 2) GET a programmer resource
    $request = $client->get($programmerUrl);
    $response = $request->send();

    echo $response;
    echo "\n\n";

That's really powerful. But it's also where things start to get complicated.
But we'll save that for later!

GET /programmers: A collection of Programmers
---------------------------------------------

We now have 2 URLs and 2 resources:

* ``/programmers``, which represents a collection of resources;
* ``/programmers/{nickname}``, which represents on programmer.

We can't yet make a GET request to ``/programmers``, and there's nothing
that says we *must* make this possible, it's up to us to decide if we need
it. But most of the time, you *will* make this possible, and your API client
will probably assume it exists anyways.

Like always, let's start by updating our testing script to try the new endpoint::

    // testing.php
    // ...

    // 3) GET a list of all programmers
    $request = $client->get('/api/programmers');
    $response = $request->send();

    echo $response;
    echo "\n\n";

Next, create a new route that points to a new ``listAction`` method in our
``ProgrammerController`` class::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        // the 2 other routes ...

        $controllers->get('/api/programmers', array($this, 'listAction'));
    }

I'll copy the ``showAction`` and modify it for ``listAction``. We'll query
for *all* programmers for now, then transform them all into a big array::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()->findAll();
        $data = array('data' => array());
        foreach ($programmers as $programmer) {
            $data['data'][] = $this->serializeProgrammer($programmer);
        }

        $response = new Response(json_encode($data), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

The ``serializeProgrammer`` method doesn't exist yet, but we can create it
by using the code from ``showAction`` to avoid duplication. We're going to
use some fancier methods of turning objects into JSON a bit later::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function showAction($nickname)
    {
        // ...

        // replace the manual creation of the array with this function call
        $data = $this->serializeProgrammer($programmer);

        // ...
    }

    private function serializeProgrammer(Programmer $programmer)
    {
        return array(
            'nickname' => $programmer->nickname,
            'avatarNumber' => $programmer->avatarNumber,
            'powerLevel' => $programmer->powerLevel,
            'tagLine' => $programmer->tagLine,
        );
    }

Let's try it out!

.. code-block:: bash

    $ php testing.php

.. code-block:: text

    HTTP/1.1 200 OK
    ... 
    Content-Type: application/json

    {
        "data": [
            {
                "nickname":"ObjectOrienter14",
                "avatarNumber":"5",
                "powerLevel":"0",
                "tagLine":null
            },
            {
                "nickname":"ObjectOrienter795",
                "avatarNumber":"5",
                "powerLevel":"0",
                "tagLine":"a test dev!"
            }
        ]
    }

Awesome! Why did I put things under a ``data`` key? Actually, no special
reason, I just invented this. But there *are* pre-existing standards for
organizing your JSON structures, an important idea we'll talk about later.
For now, we'll just worry about being consistent throughout the API.

Fixing the Content-Type on POST
-------------------------------

We now have 3 working endpoints, but one still has a big issue. The POST
*still* returns a text string as its response. So what *should* a POST body
contain after creating a resource? Your best option is to return a representation
of the new resource. So let's do that::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...
        $this->save($programmer);

        $data = $this->serializeProgrammer($programmer);
        $response = new Response(
            json_encode($data),
            201
        );
        $programmerUrl = $this->generateUrl(
            'api_programmers_show',
            ['nickname' => $programmer->nickname]
        );
        $response->headers->set('Location', $programmerUrl);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

And of course, don't forget to set the ``Content-Type`` header to ``application/json``.
To test, print out that response temporarily and try it::

    // testing.php
    // ...

    // 1) Create a programmer resource
    $request = $client->post('/api/programmers', null, json_encode($data));
    $response = $request->send();

    echo $response;
    echo "\n\n";die;

And actually, since returning JSON is so common, Silex has a shortcut: the
``JsonResponse`` class. It takes care of running ``json_encode`` *and* setting
the ``Content-Type`` header for us::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...
    use Symfony\Component\HttpFoundation\JsonResponse;

    public function newAction(Request $request)
    {
        // ...
        $this->save($programmer);

        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, 201);
        $programmerUrl = $this->generateUrl(
            'api_programmers_show',
            ['nickname' => $programmer->nickname]
        );
        $response->headers->set('Location', $programmerUrl);

        return $response;
    }

That's just there for convenience, but it cuts down on some code.

Finding Spec Information
------------------------

By the way, how do I know these rules, like that a 201 response should have
a status code or that it should return the entity body? These guidelines
come from the IETF and the W3C in the form of big technical RFC's. They're
not always easy to interpret, but sometimes they're awesome. For example,
if you google for ``http status 201`` you'll find a the famous `RFC 2616`_,
which gives us the details about the 201 status code and most of the underlying
guidelines for how HTTP works.

I'll help you navigate these rules. But as we go, try googling for answers
and seeing what's out there.

.. _`RFC 2616`: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
