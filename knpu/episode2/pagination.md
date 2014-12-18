# API Pagination Done Easily

Let's do some pagination! Technically, HAL doesn't have any opinion on how
all of this should work. But fortunately, the rest of the REST world *does*.
As you can see in this example, a great way to handle pagination is with
links (from http://phlyrestfully.readthedocs.org/en/latest/halprimer.html#collections):

```json
{
    "_links": {
        "self": {
            "href": "http://example.org/api/user?page=3"
        },
        "first": {
            "href": "http://example.org/api/user"
        },
        "prev": {
            "href": "http://example.org/api/user?page=2"
        },
        "next": {
            "href": "http://example.org/api/user?page=4"
        },
        "last": {
            "href": "http://example.org/api/user?page=133"
        }
    }
    "count": 3,
    "total": 498,
    "_embedded": {
        "users": [
            {
                "_links": {
                    "self": {
                        "href": "http://example.org/api/user/mwop"
                    }
                },
                "id": "mwop",
                "name": "Matthew Weier O'Phinney"
            },
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

The keys here - first, prev, next and last - aren't accidental. These are
Internet-wide standards like self. So if you use these names for your links,
your API will be consistent with a lot of other APIs.

## Pagination: Do it with Query Parameters

The other important thing to notice in this example is that pagination is
done with query parameters. Technically, there are a lot of ways the client
could tell us what page they want for a collection, like query parameters
or request headers. But honestly, query parameters are the easiest way. In
our case, we're going to follow what you see here exactly. And with the HATEOAS
library, this will be easy.

## Pagination Scenario

First, let's setup a scenario to test this in `programmers.feature`. In
this scenario, we're going to do something really cool: we're going to follow
the links for pagination. I want to be able to go to our collection resource,
get the URL for this `next` link and make a second request to the `next`
link and actually see what's on page 2. 

For our pagination, we'll show 5 programmers per page. In the `Given`,
we need to add a bunch of programmers to try this out - I'll paste in some
very imaginative code that gives us 12 programmers in the database:

[[[ code('28529ded31') ]]]

Everything after this will be very similar to the normal collection resource,
so I'll grab a the second half of that scenario. I'll remove the status code
200, because we're already testing for this above. After I make the first
GET request, we're going to parse through the response, find the `next`
link and make a second GET request. I already have a built-in step definition
to do exactly that. I'll just say: And I follow the "next" link:

[[[ code('01e4fe08fb') ]]]

If you looked at the implementation behind this - which I wrote - it knows
that we're using HAL, so it knows to look at `_links`, `next`, `href` and
make a second GET request for that.

So on the second page, we'd expect there to be Programmer7, so we can say:
And the "_embedded.programmers" property should contain "Programmer7", because
we expect that word to be somewhere inside that JSON. And we expect there
to *not* be Programmer2 and for there to also *not* be Programmer11:

[[[ code('4f6962ecb5') ]]]

Programmer2 should be on page 1 and Programmer11 should be on page 3.

This starts on line 96, so let's try the scenario out:

```
php vendor/bin/behat features/api.programmer.feature:96
```

It fails of course because it can't find the `next` link - our collection
response doesn't have that because we haven't done any of the work for it yet.

## Adding Pagination Links

If we look at the HATEOAS documentation, they talk about pagination. To do
pagination, you'll return this `PaginatedRepresentation` resource. So
let's create that in `ProgrammerController`. So here is `listAction`
where we're getting our programmers. And remember, right now we're creating
a `CollectionResource`, and that's what you return when you have a collection
resource, but you don't need it to have pagination:

[[[ code('b079e7abb1') ]]]

Below this, we'll say `$paginated = new PaginatedRepresentation`. My IDE
just added the `use` statement at the top for us - make sure you have it:

[[[ code('2459bc9f36') ]]]

This takes a number of different arguments. The first is the actual `CollectionRepresentation`.
The second is the route name to the list endpoint, which for us is `api_programmers_list`.
And it'll use this to generate the links like next, first and last. The third
argument is any array of parameters that need to be passed to the route.
So if the route had a nickname or id wildcard, you'd pass that here. But
there aren't any wildcards in this route, so we'll pass an empty array. The
next three arguments are the page we're on, the number of records we're showing
per page, and the total number of pages. I'm going to invent a few variables
and set them above in a moment:

[[[ code('243091b33c') ]]]

Above, let's fill this in. Initially, I just want to get the next and last
`_links` to show up, so I'm going to take some shortcuts and not worry about
truly paginating the results quite yet. I'll hardcode these variables for
now. I'll say that we're always on page 1, that the limit is always 5,
and we can calculate the total number of pages. If we have 12 programmers,
divided by 5 gives us 2.4, then we'll round that up with the ceil function:

[[[ code('643b173cd9') ]]]

Normally you'd use a library to help with pagination, but since I'm faking
it, I'm just doing some manual work myself.

Finally, now that we have this `$paginated` object, we'll pass it to
`createApiResponse`:

[[[ code('6282e3e2e0') ]]]

Cool. We'll still returning *all* of the programmers, so I don't expect our
test to pass, but let's try it:

```
php vendor/bin/behat features/api.programmer.feature:96
```

Our test fails, but it *is* getting further. Ah, it actually *is* following
the `next` link and it *is* actually seeing that the `Programmer7` resource
is in the response. But then it's failing because `Programmer2` and every
other programmer is still there.

### Hal Browser <3's Pagination Links

What's really cool is that if we go back to our Hal browser and go to
`/api/programmers`, those links are showing up. We also have some nice
properties that tell us about the collection. And we can start paginating
through the resources by following those links. And the way the links are
done is just via `?page=1`, `?page=2&limit=5`.

## Adding Real Pagination

Let's turn this into *real* pagination! Since the page and limit are being
passed as query parameters, let's use those. In my application, when I need
request information, I just add a `Request $request` argument to my controller:

[[[ code('02db5a447f') ]]]

Then for query parameters, I can say `$request->query->get('page')` and
the second argument is the default value if there is no `page` sent for
some reasons. And the same for `limit` - we'll let the client control this,
but we'll default to 5:

[[[ code('c741af0fc3') ]]]

Normally when you do pagination, you might do a LIMIT, OFFSET query to the
database or pass which subset of records you want to some search engine,
like Elastic Search. In both cases, we end up with *just* the 5 records we
want, instead of all 10,000. Here, I'm going to be lazy and *not* do that.
I'm just going to query fro *all* of my programmers, then just use a little
PHP array magic to only give us the ones we want. I'm just trying to keep
things simple.

If I were doing this in a real project, I'd probably use a library called
[Pagerfanta](https://github.com/whiteoctober/Pagerfanta). It helps you paginate
and has built-in adapters already for Doctrine, and Elastic Search. You can
give it things like, we're on page 2, we're showing 5 results per page, and
then it'll do the work to find only the results we need.

Instead of that, I'm going to paste in some manual logic and use `array_slice`:

[[[ code('00825347e9') ]]]

So I'm querying for all of the programmers and then slicing those down to
the ones I want. It's not an efficient way to do this, but we *should* have
a functional, paginated endpoint now. So let's try the test again, and it
works!

```
php vendor/bin/behat features/api.programmer.feature:96
```

Yes!

And to enjoy it a little bit more, we can go back to the Hal Browser, hit
Go, and since we're on page 2, we see Programmers 6-10. We can follow links
to the first page, then over to the last page to see only those 2 programmers.
Now pagination isn't hard, and it'll be really consistent across your API.
