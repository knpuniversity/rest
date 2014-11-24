HATEOAS Router
==============

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
