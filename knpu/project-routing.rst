Project Routing
===============

Ok, let's get start by `downloading`_ or `cloning`_ the CodeBattles project.
Now, follow the `README.md`_ file to get things working. It involves `downloading Composer`_
and installing the vendor libraries:

.. code-block:: bash

    $ git clone https://github.com/knpuniversity/rest.git
    $ cd rest
    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar install

.. tip::

    If you're new to Composer, watch `The Wonderful World of Composer`_.

When that's done, start up the app by using PHP's awesome built-in
web server:

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

CodeBattles is built in `Silex`_, a PHP microframework. If this is your first
time using Silex, take a few minutes with `its Documentation`_ to get to
know it. It basically let's us design routes, or pages and easily write the code
to render those pages. Our setup will look just a little bit different than
this, but the idea is the same.

But this is *not* a tutorial on building a REST API on only Silex! Most of
what we'll do is basically the same across any framework. You *will* need
to do a little bit of work here and there. But trust me, these things
are a pleasure to do compared with all the tough REST stuff.

First Endpoint: POST /api/programmers
-------------------------------------

Let's pretend we're building the API for an iPhone app. Ignoring authentication,
what's the first thing the user will do in the app? Create a programmer of course!
And that's our first API endpoint.

Separate URLs from our Web Interface?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

But hold up! In the web version of our app, we're already able to create a
programmer by filling out a form and submitting it via POST to ``/programmers/new``.
This either re-renders the HTML page with errors or redirects us.

Why not just reuse the code from this URL and make it work for our API?
To do this we'd need to make it accept JSON request data, become smart
enough to return errors as JSON and do something other than a redirect on
success. Then, ``/programmers`` could be used by a browser to get HTML *or*
by an API client to pass JSON back and forth.

That would be sweet! And later, we'll talk about how you could do that.
But for now, things will be a lot easier to understand if we leave the web
interface alone, prefix our API URLs with ``/api``, and write separate code
for it.

This *does* break a rule of REST, because each resource will now have 2
URLs: one for HTML and one for the JSON representation. But REST has a lot
of rules, too many for our rebel Codebattles site. We'll break this one, like
many APIs. But later, I'll show you how we *could* use 1 URL to return multiple
representation.

Basic Routing
~~~~~~~~~~~~~

So let's build the endpoint. Find the ``src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php``
file and uncomment the route definition::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));
    }

Next, create a ``newAction`` inside of this class and return ``let's battle!``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction()
    {
        return 'let's battle!';
    }

And just like that, we're making threats and we have a new endpoint with 
the URL ``/api/programmers``. If we make a POST request here, the ``newAction`` 
function will be executed and these famous last words will be returned in the response. 
This is the core of what Silex gives us.

URLs and Resources
~~~~~~~~~~~~~~~~~~

My choice of a POST request to create a programmer isn't accidental.
``/api/programmers`` is a collection resource. And according to some HTTP
rules I'll show you later, when you want to create a resource, you should
send a POST request to its collection.

In other words, I'm not making this all up: I'm following the rules of the
web. And in the API world, if you follow the rules, you'll have more friends.

Testing the Endpoint
~~~~~~~~~~~~~~~~~~~~

Well let's try it already! That's actually not easy in a browser, since we
need to make a POST request. Instead, open up the ``testing.php`` file at
the root of the project that I've already prep'ed for us::

    // testing.php
    require __DIR__.'/vendor/autoload.php';

    use Guzzle\Http\Client;

    // create our http client (Guzzle)
    $client = new Client('http://localhost:8000', array(
        'request.options' => array(
            'exceptions' => false,
        )
    ));

This is a plain PHP file that creates a `Guzzle`_ Client object. Guzzle is
a simple library for making HTTP requests and receving responses.

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

.. code-block:: text

    HTTP/1.1 200 OK
    Host: localhost:8000
    Connection: close
    Cache-Control: no-cache
    Content-Type: text/html; charset=UTF-8

    let's battle!

Success!

.. _`downloading`: http://knpuniversity.com/screencast/download/rest
.. _`cloning`: https://github.com/knpuniversity/rest
.. _`README.md`: https://github.com/knpuniversity/rest/blob/master/README.md
.. _`downloading Composer`: https://getcomposer.org/
.. _`The Wonderful World of Composer`: https://knpuniversity.com/screencast/composer
.. _`Silex`: http://silex.sensiolabs.org/
.. _`its documentation`: http://silex.sensiolabs.org/documentation
.. _`Guzzle`: http://guzzle3.readthedocs.org/
