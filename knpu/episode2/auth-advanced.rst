Auth Advanced
=============

So we have this working authentication system: you send a token, which lives
in a database table has a reference to your row in the user table. We use
that to look up who you are, authenticate you and then get down to work.

Because we're good programmers, we even wrote a scenario for this and made
sure that we can't create a programmer without authentication. What we didn't
do is create a scenario to check and see what happens if we send an *invalid*
token. So let's do that right now.

In this case, I'm *not* going to add a user to the database with this token.
I'm just going to send a token and that token is not going to exist.

Then, we'll just make any request that requires authentication. The response
status code should be 401 and remember we're always returning that API problem
format that has a ``detail`` property on it. And here, we can say whatever
we want. To be nice to the users, let's set it to "invalid credentials" so
they know what went wrong. It's not like they forgot the token, it just wasn't
valid.

Let's try this out. Again we can run Behat on one particular scenario. This
one starts on line 11. In fact you can see it *almost* passed, so out of
the box things are working. We are denying access, we are sending a 401,
and because of our security error handling in that ``ApiEntryPoint`` class,
we are sending a nice api problem format with the actual ``detail`` set to
"invalid credentials." Like before, this message comes from deep inside Silex
and is describing what's going wrong.

And because I want people to be excited about our API, I'm even going to
add an exclamation point to this. We'll see the difference this makes. We're
expecting "invalid credentials!" and we are getting it with a period. So
let's go find our translation file and and change this to our version.
That should do it!

Let's rerun things. Woops! I made a mistake - take that extra quote off.
And I made one other mistake: it's catching me on a case difference. So this
is why it is good to have tests, they have closer eyes than we do. So I'll
say "invalid credentials!" and a capital letter. Perfect! 

Next, we need all of our errors to *always* return that same api problem
response format. And when we return this format, we should always send back
its special ``Content-Type``. So let's make sure the ``Content-Type`` header
is correct here.

Ahh! It's not coming back with that. We are getting an application/problem-like
format, but without the right ``Content-Type`` header. It's coming back as
a simple ``application/json``.

In our app, when an exception is thrown, there are 2 different places that
take care of things. Most errors are handled in the ``Application`` class.
We added this in episode 1. But security errors are handled in ``ApiEntryPoint``,
and it's responsible for returning some helpful response.

So for example here, you can see why we get the ``detail`` and why we get
the 401. If I change this to 403 for example and rerun our test, you can
see it's getting back the 403 instead of the 401. Let's add the
``application/problem+json`` ``Content-Type`` header.

For consistency, one of the things we did in episode 1 is actually create
an ``ApiProblem`` class. The idea was whenever you had some sort of error
response you needed to send back, you could create this ``ApiProblem`` object,
which will help you structure things and avoid typos in any keys.

Right now inside of the ``ApiEntryPoint``, we're kind of creating the API
problem structure by hand, which is something I don't want to do. Let's leverage
our ``ApiProblem`` class instead.

So first, I'm closing a couple of these classes. Inside ``ApiProblem`` there
is a ``type`` property. The spec document that describes this format says
that we should have a ``type`` field and that it should be a unique string
for each type of error in your application. Right now we have two: ``validation_error``
as one unique thing that can go wrong and ``invalid_body_format`` as another.
That's if the client sends us json, but the json is malformed. Now we have
a third type of error, which is when you send us bad credentials. So let's
add a new constant here called ``authentication_error``. And I'm just making
up this string, it's not terribly important. And then down here is a map
from those types to a human readable response that will live on the ``title``
key.

The purpose of this is that when we create a new ``ApiProblem``, we are forced
to pass in a ``type`` and then that has a nice little map to the title. So
given a certain ``type``, you always get this nice same identical human readable
explanation for it. You don't have to duplicate the titles all around your
codebase. 

Back in ``ApiEntryPoint``, instead of this stuff, you can create a new ``ApiProblem``
object. Add our ``use`` statement for that. The status code we know is 401
and the ``type`` is going to be our new ``authentication_error`` type. So
it's a nice way to make sure we don't just invent new types all over the place.

And then,we set the ``detail``. The ``detail`` is going to be the message
that comes from Silex whenever something goes wrong related to security.
Based on what went wrong, we will get a different message here and we can
use the translator to control it.

Then down here for the response, we can say just ``new JsonResponse``. For
the content, we can say ``$problem->toArray()``. This is a function we used
earlier: it just takes all those properties and turns them into an array.
Now we'll use ``$problem->getStatusCode()``. And we'll keep the response
headers already set.

So this is a small improvement. I'm more consistent in my code, so my API
will be more consistent too. If I need to create an api problem response,
I won't do it by hand. The ``ApiProblem`` class does some special things
for us, attaching the title making sure we have a few defined types. If we
try this, we should get the same result as before and we do. Perfect. 

Let's go further! Even the creation of the response is error prone. So right
now, in both the ``Application`` class where we have our error handler *and*
inside of this ``ApiEntryPoint``, we create the ``JsonResponse`` and we set
the ``Content-Type`` header to ``application/problem+json`` by hand. I don't
want to have a lot of these laying around: I want to go through one central
spot.

The fix for this has nothing to do with Silex or API's: we're just going
to do a bit of refactoring and repeat ourselves a little bit less.

Lets create a new PHP class called ``APIProblemResponseFactory`` and it's
job will be to create API problem responses. So we'll create a single function
called ``createResponse`` and it will take in an ``ApiProblem`` object and
create the response for that. And most of this we can just copy from our
error handler code.

I'll make sure that I add a couple of ``use`` statements here. Perfect, so
it takes in the ``ApiProblem``, it transforms that into json, and it makes
sure that the ``Content-Type`` header is set. So if we can use this instead
of repeating that logic elsewhere, it is going to save us some trouble. Like
we saw before, inside of Silex there is a way to create global objects called
services. We did this for the ``erializer`` which meant that let us use it
in multiple places. So I'm going to do the same thing with the ``api.response_factory``.
And we'll just ``return new ApiProblemResponseFactory``. Of course, like
anything else don't forget to add the ``use`` statement for that. Yes, this
class is getting a little crazy. And that's it!

Down inside this class we'll use that key to access the objecta and make use
of it. I have that same app variable, so I can get rid of all this stuff
here. Pass it the  ``ApiProblem`` object to ``createResponse`` and that's
it!

We can do the same thing inside the ``ApiEntryPoint``. I need to practice
a little bit of dependency injection, and if this is a new idea to you
or going over your head, we have a free tutorial about `dependency injection`_.
I highly recommend you check it out, it is going to change the way you code. 

So in ``Application``, I'm going to find the entry point and I'm actually going
to go past that new factory object right to it as the second argument to
the ``_construct`` function of our ``ApiEntryPoint``. This means here I will
now have a second argument. Don't forget the ``use`` statement for that and
we'll just set that on a new property. So now, when this object is created
we're going to have access to this ``ApiResponseFactory``. Down below, we
can just use it. So we still create the ``ApiProblem`` object, but I don't
want to do any of this other stuff. And that's it! We just reduced duplication,
let's try our tests. Those pass too! Let's try all of our tests for the programmer.
Sahweet! They're passing too!

So there's no chance of duplication because everything is going through that
same class.
