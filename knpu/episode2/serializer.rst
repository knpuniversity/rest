The Serializer: Swiss-Army Knife of APIs
========================================

Hey guys! Welcome to episode 2 of our RESTFUL API's in the real world series.
In episode 1 we covered a lot of the basics. Phew! And explained really important
confusing terms like representations, resources, idempotency, GET, POST, PUT,
PATCH and a lot of other things that really make it difficult to learn REST.
Because, really before that you had no idea what anybody was talking about. 
At This point I hope that you feel like you have a nice base because we're 
going to take that base and start doing some interesting things like 
talking about serializers, hypermedia HATEOAS, documentation and a ton more.

.. tip::

    Hey, go download the starting code for this repository right on this page!

I've already started by downloading the code for Episode 2 onto my computer.
So I'm going to move over to the ``web/`` directory, which is the document
root for our project, and use the built-in PHP Web server to get things running:

.. code-block:: bash

    cd /path/to/downloaded/code
    cd web
    php -S localhost:8000

Let's try that out in our browser... and there we go.

    http://localhost:8000

You can login as ``ryan@knplabs.com`` with password ``foo`` - very secure -
and see the web side of our site. There's a web side of our site and an API
version of our site, which we created in Episode 1.

The project is pretty simple, but awesome, you create programmers, give them
a tag line, select from of our avatars and then battle a project. So we'll
start a battle here, fight against "InstaFaceTweet"... some magic  happens,
and boom! Our happy coder has defeated the InstaFaceTweet project.

So from an API perspective, you can see that we have a number of resources:
we have programmers, we have projects and we also have battles. And they all
relate to each other, which will be really important for episode 2.

What We've Already Accomplished
-------------------------------

The API code all lives inside of the ``src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php``
file. And in episode 1, we created endpoints for listing programmers, showing
a single programmer, updating a programmer and deleting one::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    class ProgrammerController extends BaseController
    {
        // ...
    
        public function newAction(Request $request)
        {
            // ...
        }
        
        // other methods for updating, deleting etc
    }

We also used Behat to make some nice scenarios for this:

.. code-block:: gherkin

    # features/api/programmer.feature
    Feature: Programmer
      # ...

      Background:
        Given the user "weaverryan" exists

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

      # ... additional scenarios


This makes sure that our API doesn't break. It also lets us design our API,
before we think about implementing it.

Whenever I have an API, I like to have an object for each resource. Before
we started in episode 1, I created a ``Programmer`` object. And this is actually
what we're allowing the API user to update, and we're using this object to
send data back to the user::

    <?php

    // src/KnpU/CodeBattle/Model/Programmer.php
    // ...

    class Programmer
    {
        public $id;

        public $nickname;

        public $avatarNumber;

        public $tagLine;

        // ... a few more properties
    }

Serialization: Turning Objects into JSON or XML
-----------------------------------------------

So one of the key things we were doing was turning objects into JSON. For
example, let's look in ``showAction``. We're querying the database for a
``Programmer`` object, using a simple database-system I created in the background.
Then ultimately, we pass that object into the ``serializeProgrammer`` function,
which is a simple function we wrote inside this same class. It just takes
a ``Programmer`` object and manually turns it into an array::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...
    
    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        // ...
        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, 200);

        return $response;
    }

    // ...
    private function serializeProgrammer(Programmer $programmer)
    {
        return array(
            'nickname' => $programmer->nickname,
            'avatarNumber' => $programmer->avatarNumber,
            'powerLevel' => $programmer->powerLevel,
            'tagLine' => $programmer->tagLine,
        );
    }

This transformation from an object into an array is really important because
we're going to be doing it for every resource across all of our endpoints.

Installing JMS Serializer
~~~~~~~~~~~~~~~~~~~~~~~~~

The first thing we're going to talk about is a library that makes this a lot
easier and a lot more powerful, called a serializer. The one I like is called
`jms/serializer`_, so let's Google for that. I'll show you how this works
as we start using it. But the first step to installing any library is to
bring it in via Composer.

I'm opening a new tab, and going back into the root of my project, and then
copying that command:

.. code-block:: bash

    composer require jms/serializer

If you get a "command not found" for ``composer``, then you need to
`install it globally in your system`_. Or, you can go and download it directly,
and you'll end up with a ``composer.phar`` file. In that case, you'll run
``php composer.phar require`` instead.

Creating/Configuring the Serializer Object
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

While we're waiting, let's go back and look at the usage a little bit. What
we'll do is create an object called a "serializer", and there's this ``SerializerBuilder``
that helps us with this. Then we'll pass it some data, which for us will
be a ``Programmer`` object or a ``Battle`` object. And then it returns to
you the actual JSON string. So it takes an object and turns it into a string::

    // from the serialization documentation
    $serializer = JMS\Serializer\SerializerBuilder::create()->build();
    $jsonContent = $serializer->serialize($data, 'json');
    echo $jsonContent; // or return it in a Response

Now this is a little bit specific to Silex, which is the framework we're
building our API on, but in Silex, you have a spot where you can globally
configure objects that you want to be able to use. They're called services.
I'll create a new global object called ``serializer`` and we'll use code
similar to what you just saw to create the ``serializer`` object. We're doing
this because it will let me access that object from any of my controllers::

    // src/KnpU/CodeBattle/Application.php
    // ...
    
    private function configureServices()
    {
        // ...
        
        $this['serializer'] = $this->share(function() use ($app) {
            // todo ...
        });
    }

Before I start typing anything here, I'll make sure everything is done downloading.
Yes, it is - so I should be able to start referencing the serializer classes.
Start with the ``SerializerBuilder`` that we saw. We also need to set a cache
directory, because this library caches annotations that we'll use a bit later.
This is a fancy way in my app to tell it to use a ``cache/serializer`` directory
at the root of my project.

There's also a debug flag, and when that's true, it'll rebuild the cache
automatically. Finally, the last step tells the serializer to use the same
property names that are on the ``Programmer`` object as the keys on the JSON.
In other words, don't try to transform them in any way. And that's it!

.. code-block:: php

    // src/KnpU/CodeBattle/Application.php
    // ...
    
    private function configureServices()
    {
        // ...
        
        $this['serializer'] = $this->share(function() use ($app) {
            return \JMS\Serializer\SerializerBuilder::create()
                ->setCacheDir($app['root_dir'].'/cache/serializer')
                ->setDebug($app['debug'])
                ->setPropertyNamingStrategy(new IdenticalPropertyNamingStrategy())
                ->build();
        });
    }

Using the Serializer Object
---------------------------

The important thing here is that we have a ``serializer`` object and we can
access it from any of our controllers. Let's open our ``ProgrammerController``
and rename ``serializeProgrammer`` to just ``serialize`` since it can serialize
anything.

I've setup my application so that I can reference any of those global objects
by saying ``$this->container['serializer']``. This will look different for
you: the important point is that we need to access that object we just configured.

Now, just call ``serialize()`` on it, just like the documentation told us.
I'll put ``json`` as the second argument so we get JSON. The serializer can
also give us another format, like XML::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function serialize($data)
    {
        return $this->container['serializer']->serialize($data, 'json');
    }

Perfect! Now let's look to see where we used the ``serializeProgrammer``
function before. That old function returned an array, but ``serialize`` now
returns the JSON string. So now we can return a normal ``Response`` object
and just pass the JSON string that we want. The one thing we'll lose is the
``Content-Type`` header being set to ``application/json``, but we'll fix
that in a second::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        $programmer = new Programmer();
        // ...

        $json = $this->serialize($programmer);
        $response = new Response($json, 201);

        // ... setting the Location header

        return $response;
    }

Let's go and make similar changes to the rest of our code.

In fact, when we have the collection of ``Programmer`` objects, things get 
much easier. We can pass the entire array of objects and it's smart enough 
to know how to serialize that. You can already start to see some of the 
benefits of using a serializer::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()->findAll();
        $data = array('programmers' => $programmers);
        $json = $this->serialize($data);

        $response = new Response($json, 200);

        return $response;
    }

Compared with what we had before, not a lot should have changed, because
the serializer should give us a JSON structure with all the properties in
``Programmer``. That's practically the same as we were doing before.

So let's run our tests!

.. code-block:: bash

    php vendor/bin/behat

We've totally changed how a Programmer gets turned into JSON, but *almost*
every test passes already! We'll debug that failure next.

.. _`jms/serializer`: http://jmsyst.com/libs/serializer
.. _`install it globally in your system`: https://getcomposer.org/doc/00-intro.md#globally
