# Battles and Programmer - Link them!

Let's do something really interesting. First I want to see what the `Battle`
response looks like - so I'll say "And print last response":

[[[ code('d0d5c7fb89') ]]]

Now, run Behat.

```
php vendor/bin/behat features/api/battle.feature
```

At the bottom, you can see that response has `programmer` and `project` information
right inside of it. That's because the `Battle` class has two properties that
hold these objects. The serializer sees these objects, and serializes them
recursively.

## Controlling (Removing) Embedded Resources

In a couple of chapters, we're going to talk about embedding resources where
we do this on purpose. But for now, I want to avoid it: if I'm getting a
`Battle`, I only want to retrieve that `Battle`. So like before, we need
to take control of how this is serialized.

I'll copy the `Serializer` `use` statement from `Programmer` into `Battle`.
Next, let's also copy the `ExclusionPolicy` annotation into `Battle`, which
tells the serializer to *only* serialize properties that we explicitly expose
with the `@Serializer\Expose` annotation. Which properties you want to expose
is totally up to you. I'll do it with the `$id`, of course `$didProgrammerWin`,
`$foughtAt` and we'll also expose the `$notes` property:

[[[ code('b28dc22229') ]]]

You guys know the drill - let's run just line 26 to make sure things still
pass:

```
php vendor/bin/behat features/api/battle.feature:26
```

We're still printing out the last response, but nothing is broken, so that's
good. You can see that it's in fact not printing out the programmer or project
anymore.

## Adding a Link to the JSON Response

But now that we did that, if we think about somebody who *is* retrieving
a single battle, they might want to know who the programmer was. I can hear
our user now: 

    "ok, I see this battle, but what programmer fought in it and how can 
    get more information about them?"

So what I'll do is add a line to the scenario and look for a new, invented field:
`And the "programmerUri" field should equal "/api/programmers/Fred"`:

[[[ code('945e32948f') ]]]

So we're saying that the response will have this extra field that's not *really*
on the `Battle` class. What's cool about this is that as an API client, I'll
see this and say:

    "Oh, Fred was the programmer, and I can just go to that URL to get his details".

First, let's run this and watch it fail:

```
php vendor/bin/behat features/api/battle.feature:26
```

### Using a VirtualProperty

So how can we add this? The problem is that there really *is no* `programmerUri`
property on `Battle`. So one of the cool features from JMS serializer is
the ability to have [virtual properties](http://jmsyst.com/libs/serializer/master/reference/annotations#virtualproperty).

Create a new function called `getProgrammerUri` - the name of the method
is important - and for right now, I'm just going to hardcode in the URL instead
of generating it from the name like we have been doing. I'll fix that later:

[[[ code('58344fe16d') ]]]

But just because you have this method does not means it's going to be served
out to your API. You can use an annotation called `@Serializer\VirtualProperty`:

[[[ code('a1cbccb799') ]]]

And just like that, it's going to call `getProgrammerUri`, strip the `get` 
off of there, and look like a `programmerUri` field. And when I run my test, 
it does exactly that.

Congratulations! We just added our first link. And we're going to add a bunch
more!
