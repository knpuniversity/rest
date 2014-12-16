# I <3 HATEOAS Installation

We just added a link to our API endpoint for getting a single battle. I want
to see the response again, so let's add "And print last response" then run
the test - it starts on line 26.

```
php vendor/bin/behat features/api/battle.feature:26
```

We just invented this idea to put a field on a link called `programmerUri`.
Part of the issue is that we have this link mixed up with other *real* data
fields on this property. It's not totally obvious if we can follow this link,
or if maybe this is just a field that happens to be a URL, and that we could
actually change that URL by sending a PUT request if we wanted to.

## HAL JSON: Standardizing How Links Look

A lot of smart people have thought about this and have invented different
standardized formats for how your data and links should be organized inside
of JSON or XML. One popular one right now is called [HAL](http://phlyrestfully.readthedocs.org/en/latest/halprimer.html).
I'll click into a document that has a really nice example:

```json
{
    "_links": {
        "self": {
            "href": "http://example.org/api/user/matthew"
        }
    }
    "id": "matthew",
    "name": "Matthew Weier O'Phinney"
}
```

You can see that down here is the data, and above that, you have a `_links`
property that holds the links. You'll also notice that there's this link
called `self`, and that's something we're going to see over and over again.
`self` is kind of a standard thing where each resource has a link to itself,
and you keep that on a key called `self`. What's cool about this is that
it's used on a lot of APIs. So if you ever see a link with the key `self`,
you already know what they're talking about. We're going to add this to our
stuff too.

Right now, we're using the serializer to create our JSON. And it works just
by looking at the class of whatever object we're serializing and grabs the 
properties off of it. And of course we have some control over which properties
to use.

But if you look at the `_links.self` thing, you might be wondering how
we're going to do this. Are we going to need a bunch of these `VirtualProperty`
things for `_links`?

## Installing HATEOAS

Fortunately, there's a really nice library that integrates with the serializer
and helps us add links. It's called [HATEOAS](http://hateoas-php.org/), which
is a REST term that we'll talk about later.

Before we look at how this library works, let's get it installed. Copy the
name, then run `composer require` and the library name:

```
composer require willdurand/hateoas
```

Composer will figure out the best version to bring into the project.

## How HATEOAS Works

Just like the serializer, this library works via annotations:

```php
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation("self", href = "expr('/api/users/' ~ object.getId())")
 */
class User
{
    private $id;
    private $firstName;
    private $lastName;

    public function getId() {}
}
```

It basically says the `User` class has a relation called `self`, and its
`href` should be set to `/api/users`, and then the `id` of the user. And
we'll talk about this syntax in a second. The end result will be something
that looks like this:

```json
{
    "_links": {
        "self": {
            "href": "/api/users/12"
        }
    }
    "id": "12",
    "firstName": "Leanna",
    "lastName": "Pelham"
}
```

It'll create the `_links` key with `self` and any other links  you add below
it.

## HATEOAS Setup

Getting this setup is pretty easy. Find the `HateoasBuilder` code and copy
it. Quickly, I'll make sure Composer is done downloading the library.

There's always a place inside a Silex application - and most frameworks - 
to define services, which are just re-usable objects. You might remember
from earlier, that in my project, this is done inside this `Application`
class. A few chapters ago, we used this to configure the `serializer` object.
The HATEOAS library hooks right into this, so we just need to modify a few
things. Instead of returning the serializer, we'll set it to a variable and
take off the call to `build()`. Now we'll paste the new code in, return
it, and pass the `$serializerBuilder` into the `create()` method, which
is what ties the two libraries together:

[[[ code('27bbc09fd3') ]]]

Now of course, my editor is angry because I'm missing my `use` statement,
so I'll use a shortcut to add that, which put it at the top of this file.

So that's it. We're already using the `serializer` object everywhere, and
now the HATEOAS library will be working with that to add these links for us.

## Update the Scenario for Links

Before we add our first link, let's add a scenario to look for it. Go into
`programmer.feature` and find the scenario for getting one programmer. As
I mentioned before. it's always a really good idea to have a `self` link.
And if we look at the structure of HAL, this means we're going to have a
`_links.self.href` property, and it'll be set to the URI of our programmer.

So let's add that here: `And the "_links.self.href" property should equal` -
and we know what the URL of this is going to be - `/api/programmers/UnitTester`:

[[[ code('91fc2087fb') ]]]

And as always, let's run this to see it fail. This scenario is on line 66
of `programmer.feature`:

```
php vendor/bin/behat features/api/programmer.feature:66
```

And it fails because the property doesn't even exist yet.

## Adding your First Link

Go back to the HATOEAS docs and scroll back up. Grab the `use` statement and
put it inside of the `Programmer` class. I'll go back and copy the `HATEOAS\Relation`
annotation - beautiful. This says `self`, because we want this to be the `self`
link and we'll change the href to be `/api/programmers/` and then `object.nickname`:

[[[ code('b8f922483a') ]]]

### The Expression Language

Now, what the heck is this `expr` thing? This comes from Symfony's
[expression language](http://symfony.com/doc/current/components/expression_language/syntax.html),
which is a small library that gives you a mini, PHP-like language for writing
simple expressions. It has things like strings, numbers and variables. There
are also functions and operators - very similar to PHP, but with some different
syntax. You only have access to a few variables and a few functions, so
you're sandboxed a bit.

In this case, we're saying the URL is going to be this string, then the tilde
(`~`) is the concatenation character, so like the dot (`.`) in PHP. After
that, we have `object.nickname`. When you use the `HATEOAS\Relation`, it
takes whatever object you're serializing - so `Programmer` in this case -
and makes it available as a variable in the expression called `object`. So
by saying `object.nickname`, we're saying go get the `nickname` property.

Let's try this test!

```
php vendor/bin/behat features/api/programmer.feature:66
```

Awesome, it passes that easily. Let's print out the response temporarily.
And you can see that we *do* have that `_links` property with `self` and
`href` keys inside of it. That transformation is all being taken care of
by the HATEOAS library.
