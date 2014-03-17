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
familiar with Silex, that's no problem. First, it's really easy. And second,
most of what we'll do will basically be the same in any other framework.
Sure, you'l need to do a little bit of work to hook in your framework's validation
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

    TODO: POST Programmer: Create endpoint

Next, create a ``newAction`` inside of this class and just return the classic
and boring ``hello world!``::

    TODO: POST Programmer: Create endpoint

This creates a new endpoint with the URL ``/api/programmers``. If we make
a POST request here, the ``newAction`` function will be executed.

URLs and Resources
~~~~~~~~~~~~~~~~~~

Remember that every URL is the address to a resource. Here, ``/api/programmers``
is a resource that represents the collection of programmers in the system.
A resource can be one thing - like a programmer - or many things - like a
collection of programmers. Typically, when you POST to a collection, you're
saying that you want to add a new item to it.

Testing the Endpoint
~~~~~~~~~~~~~~~~~~~~

Let's test it so far! That's actually not so easy in a browser, since only
a POST request will work. Instead, open up the ``testing.php`` file::

    TODO: POST Programmer: Create endpoint

All this does so far is instantiate a `Guzzle`_ Client object. Guzzle is
a crazy-good library that lets you make HTTP curl requests and receive responses.
If you're talking to an API in PHP, this is what you use.

Let's make a POST request to ``/api/programmers`` and print out the response::

    TODO: POST Programmer: Start basic test

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

    TODO POST Programmer: Update test with POST data

Coding up the Endpoint
----------------------

Back in the ``ProgrammerController`` class, let's start coding to make this
work. First, how do we get the JSON string passed in the request? In Silex,
you do this by getting the ``Request`` object and calling ``getContent()``
on it. Let's just return the data from the endpoint so we can see it::

    TODO POST Programmer: Dump request content

.. tip::

    Your framework will likely have a shortcut for getting the request content
    or body. But if it doesn't, you can get it by using this strange bit
    of code::
    
        $data = file_get_contents('php://input');

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

    TODO POST Programmer: Creating and saving entity

Our app already comes ready with classes for ``Programmer``, ``Battle`` and
``Project``, as well as a really simple ORM. At the bottom, I'm just returning
a really reassuring message that everything went ok.

I've also added one really ugly detail::

    TODO POST Programmer: Creating and saving entity - just the one line

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

    TODO: POST Programmer: 201 status code

Location Header
---------------

And when we use the 201 status code, there's another rule: include a ``Location``
header that points to the new resource. We don't have a page that displays
a programmer in our API yet, so let's just hardcode the ``Location`` header
to a made-up URL::

    TODO: POST Programmer: First Location header

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
