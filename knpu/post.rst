POST, 201, Location header and Testing
======================================

Start by `downloading`_ or `cloning`_ the project. Next, follow the `README.md`_
file to get things working. It involves `downloading Composer`_ and installing
the vendor libraries:

.. code-block:: bash

    $ git clone https://github.com/knpuniversity/rest.git
    $ cd rest
    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar install

When that's done, the easiest way to get the app running is to use the built-in
PHP web server:

.. code-block:: bash

    $ cd web
    $ php -S localhost:8000

.. note::

    The built-in web server requires PHP 5.4, which all of you should have!
    If you're using PHP 5.3, you'll need to configure a VirtualHost of your
    web server to point at the ``web/`` directory.

If it worked, then load up the site by going to ``http://localhost:8000``.
Awesome!    

About the App
-------------

Our application is built in `Silex`_, a PHP microframework. If you're not
familiar with Silex, take a few minutes in `its Documentation`_ to get familiar
with it. It basically let's us design routes, or pages and easily write the
code to render those pages. Our setup will look just a little bit different
than this, but the idea is the same.

And wost of what we'll do will basically be the same in any other framework.
Sure, you'll need to do a little bit of work to hook in your framework's validation
system instead of the one we'll use, but these things are easy compared with
all the tough REST stuff.

First Endpoint: POST /api/programmers
-------------------------------------

Imagine we're building the API to support an iPhone app. Other than authentication,
which we'll push off until later, what's the first thing the user will do?
Create a programmer of course. And that's our first API endpoint.

Basic Routing
~~~~~~~~~~~~~

Find the ``src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php``
file and uncomment the route definition::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));
    }

Next, create a ``newAction`` inside of this class and just return the classic
and boring ``hello world!``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction()
    {
        return 'hello world!';
    }

This creates a new endpoint with the URL ``/api/programmers``. If we make
a POST request here, the ``newAction`` function will be executed.

URLs and Resources
~~~~~~~~~~~~~~~~~~

Remember that every URL is the address to a resource. Here, ``/api/programmers``
is a resource that represents the collection of programmers in the system.
Actually, ``/api`` is just a prefix we're using to keep our API separate

 we're choosing to have so that our API
doesn't crash into our application.


A resource can be one thing - like a programmer - or many things - like a
collection of programmers. Typically, when you POST to a collection, you're
saying that you want to add a new item to it.

Testing the Endpoint
~~~~~~~~~~~~~~~~~~~~

Let's test it so far! That's actually not so easy in a browser, since only
a POST request will work. Instead, open up the ``testing.php`` file::

    <?php
    // testing.php
    require __DIR__.'/vendor/autoload.php';

    use Guzzle\Http\Client;

    // create our http client (Guzzle)
    $client = new Client('http://localhost:8000', array(
        'request.options' => array(
            'exceptions' => false,
        )
    ));


All this does so far is instantiate a `Guzzle`_ Client object. Guzzle is
a crazy-good library that lets you make HTTP curl requests and receive responses.
If you're talking to an API in PHP, this is what you use.

Let's make a POST request to ``/api/programmers`` and print out the response::

    // testing.php
    // ...
    $client = new Client('http://localhost:8000', array(
        'request.options' => array(
            'exceptions' => false,
        )
    ));

    $request = $client->post('/api/programmers');
    $response = $request->send();

    echo $response;
    echo "\n\n";

Try it out by running the file from the command line. You'll need to open
a new terminal tab and make sure you're at the root of the project where
the file is:

.. code-block:: bash

    $ php testing.php

.. code-block:: test

    HTTP/1.1 200 OK
    Host: localhost:8000
    Connection: close
    Cache-Control: no-cache
    Content-Type: text/html; charset=UTF-8

    hello world!

Success!

Designing the POST
------------------

In reality, we're going to pass some programmer details up to the server.
In REST-speak, we're passing a representation of a programmer, which can
be done in a number of different formats. It's invisible to us, but HTML
forms do this by sending data in a format called ``application/x-www-form-urlencoded``:

.. code-block:: text

    POST /api/programmers HTTP/1.1
    Host: localhost:8000
    Content-Type: application/x-www-form-urlencoded
    
    nickname=Geek+Dev1&avatarNumber=5

PHP automatically reads that and puts it into the ``$_POST`` super global.
That's fine for the web, but in the API world, this is ugly. Instead, we'll
usually pass the representation as XML or JSON:

    POST /api/programmers HTTP/1.1
    Host: localhost:8000
    Content-Type: application/json
    
    {
        "nickname": "Geek Dev1",
        "avatarNumber": 5
    }

Creating a request like this with Guzzle is easy::

    // testing.php
    // ...

    $nickname = 'ObjectOrienter'.rand(0, 999);
    $data = array(
        'nickname' => $nickname,
        'avatarNumber' => 5,
        'tagLine' => 'a test dev!'
    );

    $request = $client->post('/api/programmers', null, json_encode($data));
    $response = $request->send();

    echo $response;
    echo "\n\n";

The second ``null`` argument is an array of request headers we want to send.
We're not worried about that yet, so we can just leave it blank.

Coding up the Endpoint
----------------------

Back in the ``ProgrammerController`` class, let's start coding to make this
work. First, how do we get the JSON string passed in the request? In Silex,
you do this by getting the ``Request`` object and calling ``getContent()``
on it. Let's just return the data from the endpoint so we can see it::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        $data = $request->getContent();
        return $data;
    }

.. tip::

    Your framework will likely have a shortcut for getting the request content
    or body. But if it doesn't, you can get it by using this strange bit
    of code::
    
        $data = file_get_contents('php://input');

    This is a special stream that reads the request body. For more details,
    see `php.net: php://`_.

Try running our ``testing.php`` file again::

.. code-block:: bash

    $ php testing.php

This time, you should see the JSON string being echo'ed back at you:

.. code-block:: text

    HTTP/1.1 200 OK
    ...
    Content-Type: text/html; charset=UTF-8

    {"nickname":"ObjectOrienter31","avatarNumber":5}

Awesome! Now that we have the JSON string, we can just decode it and start
creating a new ``Programmer`` object.

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $programmer = new Programmer();
        $programmer->nickname = $data['nickname'];
        $programmer->avatarNumber = $data['avatarNumber'];
        $programmer->tagLine = $data['tagLine'];
        $programmer->userId = $this->findUserByUsername('weaverryan')->id;

        $this->save($programmer);

        return 'It worked. Believe me - I\'m an API';
    }

Our app already comes ready with classes for ``Programmer``, ``Battle`` and
``Project``, as well as a really simple ORM. At the bottom, I'm just returning
a really reassuring message that everything went ok.

I've also added one really ugly detail::

    $programmer->userId = $this->findUserByUsername('weaverryan')->id;

Every programmer is created and owned by one user. On the web, making this
relation is simple, because I'm logged in. But our API is completely anonymous
so far. We'll fix this, but for now - I'll just make *every* programmer owned
by me. Make sure to use my username - it's setup as test data that'll always
be in our database.

Moment of truth! Run the testing script again:

.. code-block:: bash

    $ php testing.php

.. code-block:: text

    HTTP/1.1 200 OK
    Host: localhost:8000
    ... 
    Content-Type: text/html; charset=UTF-8

    It worked. Believe me - I'm an API

The message tells us that it probably worked. And if you login as ``weaverryan``
with password ``foo`` on the web, you'll see this programmer in the list.

Status Code 201
---------------

But it's not time to celebrate yet. Our response is a little sad. First,
since we've just created a resource, the HTTP elders say that we should return
a 201 status code. In Silex, we just need to return a new ``Response`` object
and set the status code as the second argument::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...
        $this->save($programmer);

        return new Response('It worked. Believe me - I\'m an API', 201);
    }

Location Header
---------------

And when we use the 201 status code, there's another rule: include a ``Location``
header that points to the new resource. We don't have a page that displays
a programmer in our API yet, so let's just hardcode the ``Location`` header
to a made-up URL::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...
        $this->save($programmer);

        $response = new Response('It worked. Believe me - I\'m an API', 201);
        $response->headers->set('Location', '/some/programmer/url');

        return $response;
    }

If you think about it, this is just how the web works. When we submit the
form to create a new programmer, the server returns a redirect that takes
us to view that one programmer. In an API, the status code is 201 instead
of 301 or 302, but the server is trying to help us in both cases.

Try the final product out in our test script:

.. code-block:: bash

    $ php testing.php

.. code-block:: text

    HTTP/1.1 201 Created
    ... 
    Location: /some/programmer/url
    Content-Type: text/html; charset=UTF-8

    It worked. Believe me - I'm an API

Other than the random text we're still returning, this endpoint is looking
great. Now to GET a programmer.

.. _`downloading`: http://knpuniversity.com/screencast/download/rest
.. _`cloning`: github.com/knpuniversity/rest
.. _`README`: https://github.com/knpuniversity/rest/blob/master/README.md
.. _`php.net: php://`: http://www.php.net/manual/en/wrappers.php.php#wrappers.php.input
