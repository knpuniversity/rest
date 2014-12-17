# HATEOAS Loves Routers

This is great, except that I don't like this hardcoded URL in the `Relation`:

[[[ code('b8f922483a') ]]]

What I'd rather do is generate that URL from the internal name `api_programmers_show`
like we normally do.

Fortunately, the [HATEOAS library allows us to do that](http://hateoas-php.org/#url-generators),
and this can be hooked up to work with any framework, since they all generate
URLs. As part of that `HateoasBuilder::create()` step, you can set a URL generator
that does whatever you need.

## Hooking up the SymfonyUrlGenerator

If you're using Silex or Symfony, life is a little bit easier because there's
a built-in class called `SymfonyUrlGenerator`:

```php
// from the HATEOAS docs
use Hateoas\UrlGenerator\SymfonyUrlGenerator;

$hateoas = HateoasBuilder::create()
    ->setUrlGenerator(null, new SymfonyUrlGenerator($app['url_generator']))
    ->build()
;
```

I'll copy this line. Go back into `Application`, move `build()` onto the
next line and paste this:

[[[ code('8992c67bfb') ]]]

And don't forget that we need a `use` statement for that `SymfonyUrlGenerator`,
so I'll click "import" to have PHPStorm add this class to the top of the
file for me. This class comes from the HATEOAS library, and we're just passing
it the `url_generator` object, which in Silex, is the object responsible
for generating URLs. In Symfony, it's called `router`. 

## Using the Router in Annotations

With this, we can go back into `Programmer`. First, I'm going to move things
onto multiple lines for my sanity. Instead of setting the `href` to a URL,
we'll say `@HATEOAS\Route` . I'll make sure I have all my paranthesis in
the right place. In the Route, we'll have 2 arguments - the first is the
name of the route. The second argument is whatever variables we need to pass
into the route, in a `parameters` key. Because the route has the `{nickname}`,
we're going to pass that here using the expression language again. This time,
we'll say `object` - because that represents the Programmer - `.nickname`.
That's fancy way of saying: "generate me a URL, and here's the nickname to
use in that URL.":

[[[ code('927987034f') ]]]

Unless I've messed something up, the test should pass like before:

```
php vendor/bin/behat features/api/programmer.feature:66
```

Ah, and
it doesn't! I messed up some syntax. Anytime you see the Doctrine\Common\Annotations
`T_CLOSE_PARENTHESIS` type of thing, this is a syntax error in your annotation.
I'm missing a comma between my arguments. Let's try that one more time.
Ah, I messed up again! If you look back at the docs, which I've been ignoring,
you can see that the quotes should be around the entire `nickname` value.
I'll fix that, and learn that it's always good to follow the docs:

[[[ code('e63ffa4168') ]]]

And *this* time it finally passes. So other than my syntax error, that was
easy to fix up. And if this look overwhelming to you, that's ok. From now on,
we're just going to be copying and pasting this and customizing it for whatever
links we need.
