Testing your API with PHPUnit
=============================

What, a testing chapter so early? Yep, with API's, you really can't avoid
it, and that's a good thing. One way or another, to develop, test and debug
each endpoint, you'll need to either write some code or configure an HTTP
browser plugin like `Postman`_. And if you're already doing all that work,
you might as well make these calls repeatable and automated.

Guzzle and PHPUnit
------------------

Like I mentioned, if you're making HTTP requests in PHP, you'll want to use
Guzzle, like we are in our ``testing.php`` file. So, the easiest way to create
tests is just to put `PHPUnit`_ and Guzzle together.

I already have PHPUnit installed in our app via Composer, so let's go straight
to writing a test. Create a new ``Tests`` directory and put a ``ProgrammerControllerTest.php``
file there. Create a class inside and extend the normal PHPUnit base class::

    // src/KnpU/CodeBattle/Tests/ProgrammerControllerTest.php
    namespace KnpU\CodeBattle\Tests;

    class ProgrammerControllerTest extends \PHPUnit_Framework_TestCase
    {

    }

Next, add a ``testPOST`` method and copy in the POST logic from the ``testing.php``
script::

    // src/KnpU/CodeBattle/Tests/ProgrammerControllerTest.php
    // ...

    public function testPOST()
    {
        // create our http client (Guzzle)
        $client = new Client('http://localhost:8000', array(
            'request.options' => array(
                'exceptions' => false,
            )
        ));

        $nickname = 'ObjectOrienter'.rand(0, 999);
        $data = array(
            'nickname' => $nickname,
            'avatarNumber' => 5,
            'tagLine' => 'a test dev!'
        );

        $request = $client->post('/api/programmers', null, json_encode($data));
        $response = $request->send();
    }

Finally, let's add some asserts to check that the status code is 201, that
we have a ``Location`` header and that we get back valid JSON::

    // src/KnpU/CodeBattle/Tests/ProgrammerControllerTest.php
    // ...

    public function testPOST()
    {
        // ...

        $request = $client->post('/api/programmers', null, json_encode($data));
        $response = $request->send();

        $request = $client->post('/api/programmers', null, json_encode($data));
        $response = $request->send();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $data = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('nickname', $data);
    }

To try it out, use the ``vendor/bin/phpunit`` executable and point it at
the test file.

.. code-block:: bash

    $ php vendor/bin/phpunit src/KnpU/CodeBattle/Tests/ProgrammerControllerTest.php

With any luck, Sebastian Bergmann will tell you that everything is ok! Of
course I never trust a test that passes on the first try, so be sure to change
things and make sure it fails when it should too.

.. _`Postman`: http://www.getpostman.com/
.. _`PHPUnit`: http://phpunit.de/
