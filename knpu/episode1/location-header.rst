Updating the Location Header
============================

Hey, we have a working endpoint to view a single programmer! We're awesome :)
Now do you remember the ``Location`` response header we're return after creating a new
programmer? Let's update that to be a real value.

To do this, first add a ``bind`` function to our programmer route::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));

        $controllers->get('/api/programmers/{nickname}', array($this, 'showAction'))
            ->bind('api_programmers_show');
    }

This gives the route an internal name of ``api_programmers_show``. We can
use that below to generate a proper URL to the new programmer resource::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...

        $response = new Response('It worked. Believe me - I\'m an API', 201);
        $programmerUrl = $this->generateUrl(
            'api_programmers_show',
            ['nickname' => $programmer->nickname]
        );
        $response->headers->set('Location', $programmerUrl);

        return $response;
    }

The ``generateUrl`` method is a shortcut I added for our app, and it combines
the ``nickname`` with the rest of the URL. You may make URLs differently in 
your app, but the idea is the same: set the ``Location`` header to the URI where 
I can GET this new resource.

.. tip::

    The ``generateUrl`` method is just a shortcut for doing this::

        $programmerUrl = $this->container['url_generator']->generate(
            'api_programmers_show',
            ['nickname' => $programmer->nickname]
        );

Update the ``testing.php`` script to print out the response from the original
POST so we can check this out::

    // testing.php
    // ...

    // 1) Create a programmer resource
    $request = $client->post('/api/programmers', null, json_encode($data));
    $response = $request->send();

    echo $response;
    echo "\n\n";
    die;

    // 2) GET a programmer resource
    // ...

And when we run it again, we've got a working ``Location`` header:

.. code-block:: text

    HTTP/1.1 201 Created
    ... 
    Location: /api/programmers/ObjectOrienter330

    It worked. Believe me - I'm an API

Using the Location Header
-------------------------

The ``Location`` header is more than just a nice thing. Its purpose is to
help the client know where to go next without needing to hardcode URLs or
URL patterns. To prove this, we can update our testing script to read the
``Location`` header and use it for the next request. This lets us *remove*
the hardcoded URL pattern we had before::

    // testing.php
    // ...

    // 1) Create a programmer resource
    $request = $client->post('/api/programmers', null, json_encode($data));
    $response = $request->send();

    $programmerUrl = $response->getHeader('Location');

    // 2) GET a programmer resource
    $request = $client->get($programmerUrl);
    $response = $request->send();

    echo $response;
    echo "\n\n";

If the URL pattern to view a programmer changes in the future, our client
won't break. That's really powerful. But it's also where things start to get
complicated. More on that later, dear warrior.

