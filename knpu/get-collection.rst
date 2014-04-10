GET /programmers: A collection of Programmers
=============================================

We now have 2 URLs and so 2 resources:

* ``/api/programmers``, which represents a collection of resources (i.e. all programmers);
* ``/api/programmers/{nickname}``, which represents one programmer.

Actually, we can POST to the ``/api/programmers`` resource, but we can't
GET it yet. And nothing says that we *have* to support the GET method for
a resource. But we'll add it for two reasons. First, I'll pretend that our
imaginary iPhone app needs it. And second, API users tend to assume that
you can GET most any resource. If we make this possible, our API is that
much more predictable and friendly.

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
for *all* programmers using another method from my ORM. Once again, the important
thing is that this gives me an array of ``Programmer`` objects. Next, I'll
transform these into a big array::

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

Cool - let's try it!

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

Awesome! So why did I put the data under a ``programmers`` key? Actually,
no special reason, I just invented this standard. I could have structured
my JSON however I wanted.

And actually, there are some pre-existing standards that exist on the web
for organizing your JSON structures. These answer questions like, "should
I put the data under a ``programmers`` key?" or "how should I organize details
on how to paginate through the results?".

This is real important stuff, but more on it later. For now, we just have
to follow one golden rule: find a standard and be consistent with it.
