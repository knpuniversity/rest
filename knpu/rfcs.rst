Fixing the Content-Type on POST
===============================

Check us out! We now have 3 working endpoints, but one has a big issue. The POST
*still* returns a text string as its response. Even if you don't know what
it *should* return, that's embarassing. Come on, we can do better!

After creating a resource, one great option is to return a representation
of the new resource. Use the ``serializeProgrammer`` to get JSON and
put it into the Response::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...
        $this->save($programmer);

        $data = $this->serializeProgrammer($programmer);
        $response = new Response(
            json_encode($data),
            201
        );
        $programmerUrl = $this->generateUrl(
            'api_programmers_show',
            ['nickname' => $programmer->nickname]
        );
        $response->headers->set('Location', $programmerUrl);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

If the client needs that information, then we've just saved them one API
request.

And don't forget to set the ``Content-Type`` response header to ``application/json``.
To see your handy work, print out that response temporarily and try it::

    // testing.php
    // ...

    // 1) Create a programmer resource
    $request = $client->post('/api/programmers', null, json_encode($data));
    $response = $request->send();

    echo $response;
    echo "\n\n";die;

A JsonResponse Shortcut
-----------------------

And actually, since returning JSON is so common, Silex has a shortcut: the
``JsonResponse`` class. It takes care of running ``json_encode`` *and* setting
the ``Content-Type`` header for us -- double threat!::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...
    use Symfony\Component\HttpFoundation\JsonResponse;

    public function newAction(Request $request)
    {
        // ...
        $this->save($programmer);

        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, 201);
        $programmerUrl = $this->generateUrl(
            'api_programmers_show',
            ['nickname' => $programmer->nickname]
        );
        $response->headers->set('Location', $programmerUrl);

        return $response;
    }

That's just there for convenience, but it cuts down on some code. If your
framework or application doesn't have anything like this, create a class
or function to help with this: it will go a long way towards following our
favorite motto: be consistent.

Finding Spec Information
------------------------

By the way, how do I know these rules, like that a 201 response should have
a ``Location`` header or that it should return the entity body? These guidelines
come from the IETF and the W3C in the form of big technical documents called RFC's. 
They're not always easy to interpret, but sometimes they're awesome. For example,
if you google for ``http status 201`` you'll find the famous `RFC 2616`_,
which gives us the details about the 201 status code and most of the underlying
guidelines for how HTTP works.

I'll help you navigate these rules. But as we go, try googling for answers
and seeing what's out there. Some RFC's, like 2616, are older and well adopted.
Others are still up for comment and being interpreted. Some of which we'll cover
later.

.. _`RFC 2616`: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
