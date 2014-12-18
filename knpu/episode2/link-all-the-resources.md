# Link to a Subordinate Resource!

I'm playing with my API and looking at the collection of programmers. And
of course, I can follow the self link to GET just that one resource. But
now that I'm here, it occurs to me that it would be really cool if I had
a `battles` link we could follow that would return a collection resource of
all of the battles that this programmer has been in. So let's do that.

I'm going to add a new scenario inside `programmer.feature`, since it'll
be showing me all the battles for a programmer. I'll call the scenario:
"GET a collection of battles of the programmer". At the start of this scenario,
we'll need a few projects, one programmer and a few battles between them. 
We used some similar language in battle.feature. I'll make sure there's a 
`projectA` in the database first and then repeat that to make a projectB. 
And let's make sure that our favorite programmer Fred exists as well. Finally, 
we'll add two more lines to create 2 battles between Fred and each project:

[[[ code('16003c05bc') ]]]

Cool - so that's all the setup work.

This will return a collection resource, so we can steal a lot of the scenario
from above, since all collection resources pretty much look the same.

## Simple Guide to URL Structures

For the URI, one of the things you'll hear is that URIs don't matter. In
theory, you can make whatever URIs you want. So if you're stressing out about
how a URI should look, just choose something, because it ultimately doesn't
matter.

That being said, you typically follow a pattern. So far we've seen URLs like
`/api/programmers` for a collection and `/api/programmers/Fred` for a
single programmer. And that's a decent pattern. In this case, this is actually
what we call a "subordinate" resource - it's the collection of battles *under*
a specific programmer. So a good URL for this is the URL to a specific programmer,
plus `/battles` to get the subordinate battles collection resource for
Fred. After that, everything will be pretty much the same, changing programmers
to battles. We'll even check that the first battle has a `didProgrammerWin`
property, since every battle has that. We don't know what it's going to be
set to, but it should definitely be there:

[[[ code('53d5a57828') ]]]

Great!

This starts on line 95, so let's run this and make sure it fails with a 404:

```
php vendor/bin/behat features/api/programmer.feature:95
```

Cool!

## Coding up the Programmer's Battles Endpoint

Let's get to work! Open `ProgrammerController`. We'll need a new route and
I'll copy the "show" route, since the URL will be really similar. We'll
add the `/battles` in the end and change the method to `listBattlesAction`:

[[[ code('e1e19e082c') ]]]

The route name isn't important yet, but we'll use it later to link. Let's
call it `api_programmers_battles_list`.

Implementing this is going to be really easy! I'll put it right between
`showAction` and `listAction` so I can steal from both. Ok, let's think about
what we need to do. First, we need to find the `Programmer` for this nickname.
We have code for this, so let's steal it:

[[[ code('ca514acbf6') ]]]

If you find yourself repeating a lot of code like this, you can always create
a private function inside your controller class and put it there. That's
similar to what we've been doing by putting functions inside of `BaseController`.

The second thing we need to do is to find all of the battles that are linked
to this programmer. I have a shortcut for this that I'll use:

[[[ code('a889d1e99a') ]]]

The code might be different in your project, but this is just saying:

    "Hey, go query the battle table where programmerId is equal to the id
    of the programmer that we have."

So what's cool is that from here, this is exactly like the `listAction`,
because it's just a collection resource. So I'm going to grab everything
from it, change the variable to `$battles`, change the key to `battles`,
and that's it!

[[[ code('b5b01db87a') ]]]

So with almost no work, we'll run the test again, and it passes!

```
php vendor/bin/behat features/api/programmer.feature:95
```

## Adding the battles Relation

Back on the Hal Browser, if we hit Go, we *still* don't have that link. 
*We* know that we can just add `/battles` onto the URL, but there's no 
link yet. Let's add it!

Open up the `Programmer` class and copy and paste to create a second `Relation`.
This time the key will be `battles`, and below we'll grab the route name
we created for the endpoint:

[[[ code('c56da127a4') ]]]

Then, everything else looks good, because this route *does* need the nickname.
So you *may* want to write a test for this if it's really important, but
I'm just going to go back to the Hal browser, click "Go", and boom! We have
a new `battles` link, which we can follow and see the collection resource.
We can open up one of the battles, follow a link back to the related programmer
and click to go back to the battles once again. We're surfing through our
API, which is really cool!

## Adding the Battle self Relation

If we click to look at a battle, you'll notice that we're missing one little
thing. It has a `programmer` link, but no `self` link, which we really
want every resource to have because it's a nice standard and it comes in
handy. So let's go add this, which is *really* easy.

Open the `Battle` class and copy the relation. Let's remove the `embedded`
option. We just want this to be a normal link. Change the link name to `self`
and go find the route name from inside `BattleController`.  For this, it's
`api_battle_show`. In this case, the route needs the `id` of the battle.
So on the relation, we can simply say `object.id`:

[[[ code('2b2cb1dfdf') ]]]

Awesome!

If we re-GET this request, we see a huge error! This is no bueno! But hey,
let's  run our test for this to see if it helps us:

```
php vendor/bin/behat features/api/programmer.feature:95
```

And you can see that we're missing some "id" parameter when generating the
URL. I made a mistake in the `Relation`. You probably  saw me do it, but
I'm still passing a `nickname` instead of passing the `id`:

[[[ code('6e78115d0a') ]]]

So now, things work. Thank God for our tests, because that was really easy
to debug. And every battle *now* has that `self` link.
