# GET Your (One) Battle On

Next, let's keep going with viewing a single battle. Scenario: GETting a
single battle. And thinking about this, we're going to need to make sure
that there's a battle in the database first. I'm going to use similar language
as before to create a Fred programmer and a project called project_facebook.
I also have another step that allows me to say And there has been a battle between
"Fred" and "project_facebook":

[[[ code('0e18b039d6') ]]]

By the way, the nice auto-completion I'm getting is from the new PHPStorm 8
version, which has integration with Behat. I highly recommend it.

Great, so this makes sure there's something in the database. Next, we'll make
the GET request to `/api/battles/something`. Here's the problem: the only
way we can really identify our Battles are by their id. They're not like
`Programmer`, where each has a unique nickname that we can use.

## The Special %battles.last.id% Syntax

Here, we know there's a Battle in the database, but just like before when
we were building the request body, we have no idea what that id was going
to be. Fortunately, we can use that same magic % syntax. This time we can say 
`%battles.last.id%`:

[[[ code('c51d7819d7') ]]]

Before, we used this syntax to query for a `Programmer` by its nickname.
But it also has a special "last" keyword, which queries for the last record
in the table. Again, this is *me* adding special things to *my* Behat project.
Which is really handy for testing the API.

Next, go to `programmer.feature` and find its "GET one programmer". We'll
copy the endpoint and "Then" lines and do something similar. The status code
looks good. The Battle has a `didProgrammerWin` field and we'll also make
sure that the `notes` field is returned in the response:

[[[ code('09a5cd6d8f') ]]]

You guys know the drill. We're going to try this first to make sure it fails.
This is on line 26, so we'll add `:26` to only run this scenario:

```
php vendor/bin/behat features/api/battle.feature:26
```

And there we go - we get the 404 instead of the 200 and that's perfect.

## Creating the GET Endpoint

Let's get this working! In `BattleController`, add a new GET endpoint for
`/api/battles/{id}` and change the method to `showAction`. Because
we have a `{id}` in the path, the `showAction` will have an `$id`
argument.

[[[ code('ab389bff9d') ]]]

From here, life is really familiar. First, do we need security? - always ask
yourself that. I'm going to decide that anyone can fetch battle details out
without being authenticated. So we won't add any protection.

We *will* need to go and query for the `Battle` object that represents the
given id. We always want to check if that matches anything, and if it doesn't,
we want to return a really nice 404 response. In episode 1, we did that by
using a function called `throw404`. That's going to throw a special exception,
which is mapped to a 404 status code, and because we have our nice error handling,
the `ApiProblem` response format will be returned:

[[[ code('5a7afc75d3') ]]]

We have the object and we know we want to serialize it to get that consistent
response. Once again, this is really easy, because we can just re-use the
`createApiResponse` method, and that's going to do all the work for us.
We don't need the 2nd argument, because that defaults to 200 already:

[[[ code('4f82c40ed3') ]]]

That's it guys - let's run the test:

```
php vendor/bin/behat features/api/battle.feature:26
```

Wow, and it already passes. This is getting *really really* easy, which is
why we put in all the work before this.

## Don't Forget to Fix the Location Header

Now that we have a proper `showAction`, we can go back and fix the "todo"
in the header. First, we'll need to give the route an internal name - `api_battle_show`:

[[[ code('8b25a48ad9') ]]]

In `newAction`, we'll use `generateUrl` to make the URL for us:

[[[ code('c418e73246') ]]]

Again, these shortcuts are things I added to *my* project, but this is just
using the standard method in Silex to generate the URL based on the name
of the route. And you can see what all of the shortcut methods really do
by opening up the `BaseController` class.

First, let's make sure we didn't break anything by re-running the entire feature.

```
php vendor/bin/behat features/api/battle.feature
```

Green green green!
