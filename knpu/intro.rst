The REST API Tutorial
=====================

Well hey there! I hope you're ready to work, because we're going to build
an API from the ground up! It's not going to be easy: there's a lot to do
and a lot of theory to sort through. But I *promise* you that you'll be happy
you made the effort.

We'll of course use best practices and learn all about the theory behind
REST. But we're also going to be pragmatic. If you stick to the rules too
much, you'll get buried in technical draft specifications and blog post.
You'll also get the feeling that a perfectly RESTful API might be impossible,
and it would probably be pretty tough to use anyways.

Instead, we'll build a really nice API, keep to the best parts of REST, tell
you when we're breaking the rules and when the rules are still being debated.
And we're not going to stick to the easy stuff. Nope, we'll attack the ugliest
areas of an API, like custom methods and where each piece of documentation
should live and why.

The Project: Resources and Links
--------------------------------

The project? Introducing Code Battles: a super-serious site where programmers
battle against projects. After you register, you can create a programmer and
choose an avatar. REST is based around the idea of resources. If you're using
this screencast as a drinking game, you might *not* want to drink each time
I say "resource". It's too important to REST... you won't make it through
chapter 2. The same goes for "representations".

Pay special attention to the links on each page and how resources interact.
Our API will feel a lot like the web interface.

With a programmer, you can take an action on her: power up! Based on a little
luck, this will increase or decrease the power level of the programmer resource.
Next, start a battle, which is between the programmer and a project. A project
is our second resource.

My programmer dominated and won the battle! A battle is our third resource,
and we can see a collection of them by going to the homepage and clicking "scores".

Later, I'll explain exactly *why* these are resources, but it should feel
natural. 


Our world domination plan is to create an API that allows an HTTP
client to do everything you just saw, and even more. But what should the endpoint
look like for creating a programmer? What about editing it? Should the client
send the data via JSON and should we also return it via JSON? How can we
communicate validation errors and how should the URL structure look for things
like listing programmers and powering up a programmer? What about HTTP methods
and status codes? And how can we document all of this? How will the client
know what fields to POST when creating a programmer or what URL to use to
find projects to battle?

Woh! So building a usable, consistent API involves a lot more than making
a few endpoints. But that's why you're here, so let's go!
