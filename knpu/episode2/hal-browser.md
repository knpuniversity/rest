# Fun with the HAL Browser!

By using HAL, it gives our responses a nice, predictable structure. And while
that's *really* awesome, I want to show you something that's even *more* awesome
that we get for free by using Hal.

I'll google for [Hal Browser](https://github.com/mikekelly/hal-browser).
If you haven't seen this before, I'm not going to tell you want it is until
we see it in action. I'll copy the Git URL:

    git@github.com:mikekelly/hal-browser.git

Move into the `web/` directory of the project and clone this into a browser/
directory:

```
cd web
git clone git@github.com:mikekelly/hal-browser.git browser
```

Great!

## Opening up the Browser

Inside of this directory is a `browser.html` file - so let's surf to that
instead of our actual project:

    http://localhost:8000/browser/browser.html

And let me show you the directory - it's just that HTML file, some CSS and
some JS.

What we get is the HAL Browser. This is an API client that knows all about
dealing with HAL responses. We can put in the URL. Let's guess that there's
a battle whose id is 1 in the database, and make a *real* request to that
URL:

    Put this into the Hal Browser:

    http://localhost:8000/api/battle/1

We're actually getting a 404 right now, because apparently there's no battle
with id 1. I'll re-run a test that puts battles into the database so we have
something to play with:

```
php vendor/bin/behat features/api/battle.feature:26
```

## Properties, Links and Embedded

Perfect, this time it finds a battle. On the right you see the raw response
headers and raw JSON. But on the left, it's actually parsing through those
a little bit. It sees that *these* are the properties. So even though it
has this big response body on the right, the properties are *only* the `id`
and `didProgrammerWin` type of fields. It also parses out the links and puts
them in a nice "Links" section. And lets us follow that link, which I'll
do in a second. Down here, it also has the embedded resource. It shows us
that we have an embedded programmer, so let's click that and then it does
the same thing. It says: here are the properties, and even says, this embedded
resource has a link to itself.

Let's follow the link to the programmer. This actually makes a new GET request
to this URL - which is awesome - parses out the properties, and once again,
we see the `self` link. So just by using HAL, we get this for free, which
makes it really easy to learn your own API and you can even expose this if
you want, to your end users on your site. If you want to try our API, just
go to this URL and navigate your way around it.
