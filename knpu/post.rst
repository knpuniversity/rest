POST: Creation, Location Header and 201
=======================================

Once the POST endpoint works, the client will send programmer details to
the server. In REST-speak, it will send a representation of a programmer,
which can be done in a bunch of different ways. It's invisible to us, but
HTML forms do this by sending data in a format called ``application/x-www-form-urlencoded``:

.. code-block:: text

    POST /api/programmers HTTP/1.1
    Host: localhost:8000
    Content-Type: application/x-www-form-urlencoded
    
    nickname=Geek+Dev1&avatarNumber=5

PHP reads this and puts it into the ``$_POST`` array. That's ok for the web,
but in the API world, it's ugly. Why not, have the client send us the
representation in a beautiful boquet of curly braces known as JSON:

.. code-block:: text

    POST /api/programmers HTTP/1.1
    Host: localhost:8000
    Content-Type: application/json
    
    {
        "nickname": "Geek Dev1",
        "avatarNumber": 5
    }

Creating a request like this with Guzzle is easy::

    // testing.php
    // ...

    $nickname = 'ObjectOrienter'.rand(0, 999);
    $data = array(
        'nickname' => $nickname,
        'avatarNumber' => 5,
        'tagLine' => 'a test dev!'
    );

    $request = $client->post('/api/programmers', null, json_encode($data));
    $response = $request->send();

    echo $response;
    echo "\n\n";

The second ``null`` argument is the request headers to send.
We're not worried about headers yet, so we can just leave it blank.

Coding up the Endpoint
----------------------

Back in the ``ProgrammerController`` class, let's make this work by doing
our favorite thing - coding! First, how do we get the JSON string passed 
in the request? In Silex, you do this by getting the ``Request`` object 
and calling ``getContent()`` on it. Let's just return the data from the 
endpoint so we can see it::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        $data = $request->getContent();
        return $data;
    }

.. tip::

    Your framework will likely have a shortcut for getting the request content
    or body. But if it doesn't, you can get it by using this strange bit
    of code::
    
        $data = file_get_contents('php://input');

    This is a special stream that reads the request body. For more details,
    see `php.net: php://`_.

Try running our ``testing.php`` file again:

.. code-block:: bash

    $ php testing.php

This time, we see the JSON string being echo'ed right back at us:

.. code-block:: text

    HTTP/1.1 200 OK
    ...
    Content-Type: text/html; charset=UTF-8

    {"nickname":"ObjectOrienter31","avatarNumber":5}

Creating the Programmer Resource Object
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Awesome! I've already created a ``Programmer`` class, which
has just a few properties on it. I also created simple classes for the other two
resources - ``Project`` and ``Battle``. We'll use these later.

In ``newAction``, we have the JSON string, so let's decode it and use the data
to create a new ``Programmer`` object that's ready for battle. We'll use
each key that the client sends to populate a property on the object::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $programmer = new Programmer($data['nickname'], $data['avatarNumber']);
        $programmer->tagLine = $data['tagLine'];
        // ...
    }

My app also has a really simple ORM that lets us save these objects to the
database. How you save things to your database will be different. The key
point is that we have a ``Programmer`` class that models how we want our
API to look, and that we can somehow save this::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $programmer = new Programmer($data['nickname'], $data['avatarNumber']);
        $programmer->tagLine = $data['tagLine'];
        $programmer->userId = $this->findUserByUsername('weaverryan')->id;

        $this->save($programmer);

        return 'It worked. Believe me - I\'m an API';
    }

At the bottom, I'm just returning a really reassuring message that everything
went ok.

Faking the Authenticated User
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

I've also added one really ugly detail::

    $programmer->userId = $this->findUserByUsername('weaverryan')->id;

Every programmer is created and owned by one user. On the web, finding out
*who* is creating the programmer is as easy as finding out which user is
currently logged in.

But our API has no idea who *we* are - we're just a client making requests
without any identification.

We'll fix this later. Right now, I'll just make *every* programmer owned by
me. Make sure to use my username: it's setup as test data that's always
in our database. This test data is also known as fixtures.

Ok, the moment of truth! Run the testing script again:

.. code-block:: bash

    $ php testing.php

.. code-block:: text

    HTTP/1.1 200 OK
    Host: localhost:8000
    ... 
    Content-Type: text/html; charset=UTF-8

    It worked. Believe me - I'm an API

The message tells us that it probably worked. And if you login as ``weaverryan``
with password ``foo`` on the web, you'll see this fierce programmer-warrior 
in the list.

Status Code 201
---------------

But no time to celebrate! Our response is a little sad. First, since we've
just created a resource, the HTTP elders say that we should return a 201
status code. In Silex, we just need to return a new ``Response`` object
and set the status code as the second argument::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...
        $this->save($programmer);

        return new Response('It worked. Believe me - I\'m an API', 201);
    }

Running the testing script this time shows us a 201 status code.

Location Header
---------------

And when we use the 201 status code, there's another rule: include a ``Location``
header that points to the new resource. Hmm, we don't have an endpoint to get
a single programmer representation yet. To avoid angering the RESTful elders,
let's add a location header, and just fake the URL for now::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...
        $this->save($programmer);

        $response = new Response('It worked. Believe me - I\'m an API', 201);
        $response->headers->set('Location', '/some/programmer/url');

        return $response;
    }

If you stop and think about it, this is how the web works. When we submit
a form to create a programmer, the server returns a redirect that takes us
to the page to view it. In an API, the status code is 201 instead of
301 or 302, but the server is trying to help show us the way in both cases.

Try the final product out in our test script:

.. code-block:: bash

    $ php testing.php

.. code-block:: text

    HTTP/1.1 201 Created
    ... 
    Location: /some/programmer/url
    Content-Type: text/html; charset=UTF-8

    It worked. Believe me - I'm an API

Other than the random text we're still returning, this endpoint is looking
great. Now to GET a programmer!

.. _`php.net: php://`: http://www.php.net/manual/en/wrappers.php.php#wrappers.php.input
.. _`The Wonderful World of Composer`: http://knpuniversity.com/screencast/composer
