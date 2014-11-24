Get One Battle
==============

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
