API Token Authentication
========================

Let's talk about API Authentication. The web part of our site *does* have
authentication. I can login as ``ryan@knplabs.com`` password ``foo``. By the way,
if that ever doesn't work for you, go back and just delete the SQLight database
we're using:

.. code-block:: bash

    rm data/code_battles.sqlite

This is because when we run our Behat tests, it messes with our database.
In a real project, we'd want to fix that - it's annoying!

When we're logged in as Ryan, any programmer we create is attached to the Ryan
user in the database. The web side has authentication and we know who which
programmers.

In our API, that's not the case at all: we have no authentication. And so
we're kind of faking the attachment of a programmer to a user. In ``newAction``
we call this function ``handleRequest``,  which lives right in this same class.
Then down here on the bottom, you can see *how* we fake it::

    // todo

We assume there's a user called ``weaverryan`` in the database, grab it's
``id`` and attach that to every programmer. So obviously that's just hardcoded
silliness and we need to get rid of it! We need some way for the API request
to authenticate itself as a user.

Tokens
------

We're going to do this is with tokens. You can create your own simple token
system or you can do something bigger like OAuth. The important thing to
realize is that the end results are the same: the request will send a token
and you'll look that up in some database and figure out which user the token
belongs to and if they have access. Using OAuth is just a different way to
help you handle those tokens and get those tokens to your users.

Before we start anything let's write a new Behat feature file, because we haven't
done anything with authentication yet. For Behat newbiews, this will feel
weird, but stick with me. I'm going to describe here the business value of
our authentication feature and who benefits from it, which is the API client.
That part is not important technically, but it's a good way to clarify why
you're building this authentication feature. 

Below that, let's add our first scenario, which is going to be one where we
try to create a programmer without sending a token. Ultimately we want to
require authentication on most endpoints.

If I just request ``/post/api/programmers`` with no token, the response I
get should be 401. We don't know what the response is going to look like
yet, but let's imagine that it's JSON and has some ``detail`` key on it which
gives us a little bit of information about what went wrong. 

So there we go! No coding yet, but we know how things should look if we don't
send a token. If we try this right now - which is always a good idea - we
should see it fail. By the way we can run one feature file at a time, which
is going to be really useful for us. And there we go! You can see it's giving
us a 400 error because we're sending invalid JSON instead of the 401 we want.

So let's go into ``ProgrammerController`` and fix this as easily as possible
up in ``newAction``. I already have a function called ``getLoggedinUser()``,
which will either give us a ``User`` object or give us ``null`` if the request
isn't authenticated. What we can do is check if ``!getLoggedInUser()`` then
return the access denied page. In Silex, you do this by throwing a special
exception called ``AccessDeniedException``. Make sure you have the right
``use`` statement for this, which is in the ``Security`` component. And there
we go! Since that should deny access, let's try our test. 

Boom! And this time you can see that we *are* getting past the 401 test.
So it *is* returning a 401 authentication required response. Sweet! The only
difference is that the response body is coming back slightly different than
we expected. There *is* a ``detail`` property, but instead of it being set
to "authentication required", it's set to this ``not privileged to request the resource``
string. And yea, it's not really obvious where that's coming from.

But first, how is this even working behind the scenes? If you were with us
for episode 1, we did some pretty advanced error handling stuff at the bottom
of this ``Application`` class. What we basically did was add a function that's
called anytime there's an exception thrown *anywhere*. Then, we transform
that into a consistent JSON response that follows the `API problem`_ format.
So any exception gives us a nice consistent response.

And hey, when we deny access, *we're throwing an exception*! But bad news,
our exception is the *one* weird guy in the *whole* system: instead of
being handled here, it's handled somewhere else entirely.

Without getting too far into things, I've already written *most* of the logic
for our token authentication system, and I'll show you the important parts
but not cover how this all hoosk up. If you have more detailed questions,
just ask them on the comments.

Let's open up a class in my ``Security/Authentication`` directory called
``ApiEntryPoint``. When we deny access, the ``start()`` method is called.
Here, it's our job to return a Response that says "hey, get outta here!".
So *that's* where the ``detail`` key is coming from, and it's set to some
internal message that comes from the deep dark core of Silex's security that
describes what went wrong.

Fortunately, I'm also translating that message, and we setup translations
in episode 1. in this ``translations/`` directory. This is just a little
key value pair. So let's copy that unhelpful "this not privileged request"
thing that Silex's gives us and translate it to something nicer.

Ok, let's rerun the tests, and there we go!

So we can deny access and turn that into a nice response. Cool. Now we need
to create something that'll look for a token on the request and authenticate
us so we can actually create programmers!

You should know where to start: in one of our feature files. We're going
to modify an existing scenario. See the ``Background`` of our programmer
feature file? One of the things that we do before every single scenario is
to make sure the ``weaverryan`` user exists in the database. We aren't sending
authentication headers, just guaranteeing that the user exists in the database.

In the background, I already have a database table for tokens, and each token
has a foreign-key relation to one user. So I'm going to extend the ``Background``
a little bit to create a token in that table that relates to ``weaverryan``.
And this is the important part, this says that on whatever request we make
inside of our scenario, I want to send an ``Authorization`` header set to
be the token, a space then ``ABCD123``.

Why did I choose to set the ``Authorization`` header or this "token space"
format? Technically, none of this is important. In a second, you'll see us
grab and parse this header. If you use OAuth, it has directions on the type
of names you should give these things. So I’m just using authorization header
and the word token, space and the actual authentication token that we're sending.

By the way, we aren't doing it in this tutorial, but one thing that that's
really important for authentication across your API is that you only do it
over HTTPS. The easiest thing to do is to require all your users connect to
your api over HTTPS. Otherwise, these authentication tokens are going over
the internet via plain text, and that's a recipe for disaster.

If we rerun one of our tests right now, it's not going to make any difference. 
To prove it, let's rerun the first scenario of ``programmer.feature``, which
starts at line 11. So we say ``:11`` and it's going to fail. It *is* setting
that ``Authorization`` header, but we aren't actually doing anything with
it yet in our app. So we're getting that 401 authentication required message.

So let's hook this up! Some of this is specific to Silex's security system,
but in case you're using something else, we'll stay high level enough to see
what types of things you need to do in your system to make it happen. If
you have questions, let me know in the comments.

Inside this security directory here, I've already set up a bunch of things
for an API token authentication system. The first thing we're going to do
is open this ``ApiTokenListener``. I've written some fake code in here as
you can see. The job of the listener is to look at the request object and
find the token information off of it. And hey, since we're sending the token
on the ``Authorization`` header, we are going to look for it there. So let's
get rid of this hard coded text and instead we are going to go get that
``Authentication`` header. You can say ``$request->header->get('Authorization')``.
That's going to get you that actual raw ``token ABCD123`` type of thing. 

Next, since the actual token is the second part of that, we need to parse
it out. I'll say, ``$tokenString = $this->parseAuthorizationHeader``, which
is a function I’ve already created down here. It's a private function that
expects a format of token space and gets the second part for you. Perfect!

At this point the ``$tokenString`` is ``ABCD123``. So that's all I want to
talk about this ``TokenListener``, it's the only job of this class.

Next, I’m going to open up the ``ApiTokenProvider``, it's job is to take
the token string ``ABCD1234`` and try to look up a valid ``User`` object
in the database for it. First, remember how I have an ``api_token`` table in
my database? Let me show you some of the behind-the-scenes magic. You can
see here I am creating an ``api_token`` table. It has a token column which
is the string and a ``user_id`` column which is the user it relates to. So
you can imagine a big table full of tokens and each token is related to exactly
one user. So if we look up the entry in the token table, we can figure out
"yes" this is a valid token and it is a valid token for a user whose ``id``
is ``5``, for example.

So here, the first thing we'll do is actually go and look up the token row.
I don't want to get into the details of exactly how this all hooks up because
I want to focus on REST. But I've already configured this class and created
some code behind the scenes to take in a token string, which is the ``ABCD123``
thing in our case and return to me an ``ApiToken`` object, which represents
a row in that table. So we've taken the string and we've queried for a row
in the table. If we don't have that row, we throw an exception which causes
a 401 bad credentials error.

Next, when we have that, we just need to look up the ``User`` object from it.
Remember, the job of this class is start with the token string and eventually
give us a ``User`` object. And it does that by going through the ``api_token``
table. And that's the job of this ``ApiTokenProvider`` class. It's technical
and at the core of Silex's security system, so I just want you to internalize
that that is what it does.

At this point - between these two classes and a few other things I've setup -
if we send this ``Authorization`` header with a valid token, by the time we
get it to our ``ProgrammerController``, ``$this->getLoggedInUser()`` will
actually return to us the ``User`` object that's attached to the token that
was sent. In the case of our scenario, since we're sending a token of ``ABCD1234``,
it means that we'll get a ``User`` object that represents this ``weaverryan``
user. We will actually be logged in, except we are logged in via the api
token. So, let's try this out. And there it is!

The guts for getting this all working can be complicated, but the end result
is so simple: send an ``Authorization`` header with the api token and use
that to look in your database and figure out which ``User`` object if any
is this token attached to.

So now, in ``handleRequest()``, I have this ugly hard-coded logic that assumed
that there is a user called ``weaverryan``. Now we can replace this garbage
with ``$this->getLoggedinUser()`` to get the real user object of whoever
we happen to be logged in as.

Hey! We have this great system where users are actually being authenticated!
No we can start checking for security everywhere we need it. In ``newAction``
we're requiring that you are logged in. Awesome! In ``showAction`` and ``listAction``
we are going to leave those anonymous: in our API it's ok to read data as
an anonymous user. In ``updateAction``, we *do* need some extra security.
It's more than just being logged in: we need to check to see if our user
is actually the owner of that programmer or not. So we just add some ``if``
statement logic: ``if ($programmer->userId != $this->getLoggedInDser()->id``,
then ``throw new AccessDeniedException``.

Easy enough!

Since we're also going to use this in ``deleteAction`` let's go into our
``BaseController`` and actually make this a generic function. Open up the
``BaseController``, create a new protected function ``enforceProgrammerOwnershipSecurity``.
Let's copy the logic in there and don't forget to add your ``AccessDeniedException``
``use`` statement.

Boom! Perfect, so now go back to our ``ProgrammerController``. It's a lot
easier to just reuse this logic. Let's also use this down in ``deleteAction``.
Perfect, now the only other thing that could go wrong, is if the user is
not logged in at all and they hit ``updateAction``. Then, we are actually
going to die inside this function. The problem is that if we're not actually
logged in at all, ``$this->getLoggedInUser`` is going to be null and we'll
call the ``id`` property on a null object. So before we call this function,
we need to make sure the user is at least logged. If they aren't, then they
are definitely not the owner of this programmer.

So let's create another function here called ``enforceUserSecurity``. In
this case, go back to ``ProgrammerController`` and we can grab the logic
right here. There we go. And from inside ``enforceProgrammerOwnershipSecurity``
we can just make sure that the user is actually logged in. And in ``ProgrammerController``,
we can actually do the same thing and save ourselves a little bit of code.
Between these two new functions, we have a really easy way to go function
by function inside of our controller make sure that we're enforcing the right
type of security.

Because we are sending our authentication headers in the background of our
scenarios we should be able to run our entire ``programmer.feature`` and
it passes. Perfect! And just like that, we have our entire application locked
down.

This is great. The only question now is, in the real world, if I'm an API
client, how do I get a token so I can access the API? Right now we use a
little bit of magic I created in the ``Background``, but obviously that's
not going to work for real API users.
