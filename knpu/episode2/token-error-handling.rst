Token Error Handling
====================

Since we're not checking to see if the password is valid, let's add another
scenario for that. We can copy most of the working scenario but we'll change
a couple of things.

Instead of the right password we'll send something different. And instead 
of 201 this time it's going to be a 401.Remember whenever we have an error 
response we are always returning that API Problem format. Great! So let's 
run just this one scenario which starts on line 21. And again, we're
expecting it to fail, but I like to see my failures before I actually do
the code. Yes, failing!

In our controller we need to check to see if the password is correct
for the user. But hey, let's not do that, Silex can help us with some of this
straightforward logic. In my main ``Application`` class, where I configure my security,
I've already setup things to allow http basic to happen. By adding this
little key here, when the http basic username and password come into the
request, the Silex security system will automatically look up the user object
and deny access if they have the wrong password. It's kind of like our API
token system, but instead of sending a token it's going to be reading it off
of the http basic username and password headers. That's pretty handy.

That means that in the controller, if we need the actual user object we don't
need to query for it - the security system already did that for us.
We can just say ``$this->getLoggedInUser()``. We don't really know if the
user logged in via HTTP basic or passed a token, and frankly we don't
care. And since we need our user to be logged in for this endpoint, we can use our nice 
``$this->enforceUserSecurity()`` function. Perfect, let's try that out.

And it passes with almost no effort!

So that's really it for creating the token resource. But I do want to do a
couple of other cleanup things. First: whenever we need to read information
off of the request that the client is sending us, we're going to use this same
``json_decode`` for the content. In fact, we use this in
``ProgrammerController`` inside of the ``handleRequest`` method. So we have
the same logic duplicated there. Let's centralize this by putting it into
our ``BaseController``. Open this up and create a new protected function
at the bottom called ``decodeRequestBodyIntoParameters``. I know, really
short name. And we'll take in a ``Request`` object as an argument. And at first
this is going to be really simple. We can go to the ``TokenController``,
grab this ``json_decode`` line, go back to ``BaseController`` and return
it. So now in the ``TokenController``,  ``$data = $this->decodeRequestBodyIntoParameters($request)``
and we've got it. 

So just to be sure, let's go back and run our entire feature. Everything still 
passes so we're good. So, why did we do this? Back in ``ProgrammerController``, 
when we decoded the body in ``handleRequest``, we also checked to see if maybe 
the json that was sent to us had a bad format. If the JSON *is* bad, then 
``json_decode`` is going to return ``null`` which is what we're checking for here.

So let's move that into our new ``BaseController`` method, because that is
a really nice check. And then it's creating an ``ApiProblem`` and throwing
an ``ApiProblemException`` so we can have that really nice consistent format.
We just need to add the ``use`` statements for both of these. Perfect.
Let's rerun those again to make sure things are happy ... and they are!

One other little detail here is that if the request body is blank this is
going to blow up with an invalid request body format because ``json_decode``
is going to return ``null``. Now technically sending a blank request is not
invalid json so I  don't want to blow up in that way. This doesn't affect
anything now but it's planning for the future. So if ``!$request->getContent()``,
then just set $data to an array. Else, we'll do all of our logic down here
that actually decodes the json. And just to make sure we didn't screw anything
up, we'll rerun the tests.

One last little thing that is going to make our code even easier to deal with.
Back in ``TokenController``, because the ``decodeRequestBodyIntoParameters``
returns an array, we need to code a bit more defensively here. What if they 
don't actually send a ``notes`` key, we don't want some sort of PHP error.
The horror!

And that's not that big of a deal but it's kind of annoying and error prone.
So instead, in our new function I want to return a different type of object
called a ``ParameterBag``. This comes from a component inside of Symfony
that Silex uses. It's an object but it acts just like an array with some
extra nice methods. Let me show you what I mean, back in ``TokenController``
instead of using it like an array we can now say ``$data->get()`` and if
that key doesn't exist it's not going to throw some bad index warning. We
can also use the second argument as the default value. Nice, so once again
let's rerun the tests and everything's happy!

We have this really nice new function inside of our ``BaseController`` and 
I also want to take advantage of it inside of our ``ProgrammerController``.
We're going down to ``handleRequest`` and now we can just say 
``$data = $this->decodeRequestBodyIntoParameters()`` and pass the ``$request``
object. Next, this big ``if`` block is no longer needed. And now because data
is an object instead of an array, we need to update our two or three usages
of it down here, which is going to make things simpler. So instead of
using the ``isset`` function, we can say ``if(!$data->has($property))`` because
that's one of the methods on that ``ParameterBag`` object. And down here
instead of having to code defensively using ``isset``, we can just say
``$data->get($property)``. In fact let's just do this all in one line. Lovely!
Now that was a fairly fundamental change so there is a good chance that we
broke something.

So let's go back and run our entire programmer feature. Beautiful! We didn't
actually break anything which is so good to know. So finally, let's add a
little bit of validation to our token. This should start to feel easy because
we did all of this before with our programmer. Now let me remind you in the
``PogrammerController``, to validate things we call this validate function,
which is something I created for this project before we even started. But
the way the validation works is that on our ``Programmer``, class we have
these ``@Assert`` things. So when we call the ``validate()`` function on
our controller, it reads this and makes sure the nickname isn't blank. It's
as simple as that!

Now if that ``validate()`` function returns an array that actually has some
errors in it, then we call this ``throwApiProblemValidationException`` function,
which is something that we created inside this controller. You can see it's
further down inside this same file. What does it do? No surprises, it creates
a new ``ApiProblem`` object, sets the errors as a nice property on it then
throws a ``new ApiProblemException``. We can see this if we look inside the
``programmer.feature`` class. I'll search for 400 because validation errors
return a 400 status code. You can see this is an example of us testing our
validation situation. We're checking to see that's there are ``nickname``
and ``avatarNumber`` properties on ``errors``. 

So the ``ApiToken`` class also has one of these not blank things on it. So
all we need to do in our controller is call these same methods. So first,
let's move that ``throwApiProblemValidationException`` into our ``BaseController``,
because that's going to be really handy. And of course we'll make it protected
so we can use it in the sub classes. Perfect!

Next, let's steal a little bit of code from our ``ProgrammerController`` and
put that into our ``TokenController``. So once we're done updating our token
object, we'll just call the same function, pass it the token instead of the
programmer and throw that same error. Perfect, so this actually should all
be setup. Of course what I forgot to do was write the scenario first, so shame
on me~ Let's write the  scenario to make sure this is working. I'll copy most
of the working version. Here, we won't actually pass any request body. Fortunately
we've made our decode function able to handle that. We know the status code
is going to be 400. We can check to see that the ``errors.notes`` property
will equal the message that is actually on the ``ApiToken`` class. It will
be this message right here. 

Perfect!

This starts on line 33, so let's run just this scenario. Oh no, and it actually
passes! Instead of the 400 we want, it is giving us the 201, which means
that things are not failing validation. You can actually see for the note
it says ``default note``. If you look back in our ``TokenController``...Ah
ha! It's because I forgot to take off the default value. So now it's either
going to be set to whatever the note is or ``null``. And if it's set to ``null``
we should see our validation kick in. And we do, perfect!
