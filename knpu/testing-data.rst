Handling Data in Tests
======================

Let's run our test a second time:

.. code-block:: bash

    $ php vendir/bin/behat

It fails! The nickname of a programmer is unique in the database, and if
you look closely, this fails because the API tries to insert another ``ObjectOrienter``
and blows up. To fix this, add a new function in ``ApiFeatureContext`` with
a special ``@BeforeScenario`` anotation above it::

    // features/api/ApiFeatureContext.php
    // ...

    /**
     * @BeforeScenario
     */
    public function clearData()
    {
        $this->getProjectHelper()->reloadDatabase();
    }

The body of this function is specific to my app - it calls out to some code
that truncates all of my tables. If you can write code to empty your database
tables, or at least the ones we'll be messing with in our tests, then you can
do this.

.. tip::

    In order to access your framework's normal database-related functions,
    you'll need to bootstrap your app inside this class. For many frameworks,
    libraries exist to glue Behat and it together. If you have issues or
    questions, feel free to post them in the comments.

The ``@BeforeScenario`` annotation, or comment, tells Behat to automatically
run this before every scenario. This guarantees that we're starting with
a very predictable, empty database before each test.

Using Background to Add a User
------------------------------

Try the test again:

.. code-block:: bash

    $ php vendir/bin/behat

Dang, it failed again. Ah, remember how we're relating all programmers to
the ``weaverryan`` user? Well, when we empty the tables before the scenario,
this user gets deleted too. That's expected, and I already have a sentence
to take care of this. Uncomment the ``Background`` line above the scenario.
This runs a function that inserts my user:

.. code-block:: gherkin

    # features/api/programmer.feature
    Feature: Programmer
      # ...

      Background:
        Given the user "weaverryan" exists

      # ...

Eventually we'll have many scenarios in this one file. Lines below ``Background``
are executed before each ``Scenario``. Ok, try it one more time!

.. code-block:: bash

    $ php vendir/bin/behat

Success! When you test, it's critical to make sure that your database is
in a predictable state before each test. Don't assume that a user exists
in your database: create it with a scenario or background step.

And, every test, or scenario in Behat, should work independently. So don't
make one scenario depend on the data of a scenario that comes before it.
That's a huge and common mistake. Eventually, it'll make your tests unpredictable
and hard to debug. If you do a little bit of work early on to get all this
data stuff right, you and Behat are going to be very happy together.

Test: GET One Programmer
------------------------

Let's add a second scenario for making a GET request to view a single programmer.
This entirely uses language that I've already prepped for us:

.. code-block:: gherkin
  
    # features/api/programmer.feature
    # ...
    Scenario: GET one programmer
      Given the following programmers exist:
        | nickname   | avatarNumber |
        | UnitTester | 3            |
      When I request "GET /api/programmers/UnitTester"
      Then the response status code should be 200
      And the following properties should exist:
        """
        nickname
        avatarNumber
        powerLevel
        tagLine
        """
      And the "nickname" property should equal "UnitTester"

The ``Given`` statement actually inserts the user into the database before
we start the test. That's exactly what I was just talking about: if I need
a user, write a scenario step that adds one.

The rest of the test just checks the status code and whatever data we think
is important, just like in the previous scenario.

Run it!

    $ php vendir/bin/behat

Success!

Test: GET all Programmers
-------------------------

We're on a roll at this point, so let's add a third scenario for making 
a GET request to see the collection of all programmers. Oh, and the title 
that we give to each scenario - like ``GET one programmer``: is just for 
our benefit, it's not read by Behat. And for that matter, neither are the 
first 4 lines of the feature file. But you should still learn more about 
the importance of these - don't skip them!

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: GET a collection of programmers
      Given the following programmers exist:
        | nickname    | avatarNumber |
        | UnitTester  | 3            |
        | CowboyCoder | 5            |
      When I request "GET /api/programmers"
      Then the response status code should be 200
      And the "programmers" property should be an array
      And the "programmers" property should contain 2 items

Here, we insert 2 programmers into the database before the test, make the
HTTP request and then check some basic things on the response. It's the same,
boring process over and over again. 

I hope you're seeing how awesome testing our API with Behat is going to be!
