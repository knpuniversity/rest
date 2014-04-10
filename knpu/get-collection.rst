GET /programmers: A collection of Programmers
---------------------------------------------

We now have 2 URLs and 2 resources:

* ``/api/programmers``, which represents a collection of resources (i.e. all programmers);
* ``/api/programmers/{nickname}``, which represents one programmer.

We can't yet make a GET request to ``/programmers``, and there's nothing
that says we *must* make this possible, it's up to us to decide if we need
it. But most of the time, you *will* make this possible, and your API client
will probably assume it exists anyways.

Like always, let's start by updating our testing script to try the new endpoint::

    // testing.php
    // ...

    // 3) GET a list of all programmers
    $request = $client->get('/api/programmers');
    $response = $request->send();

    echo $response;
    echo "\n\n";

Next, create a new route that points to a new ``listAction`` method in our
``ProgrammerController`` class::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        // the 2 other routes ...

        $controllers->get('/api/programmers', array($this, 'listAction'));
    }

I'll copy the ``showAction`` and modify it for ``listAction``. We'll query
for *all* programmers for now, then transform them all into a big array::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()->findAll();
        $data = array('programmers' => array());
        foreach ($programmers as $programmer) {
            $data['programmers'][] = $this->serializeProgrammer($programmer);
        }

        $response = new Response(json_encode($data), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

The ``serializeProgrammer`` method doesn't exist yet, but we can create it
by using the code from ``showAction`` to avoid duplication. We're going to
use some fancier methods of turning objects into JSON a bit later::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function showAction($nickname)
    {
        // ...

        // replace the manual creation of the array with this function call
        $data = $this->serializeProgrammer($programmer);

        // ...
    }

    private function serializeProgrammer(Programmer $programmer)
    {
        return array(
            'nickname' => $programmer->nickname,
            'avatarNumber' => $programmer->avatarNumber,
            'powerLevel' => $programmer->powerLevel,
            'tagLine' => $programmer->tagLine,
        );
    }

Let's try it out!

.. code-block:: bash

    $ php testing.php

.. code-block:: text

    HTTP/1.1 200 OK
    ... 
    Content-Type: application/json

    {
        "programmers": [
            {
                "nickname":"ObjectOrienter14",
                "avatarNumber":"5",
                "powerLevel":"0",
                "tagLine":null
            },
            {
                "nickname":"ObjectOrienter795",
                "avatarNumber":"5",
                "powerLevel":"0",
                "tagLine":"a test dev!"
            }
        ]
    }

Awesome! Why did I put things under a ``programmers`` key? Actually, no special
reason, I just invented this. But there *are* pre-existing standards for
organizing your JSON structures, an important idea we'll talk about later.
For now, we'll just worry about being consistent throughout the API.

