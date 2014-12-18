# Filtering and HATEOAS (The Buzzword)

Similar to pagination is filtering. Let's say that I want to be able to search
in the programmers collection by nickname. So I'll add a `&nickname=2`.
Why 2? Well, my programmer nicknames aren't very interesting, but if I use
2, I'll want it to return Programmer2 and Programmer12. Of course right now,
this doesn't have any affect yet.

## Filtering: Use Query Parameters

Filtering is actually really easy. But the reason I wanted to cover it is
that sometimes people wonder if they should get clever with their URLs when
they're filtering and maybe come up with URLs like `/api/programmers/filter/nickname2`.
Don't do that. If you're filtering, use a query parameter, end of story.

## Coding up a Simple Filter

So how do we get this to work? It couldn't be simpler. At the top of `listAction`,
I'll look for the `nickname` query parameter. And if it's present, we'll
query in a special way. And if it's not, we'll do the normal `findAll`.
I have a shortcut setup to query using MySQL LIKE. I'll pass it the value
surrounded by the percent signs:

[[[ code('9f2344ef45') ]]]

And that should be it!

If we go back to the Hal Browser and hit go, we get nothing back! But we're
actually on page 3, so click to go back to page 1. Hmm, now we have too many results!
That's because we lost the &nickname query parameter. That's because I was
lazy - if I have extra filters I *should* pass those to the 3rd argument
of `PaginatedRepresentation`. If i do that, it'll show up in our pagination
links.

I'll re-add `&nickname=2` manually and this time, we see it's *only* returning
Programmer2 and Programmer12, and it knows that there's only going to be
one page of them. So that's it for filtering: use query parameters, do whatever
logic you need for filtering, and pass the filter to `PaginatedRepresentation`,
even though I didn't do it here. It's that easy.

## Hypermedia as the Engine of Application State (HATEOAS)

One quick thing to notice is that even though we now support this `nickname`
filter, there isn't any way for the API client to know this just by looking
at the links. We have links to help them go through the pages, but no link
that says: "Hey, if you want, you could pass a nickname query parameter to
filter this collection". There's not really a way with HAL to do that. There
*are* other formats that give you more options and ways to say "Hey, you can
add the following 4 query parameters to this URI, and here's what each will
do".

And this is one of those spots where REST can get frustrating. The purpose
of adding links is to *help* make your API client's life easier. They are
*not* intended to replace human-readable documentation. So even if you *did*
find a clever way to include filtering information in your response, this
would just be as a "nice feature" for your API. You should still document
which endpoints have which filters and what they mean.

The term HATEOAS means: hypermedia as the engine of application state. And
as cool as it is, it's at the heart of this confusion. In its most pure form,
HATEOAS is an idea that seems to suggest that if your API has really good
links, it doesn't need human-readable technical documentation. Instead, a
client can follow the links in your response and other details you return
to figure everything out.

But this is a dream, not a reality. As cool as our API is, it lacks a *ton*
of details. For example, it doesn't have any information about filtering.
Second, it doesn't tell you which HTTP methods each URL supports, nor what
fields you should POST to `/api/programmers` in order to create a new resource.
The links we have are nice, but they're *nowhere* near giving the API client
everything it needs.

So think of links as a nice *addition* to your API, but not something that'll
replace really nice human documentation.
