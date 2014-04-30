Debugging Tests
===============

But what if this *had* failed? Could we debug it?

Let's pretend we coded something wrong by throwing a big ugly exception in
our controller::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function updateAction($nickname, Request $request)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if (!$programmer) {
            $this->throw404();
        }

        throw new \Exception('This is scary!');
        
        // ...
    }

Now run the test again:

.. code-block:: bash

    $ php vendir/bin/behat

Not surprisingly, we're getting a 500 error instead of 200. But we can't
really see what's going on because we can't see the big error page!

But don't worry! First, I've done my best to configure Behat so when something
fails, part of the last response is printed below.

.. tip::

    This functionality works by returning the h1 and h2 elements of the HTML
    page. If your app shows erorrs with different markup, tweak the
    ``ApiFeatureContext::printLastResponseOnError`` method to your liking.

If this doesn't tell you enough, we can print out the last response in its
entirety. To do this, add "And print last response" to our scenario, just
*before* the failing line:

.. code-block:: gherkin

    // features/api/programmer.feature
    // ...

    Scenario: PUT to update a programmer
      Given the following programmers exist:
        | nickname    | avatarNumber | tagLine |
        | CowboyCoder | 5            | foo     |
      And I have the payload:
        """
        {
          "nickname": "CowboyCoder",
          "avatarNumber" : 2,
          "tagLine": "foo"
        }
        """
      When I request "PUT /api/programmers/CowboyCoder"
      And print last response
      Then the response status code should be 200
      And the "avatarNumber" property should equal "2"

Now just re-run the test:

.. code-block:: bash

    $ php vendir/bin/behat

It may be ugly, but the entire response of the last request our test made
is printed out, including all the header information on top. Once you've
figured out and fixed the problem, just take the ``print last response``
line out and keep going!
