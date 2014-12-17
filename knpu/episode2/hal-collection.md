# Collections: The HAL Way

Let's go to one other endpoint - `/api/programmers`:

    Put this into the Hal Browser:

    http://localhost:8000/api/programmers

Here, things don't quite work out as well. The response body on the right
is the same as the properties on the left. That might not look wrong to you,
but the format for our collection is not correct yet according to HAL.

## Hal Collection Format

If we look back at the HAL Primer doc, you'll see that *this* is what a collection
resource should look like:

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
            }
        ]
    }
}
```

### Collections are "Collection Resources"

By the way, we've talked about resources like Programmer and Battle, but with
the collection endpoints - like `/api/programmers` - the collection itself
is known as a resource. So this is a collection resource. And in HAL, the
way this works is that all of the items in the collection are considered
to be embedded resources. If you imagine that this is `/api/users`, all the
users live under the `_embedded` key. The only true properties - if you have
any - relate to the collection itself: things like the total number of items
in the collection or what page you're on.

And above, you'll see that this collection has pagination and it's using
the links to help the client know how to get to other pages. We're going
to do this exact same thing later, which is awesome and it'll be really easy.

## Structuring Our Collections

But for now, I want to get things into this structure. First, before we
implement it, you guys know what we're going to do, we're going to update
our test. In our `programmer.feature`, the scenario for the collection *was*
looking for a `programmers` property to be the array. But now, it's going
to be `_embedded.programmers`. Let's go even one step further and say that
the first item in this should be the UnitTester:

[[[ code('6d3abadd9c') ]]]

So its nickname should equal UnitTester. This is line 84, so let's run this
test first to make sure it fails:

```
php vendor/bin/behat features/api/programmer.feature:84
```

And it does.

## The HATEOAS CollectionRepresentation

The HATEOAS library has its own way of dealing with collections. If you scroll
to the top of its docs, you'll see a section all about this:

```php
// From the HATEOAS documentation
use Hateoas\Representation\PaginatedRepresentation;
use Hateoas\Representation\CollectionRepresentation;

$paginatedCollection = new PaginatedRepresentation(
    new CollectionRepresentation(
        array($user1, $user2, ...),
        'users', // embedded rel
        'users' // xml element name
    ),
    'user_list', // route
    array(), // route parameters
    1, // page
    20, // limit
    4, // total pages
    'page',  // page route parameter name, optional, defaults to 'page'
    'limit', // limit route parameter name, optional, defaults to 'limit'
    false    // generate relative URIs
);

```

First of all, don't worry about this `PaginatedRepresentation` thing, we're
going to talk about that later - it's not nearly as scary as it looks here.
If we have a collection that doesn't need pagination, all we need to do is
create this `CollectionRepresentation` object. 

Open `ProgrammerController` and go down to listAction. Right now we're taking
the programmers, putting them onto a programmer key and serializing that.
Instead, create a new `CollectionRepresentation` - I'll use my IDE to auto-complete
that, which automatically adds a `use` statement at the top of the file.
The `CollectionRepresentation` takes two arguments: the items that are
inside the collection and the key that these should live under. I could use
anything here, but I'll use `programmers`:

[[[ code('8e4c18e4d0') ]]]

Just choose something that makes sense, then be consistent so that whenever
your API clients see a `programmers` key, they know what it contains. 

Now, we'll just pass this new object directly to `createApiResponse`. And
hey, that's it! Let's try the test:

```
php vendor/bin/behat features/api/programmer.feature:84
```

And this time it passes! So whenever you have a collection resource, use
this object. If it's paginated, stay tuned.

## Celebrate with Hal (Browser)

And now that we're following the rules of HAL, if we go back to the HAL browser
and re-send the request, you get a completely different page. This time it says 
we have no properties, since our collection has no keys outside of the embedded stuff, 
and below it shows our embedded programmers and we can see the data for each. Each 
programmer even has a `self` link, which we can follow to move our client to that 
new URL and see the data for that one programmer. If it has any other links, we could 
keep going by following those. So hey, this is getting kind of fun! And as you'll see, 
the HAL browser can help figure out what new links can be added to make life better.
