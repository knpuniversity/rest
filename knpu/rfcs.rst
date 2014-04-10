Fixing the Content-Type on POST
-------------------------------

We now have 3 working endpoints, but one still has a big issue. The POST
*still* returns a text string as its response. So what *should* a POST body
contain after creating a resource? Your best option is to return a representation
of the new resource. So let's do that::

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

And of course, don't forget to set the ``Content-Type`` header to ``application/json``.
To test, print out that response temporarily and try it::

    // testing.php
    // ...

    // 1) Create a programmer resource
    $request = $client->post('/api/programmers', null, json_encode($data));
    $response = $request->send();

    echo $response;
    echo "\n\n";die;

And actually, since returning JSON is so common, Silex has a shortcut: the
``JsonResponse`` class. It takes care of running ``json_encode`` *and* setting
the ``Content-Type`` header for us::

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

That's just there for convenience, but it cuts down on some code.

Finding Spec Information
------------------------

By the way, how do I know these rules, like that a 201 response should have
a status code or that it should return the entity body? These guidelines
come from the IETF and the W3C in the form of big technical RFC's. They're
not always easy to interpret, but sometimes they're awesome. For example,
if you google for ``http status 201`` you'll find a the famous `RFC 2616`_,
which gives us the details about the 201 status code and most of the underlying
guidelines for how HTTP works.

I'll help you navigate these rules. But as we go, try googling for answers
and seeing what's out there.

.. _`RFC 2616`: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html