Authorization via a Token
=========================

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
token, a space then ``ABCD123``.

Why did I choose to set the ``Authorization`` header or this "token space"
format? Technically, none of this is important. In a second, you'll see us
grab and parse this header. If you use OAuth, it has directions on the type
of names you should give these things. So I’m just using authorization header
and the word token, space and the actual authentication token that we're sending.

By the way, we aren't doing it in this tutorial, but one thing that that's
really important for authentication across your API is that you only do it
over HTTPS. The easiest way to do this is to require HTTPS across your entire 
API.  Otherwise, these authentication tokens are flying through the internet via 
plain text, and that's a recipe for disaster.

If we rerun one of our tests right now, it's not going to make any difference. 
To prove it, let's rerun the first scenario of ``programmer.feature``, which
starts at line 11. So we say ``:11`` and it's going to fail. It *is* setting
that ``Authorization`` header, but we aren't actually doing anything with
it yet in our app. So we're getting that 401 authentication required message.

So let's hook this up! Some of this is specific to Silex's security system,
but in case you're using something else, we'll stay high level enough to see
what types of things you need to do in your system to make it happen. As always,
if you have questions, just ask them in the comments!

Inside this security directory here, I've already set up a bunch of things
for an API token authentication system. The first thing we're going to do
is open this ``ApiTokenListener``. I've written some fake code in here as
you can see. The job of the listener is to look at the request object and
find the token information off of it. And hey, since we're sending the token
on the ``Authorization`` header, we are going to look for it there. So let's
get rid of this hard coded text and instead go get that ``Authorization`` header. 
You can say ``$request->headers->get('Authorization')``. That's going to get you 
the actual raw ``token ABCD123`` type of thing. 

Next, since the actual token is the second part of that, we need to parse
it out. I'll say, ``$tokenString = $this->parseAuthorizationHeader``, which
is a function I’ve already created down here. It's a private function that
expects a format of token space and gets the second part for you. Perfect!

At this point the ``$tokenString`` is ``ABCD123``. So that's all I want to
talk about in this ``TokenListener``, it's the only job of this class.

Next, I’m going to open up the ``ApiTokenProvider``, it's job is to take
the token string ``ABCD123`` and try to look up a valid ``User`` object
in the database for it. First, remember how I have an ``api_token`` table in
my database? Let me show you some of the behind-the-scenes magic. You can
see here I am creating an ``api_token`` table. It has a token column which
is the string and a ``user_id`` column which is the user it relates to. So
you can imagine a big table full of tokens and each token is related to exactly
one user. For example, if we look up the entry in the token table, we can figure out
"yes" this is a valid token and it is a valid token for a user whose ``id``
is ``5``.

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
what it does.

At this point - between these two classes and a few other things I've setup -
if we send this ``Authorization`` header with a valid token, by the time we
get it to our ``ProgrammerController``, ``$this->getLoggedInUser()`` will
actually return to us the ``User`` object that's attached to the token that
was sent. In the case of our scenario, since we're sending a token of ``ABCD123``,
it means that we'll get a ``User`` object that represents this ``weaverryan``
user. We will actually be logged in, except we're logged in via the API
token. So, let's try this out. And there it is!

The guts for getting this all working can be complicated, but the end result
is so simple: send an ``Authorization`` header with the api token and use
that to look in your database and figure out which ``User`` object if any
this token is attached to.

So now, in ``handleRequest()``, I have this ugly hard-coded logic that assumed
that there is a user called ``weaverryan``. Replace this garbage
with ``$this->getLoggedinUser()`` to get the real user object that's 
attached to our token.
