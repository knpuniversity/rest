Token
=====

Our API clients can send a token with their request to become authenticated.
But now they need a way to actually create and manage these tokens!

Actually, this is already possible via the web interface. First, let me delete
our SQLite database, because the tests use that same databse and it deletes
my test user. Now, we can login as ryan@knplabs.com password ``foo``. If
you go to the url ``/tokens``, you see I have a little interface here. I can
add a token, put a message, click `tokenify-me` and there we go, I've got
a token!

And this is something we can use right now in an API request to authenticate as
the ``ryan@knplabs`` user. This is great and this might actually be enough for
you. But for me personally I also want a way to do this through my API. I want
to actually make a request through an endpoint that says "give me a new API
token."

So like always, let's start with creating a new Behat Scenario. In fact this
is a whole new feature! Create a new behat feature file and call it ``token.feature``
I'm going to start describing the business value for this. In this case it's
pretty easy: you need to get tokens so you can actually use our API. Perfect.

Next, let's put our first scenario here, which is going to be the working
scenario for creating a token. Even though a token relates to security it's,
really no different than any other resource we're creating, like a programmer
resource. So the scenario for this is going to look really similar. The only
difference is that we can't authenticate with an API token, because that's
what we are trying to get. So instead we are going to authenticate with HTTP
Basic. First, let's make sure there is a user in the database with a certain
password. And just like you saw with the web interface, every token has a
note describing how it's being used. So in our request body, we'll send JSON
with a little note. Great!

To send http basic headers with my request I have a built in line for this.
Then after that, we can make a request just like normal. I'm making up this
URL but you can see I'm being consistent because we also have ``/api/programmers``.

After this, it's just like our programmer endpoint: we know the status code
should be 201, that there should be a ``Location`` header and we'll expect
that the token resource is going to be returned to us and that it will have
a key called ``token``, which is the newly generated string. Awesome!

So let's try this: we know it's going to fail but we want to confirm that.
Perfect! And it does with a 404 because we don't actually have an endpoint
for this.

This is our second resource. We have a programmer resource, now we are going 
to handle a token resource. So I'm going to create an entirely new controller
class to handle this and I'm going to make it look a bit like my ``ProgrammerController``.
So it should extend the ``BaseController`` class which we've been adding
more helper methods into. Notice that I did just add a ``use`` statement
to that. And it expects us to have one method called ``addRoutes``. This
is special to my implementation of Silex, but you'll remember that we have
this at the top of ``ProgrammerController`` and that's just where we build
all of our endpoints. We can do the same things here. We'll add a new ``POST``
endpoint for ``/api/tokens`` that will execute a method called ``newAction``
when we hit it.

So go back, rerun the tests and it *is* working. The test still fails, but
instead of a 404, we see a 200 status code because we're returning ``foo``.
So let's do as little work as possible to get this working. The first thing
to know is that we *do* have a token table. And just like with our other tables
like the ``programmer`` table where we have a ``Programmer`` object, I've
also created an ``ApiToken`` object. If we can create this new ``ApiToken``
object, then we can use some ORM-magic I setup to save this to the database.

So let's start doing that: ``$token = new ApiToken();``. I'll add the ``use``
statement  for that. You can see immediately it's angry with me because I
need to pass the ``userId`` there. Now, what is the ``id`` of the current user?
Remember, in our scenario, we're passing http basic authentication. So here
we need to grab the HTTP Basic username and look that up in the database.
I'm not going to worry about checking the password yet, we'll do that in
a second. In my version of Silex, whenever you need request information you
can just type hint ``$request`` variable in your controller and it will
be passed in. Don't forget your ``use`` statement!

You may or may not remember this - I had to look it up - but if you want to get
the HTTP Basic username that's sent with the request, you can say ``$request->headers->get('PHP_AUTH_USER')``.
And don't forget your equals sign. Next I'll look this up in our user database.
For now we'll just assume it exists: I'm not going to do any error handling.
And then, we're going to say ``$user->id``. Perfect!

Next, we need to set the notes. In our scenario we're sending a JSON body
with the notes. So here, what we can do is just grab that from the request.
We did this before in Episode 1: ``$request->getContent()`` gets us the
raw JSON and ``json_decode`` will return an array. So, we'll get the notes
key off of that.

And that's really it! All we need to do now is save the token object, which
I'll do with my simple ORM system. Now, we need to return our normal API
response. Remember we're using the Serializer at this point and in the last
couple of chapters we created a nice new function in our ``BaseController``
called ``createApiResponse``. All we need to do is pass it the object we
want to serialize and the status code - 201 here - and that is going to build
and return the response for us. And that's it!

Let's try it out. Awesome! So it's failing because we don't have a ``Location``
header set, but if you look at what's being returned from the endpoint, can
tell it's actually working and inserting this in the database. We're missing
the ``Location`` header and we *should* have it, but for now I'm just going
to comment that line out. I don't want to take the time to build the endpoint
to view a single token. I'll let you handle that. Run the test, perfect it passes!

Since we're not checking to see if the password is valid, let's add another
scenario for that. We can copy most of the working scenario but we'll change
a couple of things.

So first, instead of the right password we'll send something different. And
instead of 201 this time it's going to be a 401. And remember whenever we
have an error response we are always keeping that same format. Great! So
let's run just this one scenario which starts on line 21. And again, we're
expecting it to fail, but I like to see my failures before I actually do
the code. Yes, failing!

And it's getting 201 because it's working, but it's expecting 401. So what
we need to do in our controller is check to see if the password is correct
for the user. But hey, let's not do that, it would be different in every
framework. But in my main ``Application`` class, where I configure my security,
I've already configured things to allow http basic to happen. By adding this
little key here, when the http basic username and password come into the
request, the Silex security system will automatically look up the user object
and deny access if they have the wrong password. It's kind of like our API
token system, but instead of sending a token it's going to be reading it off
of the http basic username and password headers. So it's really handy.

That means that inside of here, if we need the actual user object you don't
need to query for it directly. The security system already did that for us.
We can just say ``$this->getLoggedInUser()``. We don't really know if the
user logged in via HTTP basic or passed a token, and frankly we don't really
care. And since we need our user to be logged in, we can use our nice 
``$this->enforceUserSecurity()`` step right there. Perfect, let's try that
out.

And it passes with almost no effort! 

So that's really it for creating the token resource. But I do want to do a
couple of other cleanup things. First thing: whenever we need to read information
off of the request that the client is sending us, we're going to use this same
``json_decode`` of the request content. In fact, we use this inside of
``ProgrammerController`` inside of the ``handleRequest`` method. So we have
the same logic duplicated there. Let's centralize this by putting it into
our ``BaseController``. Open this up and create a new protected function
at the bottom called ``decodeRequestBodyIntoParameters``. I know, really
short name. And we'll take in ``Request`` object as an argument. And at first
this is going to be really simple. We can go to the ``TokenController``,
just grab this ``json_decode`` line, go back to ``BaseController`` and return
it. So now in the ``TokenController``,  ``$data = $this->decodeRequestBodyIntoParameters($request)``
and we've got it. 

So just to be sure, let's go back and run our entire feature. Everything still 
passes so we're good. So, why did we do this? Back in ``ProgrammerController``, 
when we decoded the body there in ``handleRequest``, we actually also checked to
see if maybe the json that was sent to us had a bad format. If the JSON *is*
bad, then ``json_decode`` is going to return ``null`` which is what we are
checking for here.

So let's move that into our new ``BaseController`` method, because that is
a really nice check. And then it's creating an ``ApiProblem`` and throwing
an ``ApiProblemException`` so we can have that really nice consistent format.
But we just need to add the ``use`` statements for both of these. Perfect.
So let's rerun these again to make sure things are happy and they are!

One other little detail here is that if the request body is blank this is
going to blow up with an invalid request body format because ``json_decode``
is going to return ``null``. Now technically sending a blank request is not
invalid json so I  don't want to blow up in that way. This doesn't affect
anything now but it's planning for the future. So if not ``!$request->getContent()``,
then just set data to an array. Else, we'll do all of our logic down here
that actually decodes the json. And just to make sure we didn't screw anything
up, we'll rerun the tests.

And one last little thing that is going to make our code even easier to deal with.
Back in ``TokenController``, because the ``decodeRequestBodyIntoParameters``
returns an array, we need to code a bit more defensively here. Because what
if they don't actually send a ``notes`` key, we don't want some sort of PHP
error.

And that's not that big of a deal but it's kind of annoying and error prone.
So instead, in our new function I want to return a different type of object
called a ``ParameterBag``. This comes from a component inside of Symfony
that Silex uses. It's an object but it acts just like an array with some
extra nice methods. Let me show you what I mean, back in ``TokenController``
instead of using it like an array we can now say ``$data->get()`` and if
that key doesn't exist it's not going to throw some bad index warning. We
can also use the second argument as the default value. Nice, so once again
let's rerun the tests and everything is happy!

So we have this really nice new function inside of our ``BaseController`` and 
I want to take advantage of it also inside of our ``ProgrammerController``.
So I'll go down to ``handleRequest`` where we are doing this and now we can
just say ``$data = $this->decodeRequestBodyIntoParameters()`` pass the ``$request``
object. Next, this big ``if`` block is no longer needed and now because data
is an object instead of an array, we need to update our two or three usages
of it down here, which is going to make things a lot simpler. So instead of
using the ``isset`` function, we can say ``if(!$data->has($property))`` because
that's one of the methods on that ``ParameterBag`` object. And down here
instead of having to code defensively using the ``isset``, we can just say
``$data->get($property)``. In fact let's just do this all in one line. Perfect!
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
of the working version. Hhere, we won't actually pass any request body. Fortunately
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
