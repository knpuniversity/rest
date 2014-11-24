HATEOAS Installation
====================

We just added a link to our API endpoint for getting a single battle. I want
to see the response again, so let's add "And print last response" then run
the test - it starts on line 26.

We just invented this idea that this new link would be on a field called
``programmerUri``. Part of the issue is that we have this link mixed up
with other *real* data fields on this property. It's not totally obvious
if we can follow this link, or if maybe this is just a field that happens
to be a URL, and that we could actually change that URL by sending a PUT
request if we wanted to.

A lot of smart people have thougth about this and have invented different
standardized formats for how your data and links should be organized inside
of JSON or XML. One popular one right now is called HAL. I'll click into
a document that has a really nice example. You can see that down here is
the data, and above that, you have a ``_links`` property that actually holds
the links. You'll also notice that there's this link called ``self``, and
that's something we're going to see over and over again. ``self`` is kind
of a standard thing where each resource has a link to itself, and you keep
that on a key called ``self``. What's cool about this is that it's used on
a lot of APIs. So if you ever see a link with the key ``self``, you already
know what they're talking about. We're going to add this to our stuff too.

Right now, we're using the serializer to create our JSON. And it works just
by looking at the class of whatever object we're serializing and just grabs
the properties off of it. And of course we have some control over which properties
to use.

But if you look at the ``_links.self`` thing, you might be wondering how
we're going to do this. Are we going to need a bunch of these ``VirtualProperty``
things for ``_links``?

Fortunaely, there's a really nice library that integrates with the serializer
and helps us add links. It's called HATEOAS, which is actually a REST term
that we'll talk about later.

Before we look at how this library works, let's get it installed. Copy the
name, then run ``compose require`` and the library name. Composer will figure
out the best version to bring into the project.

Just like the serializer, this library works via annotations. It basically
says the User class has a relation called ``self``, and its ``href`` should
be set to ``/api/users``, and then the ``id`` of the user. And we'll talk
about this syntax in a second. The end result will be something that looks
something like this: it'll create the ``_links`` and add a link called ``self``
and any other links you have.

Getting this setup is pretty easy. Find the ``HateoasBuilder`` code and copy
it. And quickly, I'll make sure Composer is done downloading the library.
There's always a place inside a Silex application - and most frameworks - 
to define services, which are just re-usable objects. You might remember
from earlier, that in my project, this is done inside this ``Application``
class. A few chapters ago, we used this to configure the ``serializer`` object.
The HATEOAS library hooks right into this, so we just need to modify a few
things. Instead of returning the serializer, we'll set it to a variable and
take off the call to ``build()``. Now we'll paste the new code in, return
it, and pass the ``$serializerBuilder`` into the ``create()`` method, which
is what ties the two libraries together.

Now of course, my editor is angry because I'm missing my ``use`` statement,
so I'll use a shortcut to add that, which put it at the top of this file.

So that's it. We're already using the ``serializer`` object everywhere, and
now the HATEOAS library will be working with that to add these links for us.

Before we add our first link, let's add a scenario to look for it. Go into
``programmer.feature`` and find the scenario for getting one programmer.
As I mentioned before. it's always a really good idea to have a ``self``
link. And if we look at the structure of HAL, this means we're going to have
a ``_links.self.href`` property, and its going to be set to the URL of our
programmer.

So let's add that here: And the "_links.self.href" property should equal -
and we know what the URL of this is going to be - "/api/programmers/UnitTester".
And as always, let's run this to see it fail. This scenario is on line 66
of ``programmer.feature``. And it fails because the property doesn't even
exist yet.

Go back to the HATOEAS docs and scroll back up. Grab ``use`` statement and
put it inside of the ``Programmer`` class. I'll go back and copy the ``HATEOAS\Relation``
stuff - beautiful. This says ``self``, because we want this to be the ``self``
link and we'll change the href to be ``/api/programmers/`` and then ``object.nickname``.

Now, what the heck is this ``expr`` thing? This comes from Symfony's expression
language, which is a small library that gives you a mini, PHP-like language
for writing simple expressions. It has things like strings, numbers and variables.
There are also functions and operators - very similar to PHP, but with some
different syntax. But you only have access to a few variables and a few functions,
so you're sandboxes a bit.

In this case, we're saying the URL is going to be this string, then the tilde
(``~``) is the concatenation character, so like the dot (``.``) in PHP. After
that, we have ``object.nickname``. When you use the HATEOAS\Relation, it
takes whatever object you're serializing - so Programmer in this case - and
makes it available as a variable in the expression called ``object``. So by
saying ``object.nickname``, we're saying go get the ``nickname`` property.

Let's try this test! Awesome, it passes that easily. Let's print out the
response temporarily. And you can see that we *do* have that ``_links`` property
with ``self`` and ``href`` keys inside of it. That transofrmation is all
being taken care of by the HATEOAS library.

-------------------------

This is great, except that I don't like this hardcoded URL in the Relation.
What I'd rather do is generate that URL from the internal name ``api_programmers_show``
like we normally do.

Fortunately, the HATEOAS library allows us to do that, and this can be hooked
up to work with any framework, since they all generate URLs. So as part of
that HateoasBuilder::create() step, you can actually set a URL generator
that does whatever you need.

If you're using Silex or Symfony, life is a little bit easier because there's
a built-in class called SymfonyUrlGenerator. I'll copy this line, go back
into Application, move ``build()`` onto the next line and paste this. And
don't forget that we need a ``use`` statement for that ``SymfonyUrlGenerator``,
so I'll click "import" to have PHPStorm add this class to the top of the
file for me. This class comes from the HATEOAS library, and we're just passing
it the ``url_generator`` object, which in Silex, is the object responsible
for generating URLs. In Symfony, it's called ``router``. 

With this, we can go back into ``Programmer``. First, I'm going to move things
onto multiple lines for my sanity. Isnetad of setting hte ``href`` to a URL,
we'll say ``@HATEOAS\Route`` . I'll make sure I have all my parantheseis in
the right place. In the Route, we'll have 2 arguments - the first is the
name of the route. The second argument is whateer variables we need to pass
into the route, in a ``parameters`` key. Because the route has the ``{nickname}``,
we're going to pass that here using the expression language again. This time,
we'll say ``object`` - because that represents the Programmer - ``.nickname``.
That's fancy way of saying: "generate me a URL, and here's the nickname to
use in that URL."

Unless I've messed something up, the test should pass like before. Ah, and
it doesn't! I messed up some synta. Anytime you see the Doctrine\Common\Annotations
``T_CLOSE_PARENTHESIS`` type of thing, this is a syntax error in your annotation.
I'm missing a comma between my arguments. Let's try that one more time.
Ah, I messed up again! If you look back at the docs, which I've been ignoring,
you can see that the quotes should be around the entire ``nickname`` value.
I'll fix that, and learn that it's always good to follow the docs. And *this*
time if finally passes. So other than my syntax error, that was easy to fix
up. And if this look overwhelming to you, that's ok. From now on, we're just
going to be copying and pasting this and customizing it for whatever links
we need.























































