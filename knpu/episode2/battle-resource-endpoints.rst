Battle Resource Endpoints
=========================

Let's login to the site. But remember, our tests like to mess with our database,
so I'm going to delete the SQLite database file and it'll regenerate with
some nice test data.

So I'll login as ``ryan@knplabs.com`` password ``foo``.

We already know that I'm able to create a programmer. And we even have some
really nice API endpoints for this. The other part of the site is all about
battles. If I click "Start Battle", this is a list of project that are in
the database right now. If I select one of those project, it starts a *Battle*
between the programmer and the project and picks a winner.

A battle is another type of resource, but it can't be created yet in the
API. Let's fix that!

Like my other resources, I already have a class that models this. You can
see there's a programmer, a project, the outcome ``didProgrammerWin`` and
it even stores the date it was fought at and some extra notes.

Let's make the endpoint to create new battles. We're going to start like always
by creating a new feature - ``battle.feature``. This is the Behat format where
we specify the business value. The API clients are going to want to create
battles to see if their programmers can take on these projects and defeat
them. The next line is the person that's benefiting from the new feature
and finally we have a little description.

The Feature isn't important for testing, it's just a good practice to remember
*why* we're building this feature. Let's create the first Scenario: Creating
a new Battle. If we go back to ``programmer.featuer``, we can copy a lot 
of this. First, in order to create a battle, we're probably going to need
to be authenticated. So, I'll copy this background. I'm even going to go
back and copy and start by copying all of the scenario for creating a programmer.
After all, this is an API, so creating a resource should always look pretty
much the same.

Let's work from the end backwards and think about how we want the Response
to look. We know there's going to be a Location header, because there's always
a Location header after creating a resource. But we don't know what URL that's
going to be yet, because we don't have an endpoint yet for viewing a single
battle. So we'll just say that the Location header should exist. And if you
look at the Battle class, you'll see there's a ``didProgrammerWin`` property.
So let's just make sure that exists as well - we don't know if it's going
to be true or false, because there's some randomness. Let's update the URL
to ``/api/battles`` and the status code of 201 looks perfect.

In order to create a Battle - we'll need to send a programmer and a project.
And probably the way we'll want the client to do that is by sending the programmer's
id and the project's. So let's send programmerId and projectId - but we don't
know yet what these should be set to.

Next, in order for us to start a battle, there needs to already be a programmer
and a battle sitting in the database. So before this line, we'll need to
say "Given there is a programmer called", and we'll create a new programmer
called Fred. Again, these are all built-in Behat definitions that I created
before we started working and they all live in either ApiFeatureContext or
ProjectContext. If you want to know what I'm doing behind the scenes, just
open up those classes. There's another one for "And there is a project called",
and we'll say "my_project".

Here's the problem: we don't know what the id's are of the programmer and
project we just created. So I don't really know what to put in the request
body - we really want whatever id those new things have. This is a really
difficult problem with testing API's. So one of the things I've put into
my testing system already, is the ability to do things like this. It's a special
syntax. And what this will do is go find a programmer whose nickname is "Fred"
and give us its id. It'll create a query for that dynamically. This syntax
is totally special - it's not something built into Behat. If you want to know
how it works, open ApiFeatureContext and scroll all the way to the bottom
and find the processReplacements function. It parses out that "%" syntax,
looks for these wildcards, and lets us do some of that magic. This will be
really handy, and we'll use it a few more times.

We'll do the same thing for projects. So this looks great! You know I like
watching my tests fail first, so let's try our test. And we'll just run this
new ``battle.feature`` file. So instead of 201, we get the 404 because the
endpoint doesn't exist. That's awesome.

So next, let's fill this in. And what's cool is that because we've done so
much work up to this point, it's going to start getting really easy. I'll
copy ``TokenController`` - I like having one resource per Controller. Update
the class name. And this already has some code we want. So let's change the
URL to ``/api/battles``.

In ``newAction``, we're going to reuse a lot of this. To create a Battle,
you *do* need to be logged in, so we'll keep the enforceUserSecurity. This
``decodeRequestBodyIntoParameters`` is what actually goes out and reads the
JSON on the request and gives us back this array-like object called a ParameterBag.
So that's all good too.

I *am* going to remove most of the rest of the things, because they're specific
to creating a token. What we need to do is read the programmer and project
id's off of the request and then create and save a new Battle object based
off of those. So first, let's go get the ``projectId``. We're able to use
this nice ``get`` function, because the ``decodeRequestBodyIntoParameters``
function gives us that nice ParametersBag object. Let's also get the programmerId.
Perfect!

And with these 2 things, I need to query for the Project and Programmer objects,
because the way I'm going to create the Battle will need the objects, not
just their ids. Plus, this will tell us if these ids are even real. I'll use
one of my project's shortcuts to query for the Project. All this is doing
is going out and finding the Project with that id and returning the Project
model object that has the data from that row. We'll do the same things with
Programmer.

Normally, to create a battle, you'd expect me to actually instantiate it
manually. We've done that before for Tokens and Programmers. But for Battles,
I have a shortcut way, which is via a class called a BattleManager, which
is a pretty awesome name. So instead of creating the Battle by hand, we'll
call this ``battle()`` function and pass it the Programmer and Project. It
takes care of all of the details of creating the Battle, figuring out who
won, setting the foughtAt time, adding some notes and saving all of this.
So we *do* need to create a Battle object, but this will do it for us.

Back in BattleController, I already have a shortcut method setup to give
us the BattleManager object. Then we'll use the battle() function we just
saw and pass it the Programmer and Project. And that's it - the Battle is
created and saved for us. Now all we need to do is pass the Battle to the
``createApiResponse`` method. And that's literally it.

Remember that the createApiResponse method does a couple of things. One thing
it does is to use the serializer object to turn the Battle object into JSON.
We haven't done any configuration on this class for the Serializer, which
means that it's serializing all of the fields. And for now, I'm happy with
that - we're getting free functionality. 

This looks good to me - so let's try it! Oh! It *almost* passes. It gets
the 201 status status code, but it's missing the ``Location`` header. In
the response, we can actually see the Battle, with notes on why our programmer
lost.

Back in ``newAction``, we can just set createApiResponse to a variable and
then call ``$response->headers->set()`` and pass it ``Location`` and a temporary
``todo``. Remember, this is the location to view a single battle, and we
don't have an endpoint for that yet. But this will get our tests to pass
for now. Perfect!

So let's add some validation. Since the ``battle()`` function is doing all
of the work of creating the Battle, we don't need to worry about too much.
We just need to make sure that ``projectId`` and ``programmerId`` are valid.
I'll just do validation manually here by creating an ``$errors`` variable
and then check to see if we didn't find a ``Project`` in the database for
some reason. If that's the case, let's add an error with a nice message.
And we'll do the same thing with the ``Programmer``.

Finally at the bottom, if we actually have at least one thing in the ``$errors``
variable, we're going to call a nice method we made in a previous chapter
called ``throwApiProblemValidationException`` and just pass it the array
of errors. It's just that easy. We don't have a scenario setup for this, so
let's tweak our scenario temporarily to try it - ``foobar`` is definitely
not a valid id.

Now, we can see that the response code is 400 and we have this really-nicely
structured error response. So that's why we want to *all* of that work of
setting up our error handling correct, because the rest of our API is so
easy and so consistent.

Let's change the scenario back and re-run the tests to make sure we haven't
broken anything. Perfect! This is a really nice endpoint for creating a battle.

-------------

Next, let's keep going with viewing a single battle. Scenario: GETting a
single battle. And thinking about this, we're going to need to make sure
that there's a battle in the database first. I'm going to use similar language
as before to create a Fred programmer and a project called project_facebook.
And I have another step set so I can say And there has been a battle between
"Fred" and "project_facebook".

By the way, the nice auto-completion I'm getting is from the new PHPStorm 8
version, which has integration with Behat. I highly recommend it. Great,
so this makes sure there's something in the database. Next, we'll make the
GET request to ``/api/battles/something``. Here's the problem: the only way
we can really identify our Battles are by their id. They're not like Programmer,
where each has a unique nickname that we can use.

Here, we know there's a Battle in the database, but just like before when
we were building the request body, we have no idea what that id is going
to be. Fortunately, we can use that same magic % syntax as before. This time
we can say ``%battles.last.id%``. Before, we used this syntax to query for
a programmer by its nickname. But this syntax also has a special "last" keyword,
which basically says to get that last record in the table. Again, this is
*me* adding special things to *my* Behat project that is really really handy
for testing the API.

Next, go to ``programmer.feature`` and find its "GET one programmer". We'll
copy the endpoint and "Then" lines and do something similar. The status code
looks good. The Battle has a ``didProgrammerWin`` field and we'll also make
sure that the ``notes`` field is returned in the response.

You guys know the drill. We're going to try this first to make sure it fails.
This is on line 26, so we'll add ``:26`` to only run this scenario. And there
we go - we get the 404 instead of the 200 and that's perfect.

Let's get this working! In ``BattleController``, add a new GET endpoint for
``/api/battles/{id}`` and change the method to ``showAction``. So because
we have a ``{id}`` in the path, the ``showAction`` will have a a ``$id``
argument.

From here, life is really familiar. First, do we need security - always ask
yourself that. I'm going to decide that anyone can fetch battle details out
without being authenticated. So we won't add any protection.

We *will* need to go and query for the ``Battle`` object that represents the
given id. We always want to check if that matches anything, and if it doesn't,
we want to return a really nice 404 response. In episode 1, we did that by
using a function called ``throw404``. That's going to throw a special exception,
that exception is mapped to a 404, and because we have our nice error handling,
we're going to get the nice Api Problem response format that we've been working
with. 

We have the object and we know we want to serialize it to get that consistent
response. Once again, this is really easy, because we can just re-use the
``createApiResponse`` method, and that's going to do all the work for us.
We don't need the 2nd argument, because that defaults to 200 already. That's
it guys - let's run the test. Wow, and it already passes. This is getting
*really really* easy, which is why we put in all the work before this.

Now that we have a proper ``showAction``, we can go back and fix the "todo"
in the header. First, we'll need to give the route an internal name - ``api_battle_show``.
In ``newAction``, we'll use ``generateUrl`` to make the URL for us. Again,
these shortcuts are things I added to *my* project, but this is just using
the standard method in Silex to generate the URL based on the name of the
route. And you can see what all of the shortcut methods really do by opening
up the ``BaseController`` class. Perfect!

First, let's make sure we didn't break anything by re-running the entire feature.

------------------


Let's do something really interesting. First I want to see what the Battle
response really looks like - so I'll say "And print last response". Now,
run Behat.

At the bottom, you can see that response has Programmer and Project information
right inside of it. That's because the Battle class has two properties that
actually hold these object. The serializer sees these objects, and serializes
them recursively.

In a couple of chapters, we're going to talk about embedding resources where
we do this on purpose. But for now, I want to avoid it: if I'm getting a Battle,
I only want to retrieve that Battle. So like before, we need to take control
of how this is serialized. I'll copy the ``Serializer`` ``use`` statement
from Programmer into Battle. Next, let's also copy the ``ExclusionPolicy``
annotation into ``Battle``, which tells the serializer to *only* serialize
properties that we explicitly expose with the ``@Serializer\Expose`` annotation.
Which properties you want to expose is totally up to you. I'll expose the
``$id``, of course ``$didProgrammerWin``, ``$foughtAt`` and we'll also expose
the ``$notes`` property.

You guys know the drill - let's run just line 26 to make sure things still
pass. We're still printing out the last response, but nothing is broken,
so that's good. You can see that it's in fact not printing out the programmer
or project anymore.

But now that we did that, thinking about somebody who *is* retrieving a single
battle, they might want to know who the actually programmer was. They might
want to say: ok, I see this battle, but what programmer fought in this battle
and how can I get more information about them. So what I'll do is add a new
line to the scenario and look for a new, invented field: And the "programmerUri"
field should equal "/api/programmers/Fred". So we're saying that the response
will have this extra field that's not *really* on the Battle class. What's
cool about this is that as an API client, I'll see this and say "Oh, Fred
was the programmer, and I can just go to that URL to get his details".

First, let's run this and watch it fail. So how can we add this? The problem
is that there really *is no* ``programmerUri`` property on Battle. So one
of the cool features from JMS serializer is the ability to have virtual fields.

Create a new function called ``getProgrammerUri`` - the name of the method
is important - and for right now, I'm just going to hardcode in the URL instead
of generating it from the name like we have been doing. I'll fix that later.

But just because you have this method does not means it's going to be served
out to your API. But you can use an annotation called ``@Serializer\VirtualProperty``.
And just like that, it's going to call ``getProgrammerUri``, it's going to
strip off the ``get`` off of there, and it's going to look like a ``programmerUri``
field. And when I run my test, it does exactly that.

Congratulations! We just added our first link, and we're going to add a bunch
more. Why? Because of nothing else, it's really convenient that when an API
client is retrieving a Battle, it can just follow a link to get more information
about the programmer.
