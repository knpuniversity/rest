Intro
-----

Hey again! In this course, we're going to build an API from the ground up.
Of course we're going to see best practices and learn all about the theory
behind REST. But we're also going to be pragmatic: if you try to build a
perfectly RESTful API, you'll get buried in research, technical draft specifications,
and a sinking feeling that a perfectly RESTful API is probably impossible,
and probably also not very usable.

Instead, we'll build a really nice API, keep to the best parts of REST, and
tell you when we're breaking the rules and when the rules are still being
figured out by the REST community. We'll also attack the ugliest areas of
an API, like custom methods and where each piece of documentation should
live and why.

The project? Introducing Code Battles: a ridiculous little site where programmers
battle against projects. After we register, we can create a programmer and
choose an avatar. REST is based around the idea of resources, and a programmer
is our first resource. I also want you to pay special attention to how each
page on the site is linked together and how resources interact. Oor API will
feel a lot like the web interface.

Once we have a programmer, we can take an action on her: power up! This increases
or decreases the power level of our programmer resource. Next, let's start
a battle, which is between our programmer and a project. A project is our
second resource.

My programmer's power was a bit low, so he lost the battle. A battle is our
third resource, and we can see a collection of them by going to the homepage
and clicking "scores".

We'll learn more about exactly *why* these are resources, but it should feel
natural. Our world domination plan is to create an API that allows an HTTP
client to do everything you just saw, and even more. What should the endpoint
look for creating a programmer? What about editing it? Should the client
send the data via JSON and should we also return it via JSON? How can we
communicate validation errors and how should the URL structure look for things
like listing programmers and powering up a programmer? What about HTTP methods
and status codes? And how can we document all of this? How will the client
know what fields to POST when creating a programmer or what URL to use to
find projects to battle?

Woh! So building a usable, consistent API involves a lot more than making
a few endpoints. But that's why you're here, so let's go!

