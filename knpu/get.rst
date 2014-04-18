GET'ing Resources, and Content-Type
===================================

We just created ObjectOrienter -- how tough and scary-- now let's make an endpoint that's
able to fetch that programmer representation. Start by writing how it'll look when a 
client makes the request::

    // testing.php
    // ...

    // 2) GET a programmer resource
    $request = $client->get('/api/programmers/'.$nickname);
    $response = $request->send();

    echo $response;
    echo "\n\n";

The URL is ``/api/programmers/{nickname}``, where the ``nickname`` part changes
based on which programmer you want to get. In a RESTful API, the URL structures
don't actually matter. But to keep your sanity, if ``/api/programmers`` returns
the collection of programmers, then make ``/api/programmers/{id}`` return
a single programmer, where ``{id}`` is something unique. And be consistent:
if your collection resources are plural - like ``/api/programmers``, use the plural
form for all collection resources. If you make these URLs inconsistent you are going
to make your future self really really miserable. Be consistent and your API users
will leave you lots of happy emoticons.

Basic Routing and Controller
----------------------------

To make this endpoint work, go back to ``ProgrammerController``, and add another
routing line. But this time, make the URL respond to the ``GET`` method::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));

        $controllers->get('/api/programmers/{nickname}', array($this, 'showAction'));
    }

The ``{nickname}`` in the URL means that this route will be matched by any
GET request that looks like ``/api/programmers/*``. Next, make a ``showAction``
method and just return a simple message::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function showAction($nickname)
    {
        return 'Hello '.$nickname;
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
be in JSON, XML and if it's April Fools try some invented format.
We'll use JSON, because it's easy simple, and all languages support it.
To do this, first query for the programmer by using its nickname. I'll do
this using a ``findOneByNickname`` method from my simple ORM::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        // ...
    }

This returns a ``Programmer`` object. The code you use to query for data
will be different. The really important part is to finish with an object
that has all the data you want to send back in your API.

Next, just turn the ``Programmer`` object into an array manually. And
finally, create a new ``Response`` object just like the POST endpoint. 
But this time, set its body to be the JSON-encoded string::

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

The correct status code is 200. We'll learn about other status codes, but
you'll still use the good ol' 200 in most cases, especially for GET requests.

Test it out!

.. code-block:: bash

    $ php testing.php

.. code-block:: text

    HTTP/1.1 200 OK
    # ...
    Content-Type: text/html; charset=UTF-8

    {"nickname":"ObjectOrienter135","avatarNumber":"5","powerLevel":"0","tagLine":"a test dev!"}

But what's the Content-Type?
----------------------------

Perfect! Except that we're still telling the client that the content is written
in HTML. That's the job of the ``Content-Type`` response header, and it defaults
to ``text/html``. Our response is being dishonest right now, and we risk confusing
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

*How* you set a response header may be different in your app, but there is
definitely a way to do this. And because the ``Content-Type`` header is so
important, you may even have a shortcut method for it.

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
            $this->throw404('Crap! This programmer has deserted! We\'ll send a search party');
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
