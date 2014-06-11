Deleting Resources
==================

After all the POST, PUT, idemptotency talk, we deserve a break. There's only
one thing that an API client *can't* do to a programmer resource: delete it!
So let's fix that!

Once again, we're going to leverage HTTP methods. We have GET to retrieve
a representation, PUT to update the resource, and DELETE to, ya know, blow
the resource up! HTTP gives us these HTTP verbs so that we don't need to
do silly things like have a ``/api/programmers/delete`` URI. Remember, every
URI is a resource, and that one wouldn't really make sense you would probably
get teased in the cafeteria for it.

Writing the Test
----------------

Oh where to start? Why not write a test? Open up our feature file and add
yet another scenario, this time for deleting a programmer resource. We need
to use a ``Given`` like in the other scenarios to first make sure that we
have a programmer in the database to delete:

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: DELETE a programmer
      Given the following programmers exist:
        | nickname   | avatarNumber |
        | UnitTester | 3            |
      When I request "DELETE /api/programmers/UnitTester"

After deleting a resource, what should the endpoint return and what about
the status code? People argue about this, but one common approach is to return
a 204 status code, which means "No Content". It's the server's way of saying
"I completed your request ok, but I really don't have anything else to tell
you beyond that". In other words, the response will have an empty body:

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: DELETE a programmer
      Given the following programmers exist:
        | nickname   | avatarNumber |
        | UnitTester | 3            |
      When I request "DELETE /api/programmers/UnitTester"
      Then the response status code should be 204

Coding the Endpoint
-------------------

To make this work, we'll need to create a route that responds to the HTTP
``DELETE`` method. Make sure the URL is the same as what we use to GET one
programmer, because we want to take the DELETE action on that resource::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        // ...

        $controllers->delete('/api/programmers/{nickname}', array($this, 'deleteAction'));
    }

Next, create the ``deleteAction`` method. We can copy a little bit of code
that queries for a programmer::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function deleteAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        // ...
    }

If the programmer exists, let's eliminate him! I've created a shortcut method
called ``delete`` in my project. Your code will be different, but fortunately,
deleting things is pretty easy::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function deleteAction($nickname)
    {
        // ...

        if ($programmer) {
            $this->delete($programmer);
        }

        // ...
    }

And finally, we just need to send a Response back to the user. The important
part is the 204 status code and the blank content, which is what 204 means::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function deleteAction($nickname)
    {
        // ...

        if ($programmer) {
            $this->delete($programmer);
        }

        return new Response(null, 204);
    }

Dang, that was really easy! Execute Behat to make sure we didn't mess anything
up. Awesome! Like with everything else, be consistent with how resources
are deleted. Whether you return a 204 status code, or some sort of JSON message,
return the same thing for all resources when they're deleted.
