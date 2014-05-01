PUT: Killing Duplicated Code
============================

Our tests are passing, but we're doing a bad job: I've created duplicated
code in ``newAction`` and ``updateAction``!

Let's redeem ourselves! Create a new private function called ``handleRequest``
and copy the code into it that reads the request body and sets the data on
the Programmer::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleRequest(Request $request, Programmer $programmer)
    {
        $data = json_decode($request->getContent(), true);

        $programmer->nickname = $data['nickname'];
        $programmer->avatarNumber = $data['avatarNumber'];
        $programmer->tagLine = $data['tagLine'];
        $programmer->userId = $this->findUserByUsername('weaverryan')->id;
    }

Cool! Now we can just call this from ``newAction`` and ``updateAction``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        $programmer = new Programmer();
        $this->handleRequest($request, $programmer);
        $this->save($programmer);
        
        // ...
    }

    public function updateAction($nickname, Request $request)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if (!$programmer) {
            $this->throw404();
        }

        $this->handleRequest($request, $programmer);
        $this->save($programmer);

        // ...
    }

Re-run the tests to see if we broke anything:

.. code-block:: bash

    $ php vendir/bin/behat

Cool! I'm going to change how this code is written *just* a little bit so
that it's even more dynamic::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleRequest(Request $request, Programmer $programmer)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new \Exception(sprintf('Invalid JSON: '.$request->getContent()));
        }

        // determine which properties should be changeable on this request
        $apiProperties = array('nickname', 'avatarNumber', 'tagLine');

        // update the properties
        foreach ($apiProperties as $property) {
            $val = isset($data[$property]) ? $data[$property] : null;
            $programmer->$property = $val;
        }

        $programmer->userId = $this->findUserByUsername('weaverryan')->id;
    }

There's nothing important in this change, but it'll make some future changes
easier to understand. If you're using a form library or have a fancier ORM,
you can probably do something like this with even less code than I have.

While we're here, let's throw a big exception if the client sends us invalid
JSON.
