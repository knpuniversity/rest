The application/problem+json Content-Type
=========================================

Sometimes, there are clear rules in the API world. When we create a new resource,
returning a 201 status code is the *right* thing to do.

But other times, there aren't clear rules. What we're working on right now
is a good example: there's no standard for *how* API error responses should look.

Our response has ``type``, ``title``, and ``errors`` fields. And I didn't invent
this: it's part of a young, potential standard called `API Problem`_, or Problem
Details. When we google for it, we find an RFC document of course! Actually,
this is technically an "Internet Draft": a work-in-progress document that
*may* eventually be a standard. If you use this, then you should
understand that it may change in the future or be discarded entirely for
something different.

But in the API world, sometimes we can choose to follow a draft like this, 
or nothing at all. In other words, we can choose to make our API consistent 
with at least *some* other API's, or consistent with noone else.

Oh, and when you're reading one of these documents, make sure you're on the
latest version - they're updated all the time.

Dissecting API Problem
----------------------

If we read a little bit, we can see that this standard outlines a response
that typically has a ``type`` field, a ``title`` and sometimes a few others.
The ``type`` field is the internal, unique identifier for an error and the
title is a short human description for the ``type``. If you look at our ``type``
and ``title`` values, they fit this description pretty well.

And actually, the ``type`` is supposed to be a URL that I can copy into my
browser to get even more information about the error. Our value is just a
plain string, but we'll fix that later.

The spec also allows you to add any extra fields you want. Since we need
to tell the user what errors there are, we've added an ``errors`` field.

This means that our error response is *already* following this spec. Yay! 
And since someone has already defined the meaning of some of our fields,
we can link to this document as part ouf or API's docs FTW! 

Media Types and Structure Versus Semantics
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Of course right now, there's no way an API client would know that we're leveraging
this draft spec, unless they happen to recognize the structure. It would
be much better if the response somehow screamed "I'm using the Problem Details
spec!".

And this is totally possible by sending back a ``Content-Type`` header of
``application/problem+json``. This says that the actual format is ``json``,
but that a client can find out more about the underlying meaning or semantics
of the data by researching the ``application/problem+json`` Content-Type.

So the ``json`` part tells us how to *parse* the document. The ``problem``
part give us a hint on how to find out the human *meaning* of its data.

This is called the media type of the document, and if you google for
`IANA Media Types`_, you'll find a page of all of the official recognized
types. You can see that there are a lot of media types that end in ``+json``,
like one for expressing calendar data. If you were sending calendar data,
you might *choose* to use this format. Why? Because it would mean you're
following a standard that is already documented, and whose structure people
spent a lot of timing thinking about.

Right now, I just want you to be aware that this exists, and that a lot of
people invest a lot of time into answering questions like: "If 2 machines
are sending calendar data, how should it be structured?".

Our ``application/problem+json`` actually isn't in this list, because it's
just a draft.

Setting the Content-Type Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

But even still, we want people to know our error response is using this media
type. First, let's update the test to look for this ``Content-Type`` header:

    # features/api/programmer.feature
    # ...

    Scenario: Validation errors
      # all the current scenario lines
      # ...
      And the "Content-Type" header should be "application/problem+json"

Next, add the header to our response. We've added plenty of response headers
already, and this is no different::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleValidationResponse(array $errors)
    {
        // ...

        $response = new JsonResponse($data, 400);
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }

When we try the tests, they still pass!

.. code-block::: bash

    $ php bin/vendor/behat

And now the client knows a bit more about our error response, without us
writing even one line of documentation.

.. _`API Problem`: http://tools.ietf.org/html/draft-nottingham-http-problem
.. _`IANA Media Types`: http://www.iana.org/assignments/media-types/media-types.xhtml
