# Adding Real Links with HATEOAS

Let's add more links! Back in `battle.feature`, we're returning a `programmerUri`,
which was our way of creating a link before we knew there was a good standard
to follow:

[[[ code('945e32948f') ]]]

So now we can say:
`And the "_links.programmer.href" property should equal "/api/programmers/Fred"`:

[[[ code('5eb5b85659') ]]]

This time, instead of using `self`, we're using `programmer`. There are some
special names like self that mean something, but when you're linking from
a battle to a programmer, we'll just invent something new. We'll want to
use this consistently in our API: whenever we're linking to a programmer,
we'll use that same string `programmer` so that our API clients learn that
whenever they see this link link they know what type of resource to expect
on the other side.

First, let's run our test - line 26 - and make sure that it fails:

```
php vendor/bin/behat features/api/battle.feature:26
```

Let's go in and add that relation. Open up `Battle` and also open up `Programmer`
so we can steal the `Relation` from there as promised. And don't forget,
every time you use an annotation for the first time in a class, you need
a `use` statement for it.

And also, since we have this relationship now, I'm going to remove our `VirtualProperty`
down below. So this is really good - we're linking to a `Programmer` like before.
So the route name is good and the nickname is good. The only thing that needs
to change is that in order to get the nickname of the programmer for this
Battle, we need to say `object.programmer.nickname` so that it uses the
`programmer` field below. Let's try our test. Ah, and it fails! I got caught
by copying and pasting - we *do* have a link, but its name is `self`. Change
that to be `programmer`:

[[[ code('d76b7679f9') ]]]

And now, we'll get that to pass. Awesome.

## Consistency = New Behat Step

Because we're *always* putting links under a `_links` key, I have a new
piece of language that we can use in Behat to check for links:

[[[ code('50af95f34b') ]]]

Why would I do this? It's just proving how consistent we are. This new sentence
will look for the `_links` property, so there's no reason to repeat it
in all of our scenarios. So let's try the test again - perfect.

```
php vendor/bin/behat features/api/battle.feature:26
```

We can repeat this same thing over in `programmer.feature` when we're checking
the `self` link. I'll comment out the old line for reference:

[[[ code('1a0a81f2be') ]]]

If we run our entire test suite, things keep passing:

```
php vendor/bin/behat
```

I love to see all of that green!
