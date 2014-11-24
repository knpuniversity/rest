Token
=====

Our API clients can send a token with their request to become authenticated.
But how are they suppposed to get that token in the first place?

Actually, this is already possible via the web interface. First, let me delete
our SQLite database, which will reset our users. Now, we can login as 
ryan@knplabs.com password ``foo``. If you go to the url ``/tokens``, you 
see I have a little interface here. I can add a token, put a message, click 
`tokenify-me` and there we go, I've got a shiny new token!

And this is something we can use right now in an API request to authenticate as
the ``ryan@knplabs`` user. This is great and might actually be enough for
you. But for me, I also want a way to do this through my API. I want
to make a request through an endpoint that says "give me a new API
token."

As always, let's start with creating a new Behat Scenario. In fact this
is a whole new feature! Create a new behat feature file and call it ``token.feature``
Let me get my necktie on here and start describing the business value for 
this. In this case it's pretty easy: you need to get tokens so you can 
use our API. Perfect.

Next, let's put our first scenario here, which is going to be the working
scenario for creating a token. Even though a token relates to security it's,
really no different than any other resource we're creating, like a programmer
resource. So the scenario for this is going to look really similar. The only
difference is that we can't authenticate with an API token, because that's
what we are trying to get. So instead we're going to authenticate with HTTP
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
Failure, sweet! And it does with a 404 because we don'te have an endpoint
for this.

To get this working, I'm going to create an entirely new controller
class and make it look a bit like my ``ProgrammerController``.
Make it extend the ``BaseController`` class which we've been adding
more helper methods into. Notice that I did just add a ``use`` statement
for that. And it expects us to have one method called ``addRoutes``. This
is special to my implementation of Silex, but you'll remember that we have
this at the top of ``ProgrammerController`` and that's just where we build
all of our endpoints. We can do the same things here. We'll add a new ``POST``
endpoint for ``/api/tokens`` that will execute a method called ``newAction``
when we hit it.

So let's go back and rerun the tests. Look at that, it *is* working. The 
test still fails, but instead of a 404, we see a 200 status code because 
we're returning ``foo``. So let's do as little work as possible to get this 
going. The first thing to know is that we *do* have a token table. And just 
like with our other tables like the ``programmer`` table where we have a 
``Programmer`` class, I've also created an ``ApiToken`` class. If we can 
create this new ``ApiToken`` object, then we can use some ORM-magic I setup 
to save a new row to that table.

So let's start doing that: ``$token = new ApiToken();``. I'll add the ``use``
statement  for that. You can see immediately it's angry with me because I
need to pass the ``userId`` there. Now, what is the ``id`` of the current user?
Remember, in our scenario, we're passing http basic authentication. So here
we need to grab the HTTP Basic username and look that up in the database.
I'm not going to worry about checking the password yet, we'll do that in
a second. In Silex, whenever you need request information you
can just type hint a ``$request`` variable in your controller and it will
be passed in. Am I sounding like a broken record yet? Don't forget your 
``use`` statement!

You may or may not remember this - I had to look it up - but if you want to get
the HTTP Basic username that's sent with the request, you can say 
``$request->headers->get('PHP_AUTH_USER')``. Oops don't forget your equals sign. 
Next I'll look this up in our user table. For now we'll just assume it exists: 
I'm living on the edge by not doing any error handling. And then, we're going to 
say ``$user->id``. Perfect!

Next, we need to set the notes. In our scenario we're sending a JSON body
with a notes field. So here, what we can do is just grab that from the request.
We did this before in Episode 1: ``$request->getContent()`` gets us the
raw JSON and ``json_decode`` will return an array. So, we'll get the notes
key off of that.

And that's really it! All we need to do now is save the token object, which
I'll do with my simple ORM system. Now, we need to return our normal API
response. Remember we're using the Serializer at this point and in the last
couple of chapters we created a nice new function in our ``BaseController``
called ``createApiResponse``. All we need to do is pass it the object we
want to serialize and the status code - 201 here - and that's going to build
and return the response for us. That's as simple as Jean-Luc Picard sending
the Enterprise into warp! Engage.

Head over to the terminal. Awesome...ish! So it's failing because we don't 
have a ``Location`` header set, but if you look at what's being returned from 
the endpoint, you can tell it's actually working and inserting this in the 
database. We're missing the ``Location`` header and we *should* have it, but 
for now I'm just going to comment that line out. I don't want to take the time
to build the endpoint to view a single token. I'll let you handle that. Let's the 
test, perfect it passes!
