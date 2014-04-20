Testing your API
================

What, a testing chapter so early? Yep, with API's, you really can't avoid
it, and that's a good thing. One way or another, to develop, test and debug
each endpoint, you'll need to either write some code or configure an HTTP
browser plugin like `Postman`_. And if you're already doing all that work,
you might as well make these calls repeatable and automated.

Guzzle and PHPUnit
------------------

Like I mentioned, if you're making HTTP requests in PHP, you'll want to use
Guzzle, like we are in our ``testing.php`` file. So, the easiest way to create
tests is just to put `PHPUnit`_ and Guzzle together.

I already have PHPUnit installed in our app via Composer, so let's go straight
to writing a test. Create a new ``Tests`` directory and put a ``ProgrammerControllerTest.php``
file there. Create a class inside and extend the normal PHPUnit base class::

    TODO: Behat: Basic PHPUnit test

Next, add a ``testPOST`` method and copy in the POST logic from the ``testing.php``
script::

    TODO: Behat: Fill in PHPUnit test

Finally, let's add some asserts to check that the status code is 201, that
we have a ``Location`` header and that we get back valid JSON::

    TODO: Behat: Adding asserts to PHPUnit test

To try it out, use the ``vendor/bin/phpunit`` executable and point it at
the test file.

.. code-block:: bash

    $ php vendor/bin/phpunit src/KnpU/CodeBattle/Tests/ProgrammerControllerTest.php

With any luck, Sebastian Bergmann will tell you that everything is ok! Of
course I never trust a test that passes on the first try, so be sure to change
things and make sure it fails when it should too.



.. _`Postman`: http://www.getpostman.com/
.. _`PHPUnit`: http://phpunit.de/
