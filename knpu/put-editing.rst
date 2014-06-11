PUT: Editing Resources
======================

We can create a programmer resource, view a representation of a programmer,
or view the collection representation for all programmers.

PUT: The Basic Definition
-------------------------

We're killing it! Now let's make it possible to edit a programmer resource.

Depending on who you ask, there are about 10 HTTP methods, and the 4 main
ones are

* GET
* POST
* PUT
* DELETE

We know GET is for retrieving a representation and DELETE is pretty clear.

But things get trickier with POST and PUT. I'm about to say something that's
**incorrect**. Ready?

POST is used for creating resources and PUT is used for updating.

Seriously, this is **not true**, and it's dangerous to say: there might be
hardcore REST fans waiting around any corner that's eager to tell us how
wrong that is.

But in practice, this statement is pretty close: PUT is for edit, POST is
for create. So let's use the PUT method for our edit endpoint. Afterwards,
we'll geek out on the *real* difference between POST and PUT.

Writing the Test
----------------

You guys know the drill: we start by writing the test. So let's add yet
*another* scenario:

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
      Then the response status code should be 200
      And the "avatarNumber" property should equal "2"

This looks a lot like our POST scenario, and that's good: consistency! But
we *do* need to add a line to put a programmer into the database and tweak
a few other details. The status code *is* different: 201 is used when an asset
is created but the normal 200 is used when it's an update.

Just to keep us tied into the theory of things, I'll describe this using
REST-nerd language. Ready? Ok.

This tests that when we send a "representation" of a programmer resource
via PUT, the server will use it to update that resource and return a representation.

.. index::
   single: HTTP Methods; 405

We haven't actually coded this yet, so when we run the test, it fails:

.. code-block:: bash

    $ php vendir/bin/behat

The test reports that the status code isn't 200, it's 405. 405 means "method
not allowed", and our framework is doing this for us. It's a way of saying
"Hey, ``/api/programmers/CowboyCoder`` *is* a valid URI, but the resource
doesn't support the PUT method."

If your API doesn't support an HTTP method for a resource, you should return
a 405 response. If you use a decent framework, you should get this functionality
for free.

Coding up the PUT Endpoint
--------------------------

Let's code it! Create another route, but use the ``put`` method to make it
respond only to PUT requests::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        // ...

        $controllers->put('/api/programmers/{nickname}', array($this, 'updateAction'));
    }

Next, copy the ``newAction`` and rename it to ``updateAction``, because these
will do almost the same thing. The biggest difference is that instead of
creating a new ``Programmer`` object, we'll query the database for an existing
object and update it. Heck, we can steal that code from ``showAction``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function updateAction($nickname, Request $request)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if (!$programmer) {
            $this->throw404();
        }

        $data = json_decode($request->getContent(), true);

        $programmer->nickname = $data['nickname'];
        $programmer->avatarNumber = $data['avatarNumber'];
        $programmer->tagLine = $data['tagLine'];
        $programmer->userId = $this->findUserByUsername('weaverryan')->id;

        $this->save($programmer);

        // ...
    }

Change the status code from 201 to 200, since we're no longer creating a
resource. And you should also remove the ``Location`` header::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function updateAction($nickname, Request $request)
    {
        // ...

        $this->save($programmer);

        $data = $this->serializeProgrammer($programmer);

        $response = new JsonResponse($data, 200);

        return $response;
    }

We only need this header with the 201 status code when a resource is created.
And it makes sense: when we create a new resource, we don't know what its
new URI is. But when we're editing an existing resource, we clearly already
have that URI, because we're using it to make the edit.

Run the Test and Celebrate
--------------------------

Time to run the test!

.. code-block:: bash

    $ php vendir/bin/behat

Woot! It passes! And we can even run it over and over again.

.. _`rfc2616`: http://www.w3.org/Protocols/rfc2616/rfc2616.html
