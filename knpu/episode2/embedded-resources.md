# We can Embed Resources Too

When we made our Battle endpoint, we decided it might be convenient to have
a link to Programmer. It's just a nice thing to help our API clients. But
to get that programmer's information, they're going to need to make a second
request out to that URI. If it's *really* common to need the programmer
information when you GET a battle, you may *choose* to put the Programmer's
data, right inside the Battle's response.

What's really nice here is that HAL already has rules about how this should
work:

```
{
    "_links": {
        "self": {
            "href": "http://example.org/api/user/matthew"
        }
    }
    "id": "matthew",
    "name": "Matthew Weier O'Phinney",
    "_embedded": {
        "contacts": [
            {
                "_links": {
                    "self": {
                        "href": "http://example.org/api/user/mac_nibblet"
                    }
                },
                "id": "mac_nibblet",
                "name": "Antoine Hedgecock"
            },
            {
                "_links": {
                    "self": {
                        "href": "http://example.org/api/user/spiffyjr"
                    }
                },
                "id": "spiffyjr",
                "name": "Kyle Spraggs"
            }
        ]
    }
}
```

There's the `_links` section, but there's also an `_embedded` section.
And our HATEOAS library will help us put stuff there.

## Embedding a Resource

So let's try to embed our programmer into the battle. First, let's add a
line to the scenario that looks for this. Let's look for `_embedded`, and
we know it's going to be called `programmer`, and the data will live below
this and we know one of the fields on a programmer is `nickname`. And we know
this should be equal to `Fred`:

[[[ code('b101844106') ]]]

Let's make sure that fails first - and it does:

```
php vendor/bin/behat features/api/battle.feature:26
```

To make this work, we'll add more annotations to `Battle`. When you think
of one resource relating to another - like how our `Battle` relates to
the `Programmer` resource - there are 2 ways to express that relation. You
can either link to it *or* you can embed it. Those are both just valid ways
to think about expressing a link between 2 resources.

This `@HATEOAS\Relation` lets you do whichever you want. For a link, use
the `href`. To embed something, use the `embedded` key and set it to an expression
that points to which object you want to embed:

[[[ code('7aa38d626d') ]]]

And actually, if you include both `href` and `embedded`, it'll create a link
*and* embed it. 

Before we run the test, add a "And print last response", because I like to
see how my endpoints look. Let's run it:

```
php vendor/bin/behat features/api/battle.feature:26
```

Awesome it passes! If you look - HATEOAS is doing all the work for us. We
still have `_links`, but we also have `_embedded`. What's cool is that it
goes out to the `Programmer` resource and serializes it. You end up with
all the same properties as normal, and you even end up with its links. So
a lot of things are falling into place accidentally. 

## Prize: New Behat Definition

And just like with links, since embedded data always lives under `_embedded`,
I have a built-in definition you can choose to use if you want to:

[[[ code('a0de161fbf') ]]]

Behind the scenes, this knows to look for all of this on the `_embedded` property.

And the test still passes. Now I'll take out the print last response. When
it comes to linking and embedding, I hope you're feeling dangerous!
