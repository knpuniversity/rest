Handling Errors
===============

Things are looking nice, but actually, our API is kinda unusable.

We're missing a really important piece: errors! Unless we properly handle
errors, our API is going to be a pain to use because the client won't know
what it's doing wrong. If we deployed right now and a client tried to create
a programmer with a nickname that already exists, it would get a 500
error with no details. Bummer!

Writing the Test
----------------

Let's start with a simple case: when a client POST's to ``/api/programmers``,
we should add validation to make the ``nickname`` field required.

Like always, let's start with the test.

We can copy the working scenario for creating a programmer, but remove the
``nickname`` field from the request payload. Obviously, the status code won't
be 201. Because this is a *client* error, we'll need a 400-level status code.
But which one? Ah, this is another spot of wonderful debate! The most common
is probably 400, which means simply "Bad Request". If we look back at the
`RFC 2616`_ document, the description of the 400 status code seems to fit
our situation:

    The request could not be understood by the server due to malformed syntax.
    The client SHOULD NOT repeat the request without modifications.

The other common choice is 422: Unprocessable Entity. 422 comes from a different
RFC and is used when the format of the data is ok, but semantically, it has
errors. We're adding validation for the business rule that a nickname is required,
which is totally a semantic detail. So even though 422 seems to be less common
than 400 for validation errors, it may be a more proper choice:

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: Validation errors
      Given I have the payload:
        """
        {
          "avatarNumber" : "2",
          "tagLine": "I'm from a test!"
        }
        """
      When I request "POST /api/programmers"
      Then the response status code should be 400

Now, what should the response content look like? Obviously, it'll be JSON
for our API, but let me suggest a structure that looks like this:

.. code-block:: json

    {
        "type": "valdiation_error",
        "title": "There was a validation error",
        "errors": {
            "nickname": "Please enter a nickname"
        }
    }

Just trust me on the structure for now. In the test, let's look for these
3 fields and make sure we have a ``nickname`` error but no ``avatarNumber`` 
error.

.. code-block:: gherkin

    # features/api/programmer.feature
    # ...

    Scenario: Validation errors
      Given I have the payload:
        """
        {
          "avatarNumber" : "2",
          "tagLine": "I'm from a test!"
        }
        """
      When I request "POST /api/programmers"
      Then the response status code should be 400
      And the following properties should exist:
        """
        type
        title
        errors
        """
      And the "errors.nickname" property should exist
      But the "errors.avatarNumber" property should not exist

Adding Validation
-----------------

Test, check! Next, let's actually add some validation. This is one of those
spots that will be different based on your framework, but the important thing
is how we *communicate* those errors to the client.

In Silex, to make the ``nickname`` field required, we need to open up the
``Programmer`` class itself. Here, add a ``NotBlank`` annotation with a nice
message::

    // src/KnpU/CodeBattle/Model/Programmer.php
    // ...

    class Programmer
    {
        // ...

        /**
         * @Assert\NotBlank(message="Please enter a clever nickname")
         */
        public $nickname;

        // ...
    }

Cool! Next, open up the ``ProgrammerController`` class. In ``newAction``,
we should check the validation before saving the new ``Programmer``. I've
created a shortcut method called ``validate`` that does this::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...
        $this->handleRequest($request, $programmer);

        $errors = $this->validate($programmer);

        $this->save($programmer);
        // ...
    }

It uses the annotation we just added to the ``Programmer`` class and returns
an array of errors: one error for each field. If you *are* using Silex or
Symfony, you can re-use my shortcut code with your project. If you're not,
just make sure you have some way of getting back an array of errors.

If the ``$errors`` array isn't empty, we've got a problem! And since we already
wrote the test, we know *how* we want to tell the user. Create an array with
the ``type``, ``title`` and ``errors`` fields. The ``$errors`` variable is
already an associative array of messages, where the keys are the field names.
So we can just set it on the ``errors`` field::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        // ...

        $errors = $this->validate($programmer);
        if (!empty($errors)) {
            $data = array(
                'type' => 'validation_error',
                'title' => 'There was a validation error',
                'errors' => $errors
            );

            return new JsonResponse($data, 400);
        }

        $this->save($programmer);
        // ...
    }

Just like with any other API response, we can create a ``JsonResponse`` class
and pass it our data. The only difference with this endpoint is that it has
a status code of 400.

While we're here, let's move the saving of the programmer out of ``handleRequest``
and into ``newAction`` and ``updateAction``::

    public function newAction(Request $request)
    {
        $programmer = new Programmer();

        $this->handleRequest($request, $programmer);

        $errors = $this->validate($programmer);
        if (!empty($errors)) {
            // ...
        }
        
        $this->save($programmer);

        // ...
    }
    
    public function updateAction(Request $request, $nickname)
    {
        // ... make the same change here, add $this->save($programmer);
    }

    private function handleRequest(Request $request, Programmer $programmer)
    {
        // ...

        $programmer->userId = $this->findUserByUsername('weaverryan')->id;
    }

This way, we can save the programmer *only* if there are *no* validation errors.

Let's try it!

.. code-block::: bash

    $ php bin/vendor/behat

Awesome, all green!

Validation on Update
--------------------

What's that? You want to check our validation rules when updating too? Great
idea!

To avoid duplication, create a new private function in the controller called
``handleValidationResponse``. We'll pass it an array of errors and it will
transform into living robotic beings originating from the distant machine 
world of Cybertron err ... I mean the proper 400 JSON response::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    private function handleValidationResponse(array $errors)
    {
        $data = array(
            'type' => 'validation_error',
            'title' => 'There was a validation error',
            'errors' => $errors
        );

        return new JsonResponse($data, 400);
    }

Now that we have this, use it in ``newAction`` and ``updateAction``::

    // newAction and updateAction

    $this->handleRequest($request, $programmer);

    if ($errors = $this->validate($programmer)) {
        return $this->handleValidationResponse($errors);
    }

    $this->save($programmer);

To make sure we didn't break anything, we can run our tests:

.. code-block::: bash

    $ php bin/vendor/behat

We could have also added another scenario to test the validation when updating.
But I'm not made of scenarios people! How detailed you get with your tests is up to you.

.. _`RFC 2616`: http://www.w3.org/Protocols/rfc2616/rfc2616.html
