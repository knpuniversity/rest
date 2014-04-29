Behat for Testing
=================

The great thing about using PHPUnit is that it's dead-simple: make an HTTP
request and assert some things about its response. If you want to test your
APIs using Guzzle and PHPUnit, you'll be very successful and your office
will smell of rich mahogany.

But in our app, we're going to make our tests much more interesting by using
a tool called `Behat`_. If you're new to Behat, you're in for a treat! But
also don't worry: we're going to use Behat, but not dive into it too deeply.
And when you want to know more, watch our `Behat Screencast`_ and then use
the code that comes with this project to jumpstart testing your API.

Creating Scenarios
------------------

With Behat, we write human-readable statements, called scenarios, and run
these as tests. To see what I mean, find the ``features/api/programmer.feature``
file:

.. code-block:: gherkin

    # api/features/programmer.feature
    Feature: Programmer
      In order to battle projects
      As an API client
      I need to be able to create programmers and power them up

      Background:
        # Given the user "weaverryan" exists

      Scenario: Create a programmer

As you'll see, each feature file will contain many scenarios. I'll fill you
in with more details as we go. For now, let's add our first scenario: `Create a Programmer`:

.. code-block:: gherkin

    # api/features/programmer.feature
    # ...

    Scenario: Create a programmer
      Given I have the payload:
        """
        {
          "nickname": "ObjectOrienter",
          "avatarNumber" : "2",
          "tagLine": "I'm from a test!"
        }
        """
      When I request "POST /api/programmers"
      Then the response status code should be 201
      And the "Location" header should be "/api/programmers/ObjectOrienter"
      And the "nickname" property should equal "ObjectOrienter"

I'm basically writing a user story, where our user is an API client. This
describes a client that makes a POST request with a JSON body. It then checks
to make sure the status code is 201, that we have a ``Location`` header and
that the response has a ``nickname`` property.

Running Behat
-------------

I may sound crazy, but let's execute these english sentences as a real test.
To do that, just run the ``behat`` executable, which is in the ``vendor/bin``
directory:

.. code-block:: bash

    $ php bin/vendor/behat

Green colors! It says that 1 scenario passed. In the background, a real HTTP
request was made to the server and a real response was sent back and then
checked. In our browser, we can actually see the new ``ObjectOrienter`` programmer.

Configuring Behat
~~~~~~~~~~~~~~~~~

Oh, and it knows what our hostname is because of a config file: ``behat.yml.dist``.
We just say ``POST /api/programmers`` and it knows to make the HTTP request
to ``http://localhost:8000/api/programmers``.

.. note::

    If you're running your site somewhere other than ``localhost:8000``,
    copy ``behat.yml.dist`` to ``behat.yml`` and modify the ``base_url``
    in both places.

How Behat Works
---------------

Behat looks like magic, but it's actually really simple. Open up the ``ApiFeatureContext``
file that lives in the ``features/api`` directory. If we scroll down, you'll
immediately see functions with regular expressions above them::

    // features/api/ApiFeatureContext.php
    // ...

    /**
     * @When /^I request "(GET|PUT|POST|DELETE|PATCH) ([^"]*)"$/
     */
    public function iRequest($httpMethod, $resource)
    {
        // ...
    }

Behat reads each line under a scenario and then looks for a function here
whose regular expression matches it. So when we say ``I request "POST /api/programmers"``,
it calls the ``iRequest`` function and passes ``POST`` and ``/api/programmers``
as arguments. In there, our old friend Guzzle is used to make HTTP requests,
just like we're doing in our ``testing.php`` script.

.. note::

    Hat-tip to `Phil Sturgeon`_ and `Ben Corlett`_ who originally created
    this file for Phil's `Build APIs you Won't Hate`_ book.

To sum it up: we write human readable sentences, Behat executes a function
for each line and those functions use Guzzle to make real HTTP requests. Behat
is totally kicking butt for us!

Seeing our Library of Behat Sentences
-------------------------------------

I created this file and filled in all of the logic in these functions. This
gives us a big library of language we can use immediately. To see it, run
the same command with a ``-dl`` option:

.. code-block:: bash

    $ php vendor/bin/behat -dl

Anywhere you see the quote-parentheses mess that's a wildcard
that matches anything. So as long as we write scenarios using this language,
we can test without writing any PHP code in ``ApiFeatureContext``. That's powerful.

If you type a line that doesn't match, Behat will print out a new function
with a new regular expression. It's Behat's way of saying "hey, I don't have
that language. So if you want it, paste this function into ApiFeatureContext
and fill in the guts yourself". I've already prepped everything we need. So
if you see this, you messed up - check your spelling!

And if using Behat is too much for you right now, just keep using the PHPUnit
tests with Guzzle, or even use a mixture!

.. _`Behat`: http://behat.org/
.. _`Behat Screencast`: http://knpuniversity.com/screencast/behat
.. _`Phil Sturgeon`: https://twitter.com/philsturgeon
.. _`Ben Corlett`: https://twitter.com/ben_corlett
.. _`Build APIs you Won't Hate`: https://leanpub.com/build-apis-you-wont-hate
