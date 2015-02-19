# New Battle Resource (the Scenario)

Let's login to the site. Don't forget, our tests like to mess with our database,
so I'm going to delete the SQLite database file and it'll regenerate with
some nice test data:

```
rm data/code_battles.sqlite
```

We'll login as `ryan@knplabs.com` password `foo`.

We already know that I'm able to create a programmer. And we even have some
really nice API endpoints for this. The other part of the site is all about
battles. If I click "Start Battle", this is a list of projects that are in
the database right now. If I dare to select one of those project, it starts 
an EPIC CODE *Battle* OF HISTORY between the programmer and the project 
and picks a winner.

A battle is another type of resource, but it can't be created yet in the
API. Let's fix that!

## New Battle Feature

Like my other resources, I already have a class that models this. You can
see there's a programmer, a project, the outcome `didProgrammerWin` and
it even stores the date it was fought and some extra notes:

[[[ code('eb013e2783') ]]]

Let's make the endpoint to create new battles. We're going to start like always
by creating a new feature - `battle.feature`. The API clients are going to
want to create battles to see if their programmers can take on and defeat
these projects. After the business value, the next line is the person that's
benefiting from the new feature and finally we have a little description:

[[[ code('5afd004f84') ]]]

## Create Battle Scenario

Let's add the first Scenario: Creating a new Battle. If we go back to `programmer.feature`, 
we can copy a lot of this. First, in order to create a battle, we're probably
going to need to be authenticated. So, I'll copy this background:

[[[ code('647571ce0f') ]]]

I'm going to go back and copy the entire scenario for creating a programmer.
After all, this is an API, so creating a resource should always look pretty
much the same.

Let's work from the end backwards and think about how we want the response
to look. We know there's going to be a `Location` header, because there's
always a `Location` header after creating a resource. But we don't know what
URL that's going to be yet, because we don't have an endpoint yet for viewing
a single battle. So we'll just say that the `Location` header should exist.
And if you look at the `Battle` class, you'll see there's a `didProgrammerWin`
property. Let's just make sure that exists as well - we don't know if it's
going to be true or false, because there's some randomness. Let's update
the URL to `/api/battles` and the status code of 201 looks perfect:

[[[ code('d0def4fb13') ]]]

## Creating a Programmer and Project First

In order to create a Battle - we'll need to send a programmer and a project.
And probably the way we'll want the client to do that is by sending the programmer
and the project's ids. So let's send programmerId and projectId - but we don't
know yet what these should be set to.

Next, in order for us to start a battle, there needs to already be a programmer
and a project sitting in the database. So before this line, we'll need to
say `Given there is a programmer called`, and we'll create a new programmer
called Fred. Again, these are all built-in Behat definitions that I created
before we started working and they all live in either `ApiFeatureContext` or
`ProjectContext`. If you want to know what I'm doing behind the scenes, just
open up those classes. There's another one for `And there is a project called`,
and we'll say "my_project":

[[[ code('b11a965458') ]]]

A little problem still exists: we don't know what the id's are of the programmer
and project we just created. So I don't know what to put in the request body -
we really  want whatever ids those new things have. This is a really difficult
problem with testing API's. So one of the things I've put into my testing
system already, is the ability to do things like this:

[[[ code('322f3b3e05') ]]]

It's a special syntax. And what this will do is go find a programmer whose
nickname is "Fred" and give us its id. It'll create a query for that dynamically. 
This syntax is totally special - it's not something built into Behat. If
you want to know how it works, open `ApiFeatureContext`, scroll all the way
to the bottom and find the `processReplacements()` function. It parses out
that "%" syntax, looks for these wildcards, and lets us do some of that magic.
This will be really handy, and we'll use it a few more times.

We'll do the same thing for projects. This looks great!

[[[ code('579e9d2e12') ]]]

You know I like watching my tests fail first, so let's try it out. We'll
just run this new `battle.feature` file:

```
php vendor/bin/behat features/api/battle.feature
```

Instead of 201, we get the 404 because the endpoint doesn't exist. That's
awesome. Now let's make this work!
