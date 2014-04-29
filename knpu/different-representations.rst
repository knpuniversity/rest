GET Representation != POST Representation
=========================================

So far, the representation of a programmer that we send in our PUT request
exactly matches the representation the server sends us in a GET request.
But it does't need to be this way. It would be perfectly legal to design
our API so that we have an ``avatarNumber`` field when we POST or PUT a programmer,
but then get back an ``avatarURL`` when we GET that same programmer resource.

The point is that I don't want you to feel like the data your API receives
needs to look exactly like the data you send back. Nope. Both are just *representations*
of a resource.

With that said, if you make the representations inconsistent for no reasons,
your API users will hunt you down with pitchforks. So if you expect ``avatarNumber``
in the POST body, don't send bck ``avatar_number`` in the GET request. That's
just mean.

Immutable Properties
--------------------

In our API, the programmer's nickname is its unique, primary key. So, I don't
really want it to be editable. In other words, even though the response representation
of a programmer resource will contain a ``nickname`` property, a PUT request
to update it should *not* have this field. It's a small examle of how the
same programmer resource may be represented differently in different situations.

Let's first add to our scenario to test that even if we send a ``nickname``
field, the resource's nickname doesn't change:

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: PUT to update a programmer
      # ...
      And I have the payload:
        """
        {
          "nickname": "CowgirlCoder",
          "avatarNumber" : 2,
          "tagLine": "foo"
        }
        """
      # ...
      But the "nickname" property should equal "CowboyCoder"

Run the test first to make sure it's failing. Next, let's update the ``handleRequest``
function to only set the ``nickname`` on a *new* Programmer::

    private function handleRequest(Request $request, Programmer $programmer)
    {
        $data = json_decode($request->getContent(), true);
        $isNew = !$programmer->id;

        if ($data === null) {
            throw new \Exception(sprintf('Invalid JSON: '.$request->getContent()));
        }

        // determine which properties should be changeable on this request
        $apiProperties = array('avatarNumber', 'tagLine');
        if ($isNew) {
            $apiProperties[] = 'nickname';
        }
        
        // ...
    }

Now run the test:

.. code-block:: bash

    $ php vendir/bin/behat

Perfect! We've decided just to ignore these "extra" properties. You could
also decide to return an error response instead. It just depends on if your
taste. What we did here is easier to use, but our client may also not notice
that we're ignoring some of the submitted data. We'll talk about error responses
in a few minutes.
