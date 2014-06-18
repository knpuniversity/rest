PATCH: The Other Edit
=====================

The rules of HTTP say that in normal situations, if you want to edit a resource,
you should make a PUT request to it and include the new representation you
want.

.. note::

    What are the non-normal situations? That would include custom endpoints,
    like an endpoint to "power up" a programmer. This will edit the programmer
    resource, but will have a different URI and could be POST or PUT, depending
    on all that idempotency stuff.

PUT is Replace
--------------

To get technical, PUT says "take this representation and entirely put it
at this URI". It means that a REST API should require the client to send
*all* of the data for a resource when updating it. If the client *doesn't*
send a field, a REST API is supposed to set that field to null.

Right now, our API follows this rule. If a client sends a PUT request to
``/api/programmers/ObjectOrienter`` to update the ``avatarNumber`` field
but forgets to include the ``tagLine``, the ``tagLine`` will be set to null.
Woops! There goes some perfectly good data. So PUT is really more of a 
*replace* than an *update*. 

This is how PUT is *supposed* to work. But not all API's follow this rule,
because it's a bit harsh and might cause data to be set to blank without
a client intending to do that.

We're going to follow the rules and keep our PUT implementation as a replace.
But how could we allow the client to update something without sending every
field?

Introducing PATCH: The (Friendly) Update Method
-----------------------------------------------

With PATCH of course! The main HTTP methods like GET, POST, DELETE and PUT
were introduced in the famous RFC 2616 document. Maybe you've heard of it? 
But because PUT has this limitation, PATCH was born in `RFC 5789`_.

Typically, PATCH is used exactly like PUT, except if we don't send a ``tagline``
field then it keeps its current value instead of obliterating it to null. PATCH, 
it's the friendly update.

Writing the Test
----------------

Let's write a scenario for PATCH, one that looks like our PUT scenario, but
*only* sends the ``tagLine`` data in the representation. Of course, we'll
want to check that the ``tagLine`` was updated, but also that the ``avatarNumber``
is unchanged:

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: PATCH to update a programmer
      Given the following programmers exist:
        | nickname    | avatarNumber | tagLine |
        | CowboyCoder | 5            | foo     |
      And I have the payload:
        """
        {
          "tagLine": "giddyup"
        }
        """
      When I request "PATCH /api/programmers/CowboyCoder"
      Then the response status code should be 200
      And the "avatarNumber" property should equal "5"
      And the "tagLine" property should equal "giddyup"

Coding the Endpoint
-------------------

Let's head into our controller and add another routing line that matches
the same ``/api/programmers/{nickname}`` URI, but only on PATCH requests.
This looks a little bit different only because Silex doesn't natively support
configuring PATCH requests. But the result is the same as the other routing
lines::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    protected function addRoutes(ControllerCollection $controllers)
    {
        // ...

        // point PUT and PATCH at the same controller
        $controllers->put('/api/programmers/{nickname}', array($this, 'updateAction'));

        // PATCH isn't natively supported, hence the different syntax
        $controllers->match('/api/programmers/{nickname}', array($this, 'updateAction'))
            ->method('PATCH');

        // ...
    }

I made this route use the same ``updateAction`` function as the PUT route.
That's not on accident: these two HTTP methods are so similar that I bet
we can save some code by re-using the controller function.

The only difference with PATCH is that if we don't see a field in the JSON
that the client is sending, we should just skip updating it. Add an ``if``
statement that checks to see if this is a ``PATCH`` method and also if the
field is missing from the JSON. If both are ``true``, let's do nothing::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleRequest(Request $request, Programmer $programmer)
    {
        // ...
        $apiProperties = array('avatarNumber', 'tagLine');
        // ...

        foreach ($apiProperties as $property) {
            // if a property is missing on PATCH, that's ok - just skip it
            if (!isset($data[$property]) && $request->isMethod('PATCH')) {
                continue;
            }

            $val = isset($data[$property]) ? $data[$property] : null;
            $programmer->$property = $val;
        }

        // ...
    }

And just like that, we *should* have a working PATCH endpoint. And if we
somehow broke our PUT endpoint, our tests will tell us!

But we're in luck! When we run Behat, everything still comes back green.
We now have 2 methods a client can use to update a resource: PUT and PATCH.

Should I Support PUT and PATCH?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All of this wouldn't be RESTful if it weren't a bit controversial. Because
PUT's correct behavior is harsh, many APIs support PUT, but make it
act like PATCH. Do what's best for your API clients, be consistent, and then
make sure it's perfectly clear how things work. Remember, the more you
bend the rules, the weirder your API will look when people are learning it.

The Truth Behind PATCH
----------------------

And about PATCH, I've been lying to you. We're *technically* using PATCH
incorrectly. womp womp... Let's go back to `RFC 5789`_ where it describes 
PATCH with a little more detail:

    In a PUT request, the enclosed entity is considered to be a modified
    version of the resource stored on the origin server, and the client is
    requesting that the stored version be replaced. With PATCH, however,
    the enclosed entity contains a set of instructions describing how a resource
    currently residing on the origin server should be modified to produce
    a new version.

Let me summarize this. With PUT, we send a representation of the resource.
But with PATCH, we send a set of *instructions* on what to edit, not a representation.
So instead of a JSON programmer, we might instead create some JSON structure
with details on what to update:

.. code-block:: json

    [
        { "op": "replace", "path": "avatarNumber", "value": "5" },
        { "op": "remove", "path": "tagLine" }
    ]

In fact, even this little structure here comes from another proposed standard,
`RFC 6902`_. If you want to know more about this, read the blog post
`Please. Don't Patch Like An Idiot`_ from this tutorial's co-author Mr. William Durand.

So what should you do in your API? It's tough, because we live in a world
where the most popular API's still bend the rules. Try to follow the rules
for PUT and PATCH the best you can, while still making your API very easy
for your clients. And above everything, be consistent and outline your implementation
in your docs.

.. _`RFC 5789`: https://tools.ietf.org/html/rfc5789
.. _`RFC 6902`: https://tools.ietf.org/html/rfc6902
.. _`Please. Don't Patch Like An Idiot`: http://williamdurand.fr/2014/02/14/please-do-not-patch-like-an-idiot/
