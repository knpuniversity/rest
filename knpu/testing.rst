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

.. code-block:: gherkin

    TODO: Behat: Adding asserts to PHPUnit test

As you'll see, each feature file will contain many scenarios, and I'll fill
you in with more details as we go. For now, let's add our first scenario:
`Create a Programmer`:

.. code-block:: gherkin

    TODO: Behat: Basic POST scenario

I'm basically writing a user story, where our user is an API client. This
describes a client that makes a POST request with a JSON body. It then checks
to make sure our status code is 201, that we have a ``Location`` header and
looks for the response to at least have a ``nickname`` property.

I may sound crazy, but let's execute these english sentences as a real test.
To do that, just run the ``behat`` executable, which is in the ``vendor/bin``
directory:

.. code-block:: bash

    $ php bin/vendor/behat

The result is a lot of green and a message that says our 1 scenario passed.
In the background, a real HTTP request was made to our server and a real
response was sent back and then checked. In our browser, we can actually see
the new ``ObjectOrienter`` programmer.

Oh, and it knows what our hostname is because of a configuration file: ``behat.yml.dist``.
We just say ``POST /api/programmers`` and it knows to make the HTTP request
to this host.

.. note::

    If you're running your site somewhere other than ``localhost:8000``,
    copy ``behat.yml.dist`` to ``behat.yml`` and modify the ``base_url``
    in both places.

How Behat Works
~~~~~~~~~~~~~~~

This looks like magic, but it's actually really simple. Open up the
``ApiFeatureContext`` file that lives in the ``features/api`` directory.
If we scroll down, you'll immediately see functions with regular expressions
above them::

    Behat: Basic POST scenario: FeatureContext::iRequest

Behat reads each line under a scenario and then looks for a function here
whose regular expression matches it. So when we say ``I request "POST /api/programmers"``,
it calls the ``iRequest`` function and passes ``POST`` and ``/api/programmers``
as arguments. In there, our old friend Guzzle is used to make HTTP requests,
just like we're doing in our ``testing.php`` script.

.. note::

    Hat-tip to `Phil Sturgeon`_ and `Ben Corlett`_ who originally created
    this file for Phil's `Build APIs you Won't Hate`_ book.

To sum it up: we write human readable sentences, Behat executes a function
for each line, those functions use Guzzle to make real HTTP requests.

Seeing our Library of Behat Sentences
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

I've already prepared a big library of language we can use. To see it, run
the same command with a ``-dl`` option:

.. code-block:: bash

    $ php vendor/bin/behat -dl

Obviously, anywhere you see the quote, parentheses mess, that's a wildcard
that matches anything. So as long as we write scenarios using this language,
we can test without doing any more work. That's powerful.

If you type a line that doesn't match, Behat will print out a new function
with a new regular expression. It's Behat's way of saying "hey, I don't have
that language. So if you want it, paste this function into ApiFeatureContext
and fill in the guts yourself". I've already prepped everything we need. So
if you see this, you messed up - check your spelling!

And ultimately, if using Behat is too much for you right now, just keep using
the PHPUnit tests with Guzzle, or even use a mixture!

Clearing Data Between Tests
---------------------------

Let's run our test a second time:

    $ php vendir/bin/behat

It fails! The nickname of a programmer is unique in the database, and if
you look closely, the test fails because the API tries to insert another
``ObjectOrienter`` and blows up. To fix this, add a new function in ``ApiFeatureContext``
with a special ``@BeforeScenario`` anotation above it::

    Behat: Clear data between tests

The body of this function is specific to my app - it calls out to some code
that truncates all of my tables. If you can write code to empty your database
tables, at least the ones we'll be messing with in our tests, then you can
do this.

.. tip::

    In order to access your framework's normal database-related functions,
    you'll need to bootstrap your app inside this class. For many frameworks,
    libraries exist to glue Behat and it together. If you have issues or
    questions, feel free to post them in the comments.

The ``@BeforeScenario`` annotation tells Behat to automatically run this
before every scenario. This guarantees that we're starting with a very predictable,
empty database before each test.

Try the test again:

    $ php vendir/bin/behat

Hmm, it failed again. Ah, remember how we're relating all programmers to
the ``weaverryan`` user? Well, when we empty the tables before the scenario,
this user gets deleted too. That's expected, and I already have a sentence
to take care of this. Uncomment the ``Background`` line above the scenario:

    TODO: Behat: Clear data between tests Background

Eventually we'll have many scenarios in this one file. Lines below ``Background``
are executed before each ``Scenario``. Ok, try it one more time!

    $ php vendir/bin/behat

Success! I know that had nothing to do with APIs, but testing an API is really
important. And this whole issue of clearing out the data was going to be
a big problem for you eventually.

Test: GET One Programmer
------------------------

Let's add a second scenario for making a GET request to view a single programmer.
This entirely uses language that I've already prepped for us:

.. code-block:: gherkin

    Behat: List and show tests: Scenario GET one

The ``Given`` statement actually inserts that user into the database before
we start the test. Then everything works like normal: make an HTTP request
and check some things.

Run it!

    $ php vendir/bin/behat

Success!

Test: GET all Programmers
-------------------------

That was easy, so let's add a third scenario for making a GET request to
see the collection of all programmers. Oh, and the title that we give to
each scenario - like ``GET one programmer``: is just for our benefit, it's
not read by Behat:

.. code-block:: gherkin

    Behat: List and show tests: Scenario GET collection

Here, we insert 2 programmers into the database before the test, make the
HTTP request, then check some basic things on the response. I hope you're
seeing how awesome testing our API with Behat is going to be!

.. _`Postman`: http://www.getpostman.com/
.. _`PHPUnit`: http://phpunit.de/
.. _`Behat`: http://behat.org/
.. _`Behat Screencast`: http://knpuniversity.com/screencast/behat
.. _`Phil Sturgeon`: https://twitter.com/philsturgeon
.. _`Ben Corlett`: https://twitter.com/ben_corlett
.. _`Build APIs you Won't Hate`: https://leanpub.com/build-apis-you-wont-hate