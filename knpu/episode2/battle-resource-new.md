# Start (Create) an Epic Battle (Resource)

What's cool is that because we've done so much work up to this point, coding
up our API is going to get really easy. I'll copy `TokenController` - I 
like having one resource per Controller. Update the class name. And this already
has some code we want. So let's change the URL to `/api/battles`:

[[[ code('a228e9c47e') ]]]

In `newAction`, we're going to reuse a lot of this. To create a Battle,
you *do* need to be logged in, so we'll keep the `enforceUserSecurity()`.
This `decodeRequestBodyIntoParameters()` is what goes out and reads the JSON
on  the request and gives us back this array-like object called a `ParameterBag`.
So that's all good too.

I *am* going to remove most of the rest of this, because it's specific to
creating a token.

## Finding the Battle and Project

What we need to do is read the programmer and project id's
off of the request and then create and save a new `Battle` object based
off of those. So first, let's go get the `projectId`. We're able to use
this nice `get` function, because the `decodeRequestBodyIntoParameters`
function gives us that `ParametersBag` object. Let's also get the `programmerId`.

[[[ code('3f57933585') ]]]

Perfect!

And with these 2 things, I need to query for the Project and Programmer objects,
because the way I'm going to create the Battle will need them, not just their
ids.  Plus, this will tell us if these ids are even real. I'll use one of
my shortcuts to query for the `Project`:

[[[ code('5bef2004e6') ]]]

All this is doing is going out and finding the `Project` with that `id` and
returning the `Project` model object that has the data from that row. We'll
do the same thing with `Programmer`:

[[[ code('ce4395eebb') ]]]

## The BattleManager

Normally, to create a battle, you'd expect me to instantiate it manually.
We've  done that before for Tokens and Programmers. But for Battles, I have
a helper called `BattleManager` that will do this for us. So instead of creating
the `Battle` by hand, we'll call this `battle()` function and pass it the
`Programmer` and `Project`:

[[[ code('ccf13ad44b') ]]]

It takes care of all of the details of creating the `Battle`, figuring out
who won, setting the `foughtAt` time, adding some `notes` and saving all
of this. So we *do* need to create a `Battle` object, but this will do it
for us.

### Creating the Battle

Back in `BattleController`, I already have a shortcut method setup to give
us the `BattleManager` object. Then we'll use the `battle()` function we
just saw and pass it the `Programmer` and `Project`:

[[[ code('9bb10551cd') ]]]

And that's it - the `Battle` is created and saved for us. Now all we need
to do is pass the `Battle` to the `createApiResponse()` method. And that
will take care of the rest:

[[[ code('1fe0bd4197') ]]]

The `createApiResponse` method uses the serializer object to turn the `Battle` 
object into JSON. We haven't done any configuration on this class for the 
serializer, which means that it's serializing all of the fields. And for now, 
I'm happy with that - we're getting free functionality. 

This looks good to me - so let's try it!

```
php vendor/bin/behat features/api/battle.feature
```

Oh! It *almost* passes. It gets the 201 status code, but it's missing the
`Location` header. In the response, we can see the created `Battle`, with
`notes` on why our programmer lost.

### Adding the Location Header

Back in `newAction`, we can just set createApiResponse to a variable and
then call `$response->headers->set()` and pass it `Location` add a temporary
`todo`:

[[[ code('b16b7be05b') ]]]

Remember, this is the location to view a single battle, and we don't have
an endpoint for that yet. But this will get our tests to pass for now:

```
php vendor/bin/behat features/api/battle.feature
```

Perfect!

## Battle Validation

So let's add some validation. Since the `battle()` function is doing all
of the work of creating the `Battle`, we don't need to worry about it too
much. We just need to make sure that `projectId` and `programmerId` are valid.
I'll just do validation manually here by creating an `$errors` variable
and then check to see if we didn't find a `Project` in the database for
some reason. If that's the case, let's add an error with a nice message.
And we'll do the same thing with the `Programmer`:

[[[ code('0a1651650d') ]]]

Finally at the bottom, if we actually have at least one thing in the `$errors`
variable, we're going to call a nice method we made in a previous chapter
called `throwApiProblemValidationException` and just pass it the array of
errors. It's just that easy.

We don't have a scenario setup for this, so let's tweak ours temporarily
to try it - `foobar` is definitely not a valid id:

[[[ code('4bdf8add09') ]]]

```
php vendor/bin/behat features/api/battle.feature:11
```

Now, we can see that the response code is 400 and we have this beautifully
structured error response. So that's why we went to *all* of that work of
setting up our error handling correctly, because the rest of our API is so
easy and so consistent.

Let's change the scenario back and re-run the tests to make sure we haven't
broken anything:

```
php vendor/bin/behat features/api/battle.feature:11
```

Perfect! This is a really nice endpoint for creating a battle.
