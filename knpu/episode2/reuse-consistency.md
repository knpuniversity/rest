# Reuse and Consistency

Now I want to do a couple of other cleanup things. First: whenever we need to
read information off of the request that the client is sending us, we're going 
to use this same `json_decode` for the content:

[[[ code('b4f7a37cdd') ]]]

In fact, we use this in `ProgrammerController` inside of the `handleRequest`
method.:

[[[ code('fee84cdec2') ]]]

So we have the same logic duplicated there. Let's centralize this by putting
it into our `BaseController`. Open this up and create a new protected function
at the bottom called `decodeRequestBodyIntoParameters`. I know, really short
name. And we'll take in a `Request` object as an argument. And at first this
is going to be really simple. We can go to the `TokenController`, grab this
`json_decode` line, go back to `BaseController` and return it::

[[[ code('6e4ffb041e') ]]]

So now in the `TokenController`,  `$data = $this->decodeRequestBodyIntoParameters($request)`
and we've got it. 

So just to be sure, let's go back and run our entire feature:

```
php vendor/bin/behat features/api/token.feature
```

## Consistently Erroring on Invalid JSON

Everything still passes so we're good. So, why did we do this? Back in `ProgrammerController`, 
when we decoded the body in `handleRequest`, we also checked to see if maybe 
the json that was sent to us had a bad format. If the JSON *is* bad, then 
`json_decode` is going to return `null` which is what we're checking for here:

[[[ code('1cb364d4b7') ]]]

So let's move that into our new `BaseController` method, because that is
a really nice check. And then it's creating an `ApiProblem` and throwing
an `ApiProblemException` so we can have that really nice consistent format.
We just need to add the `use` statements for both of these:

[[[ code('6e4ffb041e') ]]]

Perfect. Let's rerun those again to make sure things are happy ... and they
are!

## Don't Blow up on an Empty Request Body!

One other little detail here is that if the request body is blank this is
going to blow up with an invalid request body format because `json_decode`
is going to return `null`. Now technically sending a blank request is not
invalid json so I  don't want to blow up in that way. This doesn't affect
anything now but it's planning for the future. So if `!$request->getContent()`,
then just set $data to an array. Else, we'll do all of our logic down here
that actually decodes the json:

[[[ code('97c11d81e7') ]]]

And just to make sure we didn't screw anything up, we'll rerun the tests.

```
php vendor/bin/behat features/api/token.feature
```

## A ParameterBag Makes Life Nicer

One last little thing that is going to make our code even easier to deal with.
Back in `TokenController`, because the `decodeRequestBodyIntoParameters`
returns an array, we need to code a bit more defensively here. What if they 
don't actually send a `notes` key, we don't want some sort of PHP error:

[[[ code('77233340cf') ]]]

The horror!

And that's not that big of a deal but it's kind of annoying and error prone.
So instead, in our new function I want to return a different type of object
called a `ParameterBag`:

[[[ code('2486d871fb') ]]]

This comes from a component inside of Symfony that Silex uses. It's an object
but it acts just like an array with some extra nice methods. Let me show
you what I mean, back in `TokenController` instead of using it like an array
we can now say `$data->get()` and if that key doesn't exist it's not going
to throw some bad index warning. We can also use the second argument as the
default value:

[[[ code('f349363fb5') ]]]

Nice, so once again let's rerun the tests and everything's happy!

## Use decodeRequestBodyIntoParameters in all the Places

We have this really nice new function inside of our `BaseController` and 
I also want to take advantage of it inside of our `ProgrammerController`.
We're going down to `handleRequest` and now we can just say 
`$data = $this->decodeRequestBodyIntoParameters()` and pass the `$request`
object:

[[[ code('1b9ebee2c7') ]]]

Next, this big `if` block is no longer needed. And now because data is an
object instead of an array, we need to update our two or three usages of
it down here, which is going to make things simpler. So instead of using
the `isset` function, we can say `if(!$data->has($property))` because that's
one of the methods on that `ParameterBag` object. And down here instead of
having to code defensively using `isset`, we can just say `$data->get($property)`.
In fact let's just do this all in one line:

[[[ code('bf11403251') ]]]

Lovely! Now that was a fairly fundamental change so there is a good chance that we
broke something. So let's go back and run our entire programmer feature:

```
php vendor/bin/behat features/api/programmer.feature
```

Beautiful! We didn't actually break anything which is so good to know. 
