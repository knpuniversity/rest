PUT: Editing Resources
======================

We can create a programmer resource, view a representation of a programmer,
or view the collection representation for all programmers.

PUT: The Basic Definition
-------------------------

Now let's make it possible to edit an existing programmer! Depending on who
you ask, there are about 10 HTTP methods, and the 4 main ones are

* GET
* POST
* PUT
* DELETE

We know GET is for retrieving a representation and DELETE is pretty clearly
going to be used to delete a resource.

Things get trickier with POST and PUT. I'm about to say something that's
**incorrect**. Ready?

POST is used for creating resources and PUT is used for updating.

Seriously, this is **not true**, and it's dangerous to say: there are REST
fanatics waiting around every corner to tackle you when you say bad things
like this.

But in practice, this statement is pretty close. So let's use the PUT method
for our edit endpoint. Afterwards, we'll geek out on the *real* difference
between POST and PUT.

Writing the Test
----------------

In my opinion, the easiest way to get the endpoint working is to write the
test first. When the test passes, we'll know the new endpoint works correctly.

Yep, let's add *another* scenario:

.. code-block:: gherkin

    TODO: PUT Programmer: Write the test

This looks a lot like our POST scenario, and with good reason: consistency!
It would be a real bummer if the data we sent to the server looked dramatically
different based no whether we're creating or updating a programmer. The status
code *is* different: 201 is used when an asset is created but the normal 200
is used when it's an update.

- we're sending a "representation" of the resource, which the server uses
  to update the underlying resource

Just to keep us tied into the theory of things, I'll describe this using
REST-nerd language. Ready? Ok.

This tests that when we send a "representation" of a programmer resource
via PUT, the server will use it to update that resource and return a representation.

.. index::
   single: HTTP Methods; 405

We haven't actually coded this yet, so when we run the test, it fails:

.. code-block:: bash

    $ php vendir/bin/behat

The status code isn't 200, it's 405. 405 means "method not allowed", and our
framework is doing this for us. It's a way of saying "Hey, ``/api/programmers/CowboyCoder``
*is* a valid resource, but it doesn't support the PUT method."

If your API doesn't support an HTTP method for a resource, you should return
a 405 response. If you use a decent framework, it'll do this for you.

Coding up the PUT Endpoint
--------------------------

Let's add the PUT support! First, create the route, except this time you'll
use the ``put`` method to make this route respond only to PUT requests::

    TODO: PUT Programmer: Create PUT endpoint - routing

Next, copy the ``newAction`` and rename it to ``updateAction``, because these
will do almost the same thing. The biggest difference is that instead of
creating a new ``Programmer`` object, we'll query the database for an existing
object and update it. Heck, we can steal that code from ``showAction``. Just
be sure that you're still setting the ``nickname`` and ``avatar`` properties::

    TODO: PUT Programmer: Create PUT endpoint - part of controller

Now just change the status code from 201 to 200, since we're no longer creating
a resource. And you can also remove the ``Location`` header::

    TODO: PUT Programmer: Create PUT endpoint - method

We only need this header with the 201 status code when a resource is created.
And it makes sense: when we create a new resource, we don't know what its
new URL is. But when we're editing an existing resource, we clearly already
have that URL, because we're using it to make the edit.

Run the Test and Celebrate
--------------------------

Time to run the test!

.. code-block:: bash

    $ php vendir/bin/behat

Woot! It passes! And we can even run it over and over again.

Debugging Tests
---------------

But what if this had failed? Let's pretend we coded something wrong by throwing
a big ugly exception in our controller::

    PUT Programmer: Temporarily making the test fail

Now run the test again:

.. code-block:: bash

    $ php vendir/bin/behat

It fails because we're getting a 500 error instead of 200. But we can't really
see what's going on because we can't see the big error page!

But don't worry! First, I've done my best to configure Behat so when something
fails, part of the last response that was made to the server before the failure
is printed below.

.. tip::

    This functionality works by returning the h1 and h2 elements of the HTML
    page. If your app shows erorrs with different markup, tweak the
    ``ApiFeatureContext::printLastResponseOnError`` method to your liking.

If this doesn't tell you enough, we can print out the last response in its
entirety. To do this, add "And print last response" to our scenario, just
*before* the failing line:

    PUT Programmer: Temporarily making the test fail

Now just re-run the test:

.. code-block:: bash

    $ php vendir/bin/behat

It may be ugly, but the entire response of the last request our test made
is printed out, including all the header information on top. Once you've
figured out and fixed the problem, just take the ``print last response``
line out and keep going!

Oh no, Duplicate Code
---------------------

Our tests are passing, but we're doing a bad job, because we're now duplicating
code between ``newAction`` and ``updateAction`` in ``ProgrammerController``!

We can do better than that! Create a new private function called ``handleRequest``
and copy the code into it that reads the request body and sets the data on
the Programmer::

    PUT Programmer: Refactor the "form" update code: new function

Cool! Now we can just call this from ``newAction`` and ``updateAction``::

    PUT Programmer: Refactor the "form" update code: using function

Re-run the tests to see if we broke anything:

.. code-block:: bash

    $ php vendir/bin/behat

Cool! I'm going to change how this code is written *just* a little bit so
that it's even more dynamic::

    PUT Programmer: make handleRequest a little fancier

There's nothing important in this change, but it'll make some future changes
easier to understand. If you're using a form library or have a fancier ORM,
you might be able to do something like this much easier than I am.

Your Representation Doesn't need to be the same between GET and POST
--------------------------------------------------------------------

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

    PUT Programmer: Making certain properties not mutable

Run the test first to make sure it's failing. Next, let's update the ``handleRequest``
function to only set the ``nickname`` on a *new* Programmer::

    PUT Programmer: Making certain properties not mutable

Now run the test:

.. code-block:: bash

    $ php vendir/bin/behat

Perfect!

POST versus PUT
---------------

POST versus PUT: one of those conversations you try *not* to have. It leads
to fights, flamewars, and sadness. People are passionate about REST, and
this is one of this really sensitive topics.

First, you can read the technical descriptions in the `rfc2616`_ document
I mentioned earlier. It's actually pretty cool stuff.

Safe Methods
~~~~~~~~~~~~

First, each HTTP method is said to be "safe" or "unsafe". An HTTP method
is "safe" if using it doesn't modify anything on the server. Ok, yes, logs
will be written and maybe analytics will write data, but "safe" methods should
never modify any data. GET and HEAD are "safe" methods.

Making a request with an unsafe methods - like POST, PUT and DELETE - *does*
change data. Actually, an unsafe request *may not* change anything, for example
if you update a programmer's ``avatarNumber`` to the value it already has.

But the point is that if a client uses an unsafe method, it knows that this
method may have consequences. But if it uses a safe method, that request
won't ever have consequences. You could of course write an API that violates
this. But that's dishonest - like showing a picture of ice cream and then
giving people broccoli. I like brocolli, but don't be a jerk.

Being "safe" affects caching. Safe requests can be cached by a client, but
unsafe requests can't be. But caching is a whole different topic!

Idempotent
~~~~~~~~~~

Within the unsafe methods, we have to talk about the famous "idempotency".
A request is idempotent if the side effects of making the request 1 time
is the same as making the request 2, 3, 4, or 1000 times. PUT and DELETE
are idempotent, POST is not.

For example, if we make the PUT request from our test once, it updates the
``avatarNumber`` to 2. If we make it again, the ``avatarNumber`` will still
be 2. If we make the PUT request 1 time or 10 times, the server always results
in the same state.

Now think about the POST request in our test, and imagine for a second that
the ``nickname`` doesn't have to be unique, because the detail clouds things
here unnecessarily.

If we make the request once, it would create a programmer, if we make it again,
it'll create *another* programmer. So making the request 10 times is not
the same as making it just once. This is *not* idempotent.

Now you can see why it *seems* right to say that POST creates resources and
PUT updates them.

POST or PUT? The 2 Rules of PUT
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Other than PATCH, which is an edge case we'll discuss next, if you're building
an endpoint that will modify data, it should use a POST or PUT method.

Deciding between POST and PUT is easy: use PUT if and only if the endpoint
will follow these 2 rules:

1. The endpoint must be idempotent, so safe to redo the request over and
   over again;

2. The URI must be the address to the resource being updated.

When we use PUT, we're saying that we want the resource that we're sending
in our request to be stored at the given URI. We're literally "putting" the
resource at this address.

This is what we're doing when we PUT to ``/api/programmers/CowboyCoder``.
This results in an update because ``CowboyCoder`` already exists. But imagine
if we changed our controller code so that if ``CowboyCoder`` didn't exist,
it would be created. Yes, that would *still* be a PUT: we're putting the
resource at this URI. Because of this, PUT is usually thought of as an update,
but it could also be used to create resources. You may never use PUT this
way, but keep this difference in mind.

Practically speaking, if your endpoint is idempotent and the result is that
you're setting the value of the resource in your URI, use PUT. Otherwise,
use POST.

Technically speaking, we *could* support making a PUT request to ``/programmers``.
But if we did and we followed the rules of PUT, we'd expect the client to
pass us a colletion of programmers, and we'd replace the entire collection
with this new one. 

We'll look at some more complex PUT and POST examples later, but this lays
the groundwork. 

POST and Non-Idempotency
~~~~~~~~~~~~~~~~~~~~~~~~

POST is not idempotent, so making a POST request more than one time *may*
have additional side effects, like creating a second, third and fourth user.
But the key word here is *may*. Just because an endpoint uses POST doesn't
mean that it *must* have side effects on every request. It just *might* have
side effects.

When choosing between PUT and POST, don't just say "this request is idempotent,
it must be PUT!". Instead, look at the above 2 rules for put. If it fails
one of those, use POST: even if the endpoint is idempotent.

NOTES
-----

- I think there's way too much theory at the end - should move some of this
    later to when we run into it more directly