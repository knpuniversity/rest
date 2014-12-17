# Hypermedia vs. Media (Buzzwords!)

It's time to talk about a big term in REST -- hypermedia. It's one of those
terms that seems like it was invented to scare people, but it's really quite
underwhelming. We all know that every response has a `Content-Type`, like
`text/html` or `application/json`. So when you hear "media" or "media types"
and "content types", they're referring to the same idea.

We have two things: media and we have hypermedia.

## Media is...

Media is any format: `text/html`, `text/plain`, `application/json`.
These all contain data, and it's as simple as that.

## Hypermedia is... Links!

*Some* of these formats are also called *hypermedia*. What's the difference
between media and hypermedia? Hypermedia is a format that has a place for
*links* to live. And that's it. The classic case you'll hear people talk about
for hypermedia is HTML. HTML is the o.g. (original) hypermedia format. It
contains data - like we see here - but it also has a way to express links,
via anchor tags and forms are also a type of link. So these are links here
and these are links. So implicit in the HTML format is a way to separate
links from the rest of your data.

### JSON is not Hypermedia

JSON is **not** hypermedia. That may seem confusing, because you might be
thinking:

    "but didn't we just add links to our JSON - isn't that hypermedia?"

And that answer is no, because if you read the official JSON specification,
all it will talk about is where your curly braces, quotes, colons and commas
should go. JSON is about the structure of the data - it says nothing about
what's actually inside of the data. So by itself, JSON is just a media type,
because there's nothing in there that says where your links should live.

### Hal+JSON IS Hypermedia

But what's cool is that we've adopted this HAL JSON. This is something that's
built on *top* of JSON: it starts with the JSON structure and then adds extra
rules about where links should live. So when you talk about JSON, that's
a media format. But when you talk about HAL, that's a hypermedia format, because
it's spec tells you that links live below `_links`. 

So that's really it: hypermedia is just a way to say: I have a structure
that returns links, and there are rules about where those links live.

## Advertising your Hypermedia Type (Content-Type Header)

As soon as you adopt a hypermedia format, instead of returning a `Content-Type`
of `application/json`, you can return a `Content-Type` of something different,
like `application/hal+json`. At the [bottom of this page](http://phlyrestfully.readthedocs.org/en/latest/halprimer.html#interacting-with-hal),
there's an example of what a response looks like:

```
HTTP/1.1 201 Created
Content-Type: application/hal+json
Location: http://example.org/api/user/matthew

{
    ...
}
```

And you can see that the response comes back with a `Content-Type` of
`application/hal+json`. This is a signal to the client that the response has
a JSON structure but has some additional semantic rules on top of it. What's
awesome is that if we return this in our API and someone looks at that header,
they're going to say:

    "Oh, what's this application/hal+json format?".

If they haven't heard of it, they can Google it and read about the structure
and say:

    "oh, they're using a format where the links live in an `_links` key"

along with some other rules. 

## Globally Setting the Content-Type

Because we're already following HAL, returning this `Content-Type` header
on all of our endpoints is an easy win. In `battle.feature`, let's add
a new scenario line to test this. My editor isn't happy with my language here.
If you can't remember your definitions, run behat with the `-dl` option:

```
php vendor/bin/behat -dl
```

I'll grep this for `header` because I know I have a definition. Ah, and
my language is slightly off. And now PHPStorm is very happy:

[[[ code('c4b519c721') ]]]

Oh, and we actually want to look for `application/hal+json`. I'll run the
test first, and it's failing:

```
php vendor/bin/behat features/api/battle.feature:26
```

Remember, this is served from `BattleController`, so let's go back there.
And all of our endpoints call this same `createApiResponse` method:

[[[ code('b22867d4f4') ]]]

If we click into this, it opens up the `BaseController` and this is a method
we created earlier. It uses the serializer then creates a `Response`. So
let's just update that `Content-Type` header:

[[[ code('5905f673d8') ]]]

Run the test, and it passes perfectly:

```
php vendor/bin/behat features/api/battle.feature:26
```

Now, API clients can see this header and know that we're using some extra
rules on top of the JSON structure.
