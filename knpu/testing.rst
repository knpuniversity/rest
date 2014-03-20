Testing your API
================

What, a testing chapter so early? Yep, with API's, you really can't avoid
it, and that's a good thing. One way or another, to debug and develop your
API, you'll need to either write some code or configure the parameters of
each call  in a REST browser plugin (like `Postman`_). And if you're already
doing that, you might as well make these calls repeatable and automated.

Guzzle and PHPUnit
------------------

Like I mentioned before, if you're making HTTP requests in PHP, you'll want
to use Guzzle, like we are in our ``testing.php`` file. So, the easiest way
to create tests is to join `PHPUnit`_ and Guzzle together.

I already have PHPUnit installed in our application via Composer, so let's
go straight to writing a test. I'll create a new ``ProgrammerControllerTest.php``
file and setup its class to extend the normal ``PHPUnit_Framework_TestCase``::

    TODO: Behat: Basic PHPUnit test

Add a ``testPOST`` method and copy in the POST logic from our ``testing.php``
script::

    TODO: Behat: Fill in PHPUnit test

Finally, let's add some asserts to check that the status code is 201, that
we have a ``Location`` header and that we get back valid JSON::

    TODO: Behat: Adding asserts to PHPUnit test

To try it out, point the phpunit executable that lives in the ``vendor/``
directory at your test:

.. code-block:: bash

    $ php vendor/bin/phpunit src/KnpU/CodeBattle/Tests/ProgrammerControllerTest.php

With any luck, Sebastian will tell you that everything is ok! Of course I
never trust a test that passes on the first try, so be sure to change things
and make sure it fails when it should too.

Behat for Testing
-----------------

The great thing about this setup is that it's dead-simple: make some HTTP
requests, assert some things about the response. If you want to test your
APIs using Guzzle and PHPUnit, you'll be very successful.

But in our app, we're going to use a different tool: `Behat`_. If you haven't
heard of it before, you're in for a treat! But also don't worry: we're going
to use Behat, but not dive into it too deeply. If you want to know more, watch
our `Behat Screencast` and use the code with this project to jumpstart testing
your API.

Behat allows us to write human-readable scenarios and run these as tests.
First, find the ``features/api/programmer.feature`` file:

.. code-block:: gherkin::

    TODO: Behat: Adding asserts to PHPUnit test

As you'll see, each feature file will contain many scenarios, and I'll fill
you in with more details as we go. For now, let's add our first scenario:
`Create a Programmer`:

.. code-block:: gherkin::

    

.. _`Postman`: http://www.getpostman.com/
.. _`PHPUnit`: http://phpunit.de/
.. _`Behat`: http://behat.org/
.. _`Behat Screencast`: http://knpuniversity.com/screencast/behat
