Transitions and Client State
============================

Ok, just one more thing: state transitions. We already know about resource
state, and how a client can change the resource state by sending a representation
with its new state, or data.

In addition to resource state, we also have application state. When you browse
the web, you're always on just *one* page of a site. That page is your application's
state. When you click a link, you transition your application state to a
different page. Easy.

Whatever state we're in, or page we're on, helps us get to the next state
or page, by showing us links. A link could be an anchor tag, or an HTML form.
If I submit a form it POST's to a URL with some data, and that URL becomes
our new app state. If the server redirects us, *that* now becomes our new app
state.

Application state is kept in our browser, but the server helps guide us by
sending us links, in the form of ``a`` tags, HTML forms, or even redirect
responses. HTML is called a hypermedia format, because it supports having
links along with its data.

The same is true for an API, though maybe not the APIs that you're used to.
We won't talk about it initially, but a big part of building a RESTful API
is sending back links along with your data. These tell you the most likely
URLs you'll want your API to follow and are meant to be a guide. When you
think about an API client following links, you can start to see how there's
application state, even in an API.

Richardson Maturity Model
-------------------------

We just accidentally talked through something called the `Richardson Maturity Model`_.
It describes different levels of RESTfulness. If your API is built where each
resource has a specific URL, you've reached level 1. If you take advantage
of HTTP verbs, like GET, POST, PUT and DELETE, congrats: you're level 2!
And if you take advantage of these links I've been talking about, that means
you've reached "hypermedia", a term we'll discuss later. But anyways, hypermedia
means you're a Richardson Maturity Model grand master, or something. How's that
for gamification?

We'll keep this model in mind, but for now, let's start building!
