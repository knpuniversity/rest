Token Validation
================

So finally, let's add a
little bit of validation to our token. This should start to feel easy because
we did all of this before with our programmer. Now let me remind you in the
``PogrammerController``, to validate things we call this validate function,
which is something I created for this project before we even started. But
the way the validation works is that on our ``Programmer``, class we have
these ``@Assert`` things. So when we call the ``validate()`` function on
our controller, it reads this and makes sure the nickname isn't blank. It's
as simple as that!

Now if that ``validate()`` function returns an array that actually has some
errors in it, then we call this ``throwApiProblemValidationException`` function,
which is something that we created inside this controller. You can see it's
further down inside this same file. What does it do? No surprises, it creates
a new ``ApiProblem`` object, sets the errors as a nice property on it then
throws a ``new ApiProblemException``. We can see this if we look inside the
``programmer.feature`` class. I'll search for 400 because validation errors
return a 400 status code. You can see this is an example of us testing our
validation situation. We're checking to see that's there are ``nickname``
and ``avatarNumber`` properties on ``errors``. 

So the ``ApiToken`` class also has one of these not blank things on it. So
all we need to do in our controller is call these same methods. So first,
let's move that ``throwApiProblemValidationException`` into our ``BaseController``,
because that's going to be really handy. And of course we'll make it protected
so we can use it in the sub classes. Perfect!

Next, let's steal a little bit of code from our ``ProgrammerController`` and
put that into our ``TokenController``. So once we're done updating our token
object, we'll just call the same function, pass it the token instead of the
programmer and throw that same error. Perfect, so this actually should all
be setup. Of course what I forgot to do was write the scenario first, so shame
on me~ Let's write the  scenario to make sure this is working. I'll copy most
of the working version. Here, we won't actually pass any request body. Fortunately
we've made our decode function able to handle that. We know the status code
is going to be 400. We can check to see that the ``errors.notes`` property
will equal the message that is actually on the ``ApiToken`` class. It will
be this message right here. 

Perfect!

This starts on line 33, so let's run just this scenario. Oh no, and it actually
passes! Instead of the 400 we want, it is giving us the 201, which means
that things are not failing validation. You can actually see for the note
it says ``default note``. If you look back in our ``TokenController``...Ah
ha! It's because I forgot to take off the default value. So now it's either
going to be set to whatever the note is or ``null``. And if it's set to ``null``
we should see our validation kick in. And we do, perfect!
