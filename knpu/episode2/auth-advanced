Auth Advanced
=============

So we have this working authentication system, you set a token, the token is attached
to your user object. We look up who you are and we authenticate you and we can
then start getting down to work. 

Because we're good programmers we even wrote a scenario for this and made sure that
we can't create a programmer without authentication. What we didn't do is create a
scenario to check and see what happens if we send an invalid token. So let's do that
right now.

So in this case, I'm not going to make sure that there's a user in the database
that has my token. I'm just going to send a token and that token is not going to exist.

Then we'll just make any request that requires authentication. The response status code
should be 401 and remember we're always returning that API problem format that has a
detail property on it. And here we can say whatever we want, we know we want the the detail
property set. So to be nice to the users let's set it to invalid credentials. So they know
what went wrong. It's not like the forgot some token, it just wasn't valid.

So let's try this out. Again we can run Behat on one particular scenario. This one starts on
line 11. In fact you can see it almost passed, so out of the box things are working. We are
denying, we are sending a 401, and because of our error handler we are sending a nice api
problem format with the actual details set to "invalid credentials." This is once again one
of those mysterious strings that live in the deep part of Symfony when different things happen
we send back different error messages. If you want to customize these you are going to tranlate them.

And because I want people to be excited about our API I'm even going to add an exclamation point to this. WE
will see the difference it will make. We're expecting "invalid credentials!" and we are getting it with a 
period. So let's go find our translation file and it's as easy as what we did before. And that should do it.

So let's rerun and actually I made a mistake there, take that extra quote off. Perfect. And I made one other mistake,
it's catching me on a case difference. So this is why it is good to have tests, they have closer eyes than we do.
So I'll say "invalid credentials!" and a capital letter. Perfect! 

So next thing, we need all of our errors to always return that same api problem response. And when it does that i
also comes back with a special content type. So let's make sure the content type is here.

Ahh and in fact it's not coming back with that. We are getting an application/problem like format but the 
content type is not coming back like that. It's coming back as a simple application/json. So you remember
from earlier, all of our error handling magic is coming from this function here. And this function we are
absolutely making sure we create an api problem we set the content type correctly so that no matter what
happens and what goes wrong in our application we get that same structure and that same header. SO the mystery
is why are we getting the application/json instead of application/problem+json. The answer is in the security
system of Symfony or Silex once you throw an accessdeniedexception it's actually handled by a slightly different
system. So whenever you make a bad request with a bad token or bad username and password that code is actually
handled by an entry point. Which I already have created from us. And that is where our actual logic is coming
from. So the API entry point is responsible for taking in a bad token and returning a really helpful message to the
user that says hey "you have a bad token, but let me give you a nice message so you can actually figure out what's
going on there." So for example here, you can see why we get the detail and why we get the 401. If I change this to
403 for example and rerun our test you can see it's getting back the 403 instead of the 401. So this is the class 
that is handling that code. So that's easy enough. 

Try it again, perfect! So now we have two spots in our application that are actually creating these api/problem+json
responses. One that handles almost all of them and one that is a fall back for when you have the invalid credentials.
I know that's a little tricky but these are the two areas that you need to keep track of if you are doing this
inside of Silex. 

Now for consistency one of the things we did in episode 1 is we actually created an api problem class. And the idea
was whenever you had some sort of error response you needed to send back you could create this api problem object and
it has all the keys that you normally have inside of an api problem response. So we would make sure that we always
create a consistent api problem structure we don't have a typo when we create it or something like that. 

So right now inside of the api entry point we're kind of creating the api problem by hand. Which is something
I didn't want to do. For example, I'm just kind of saying detail here, where as what I want to do is create
an api problem object which helps me normalize that detail and make sure I'm not making any mistakes. 

So first, I'm closing a couple of these classes. Inside api problem there is a type. So the type key in the api
problem, this is what the spec calls for, and this should be a unique error in your application. So now we have 
two, we have validation error as one unique thing that can go wrong and invalid body format is another. That's if
the client sends us json but the json is malformatted. Now we have a third type of error, which is actually
you sent us bad credentials. So let's add a new constant here called type authentication error. And I'm just making
up this string, it's not terribly important. ANd then down here is a map from those types to a human readable 
response that should happen on the title key.

The purpose of this is when we create a new api problem we are forced to pass in a type and then that has a nice
little map, so given a certain type you always get this nice same identicle human readable explanation for it, you
don't have to duplicate the titles all around your codebase. 

So back in api entrypoint instead of this stuff you can create a new api problem object. Add our use statement for
that. And then status code we know is 401 and the type is going to be api problem and we'll use our new authentication
error type. So it's a nice way to make sure we don't just invent new types, we always use consistent types.

And then, we set the detail, the detail is going to be the message and that message comes from the actual
authentication so when we throw the bad credentials exception it gives us that bad credentials string. So that's
where that comes from. So internally based on whatever exception is thrown we will get a different message here
and then we can use the translator to translate that.

Then down here for the response we can say just new json response. For the content, we can say problem to array.
It's a function we used earlier, it just takes all those properties and turns them into an array. And then we use
our 401 status code. I realize I'm repeating myself for the 401, we'll fix that in a second. And then we'll use
problem get status code. And we'll keep the response headers already set. So this is a small improvement just
because I always want to be consistent in my code so my api is consistent. If I need to create an api problem
object, don't do it by hand. The api problem class does some special things for us. Like it attaches this title
and it makes sure we have a few consistent types in here. So if we try this we should get the same result as before
and we do. Perfect. 

So let's take this one step further. Even the creation of the response is error prone. So right now in both
the appication class where we have our error handler and inside of this api reentry point we create the json response
and we set the content type to application/problem+json by hand. I don't want to have a lot of these laying around 
my application, in creating a response I want to go through one central spot. So to do this, it has nothing to do
with Silex or API's we're just going to practice some code refactoring to repeat ourselves a little bit. 

So instead of API we're going to create a new PHP class called APIProblemResponseFactory and it's job will be to
create API problem responses. So we'll get a single function called createResponse and it will take in an api
problem object and create the response for that. And most of this we can just copy from our error handler code.

I'll make sure that I add a couple of use statements here. Perfect, so it takes in the api problem, it transforms
that into json and it makes sure that the content type header is set. So if we can use this instead of repeating
that logic elsewhere it is going to save us some trouble. So again, like we showed you a few chapters ago, inside
of Silex there is a way to create global objects called services. We did this for the Serializer which meant that
we could use it in multiple places. So I'm going to do the same thing with the api.responseFactory. ANd we'll
just return new api response problem response factory. Of course like anything else don't forget to add the 
use statement for that. Yes, this class is getting a little crazy. And that's it!

So first thing we are going to use this key here, because that is how we access our object. And down
inside this class we are going to make use of it. So here, I have that same app variable so I can basically
get rid of all this stuff here. So response = app create response. Pass it that problem object. ANd that's it, 
so that will go into our api problem response, and get returned and then we return that to the user. We can do
the same thing inside the api entry point. I need to practice a little bit of dependency injection, and if this 
is a new idea to you or going over your head we have a free tutorial about dependency injection. I highly recommend
you check it out, it is going to change the way you code. 

So in our application I'm going to find our entry point and I'm actually going to go past that new object right there
as the second argument to the constructor of our api entry point. Which means here I will now have a second argument.
Don't forget the use statement for that and we'll just set that on the second property. So now when this object
is created we're going to have access to this api response factory. Which means down here we can just use it. So
we still create the api problem but I don't want to do any of this other stuff. And that's it. So we just reduced
duplication, let's try our tests. Those pass! Let's try all of our tests for the programmer. Sweet! Those pass as 
well!

So there's no chance of duplication because everything is going through that same class. So to back up with all
of this. We first made sure we had a token authentication system, which we did and a little bit of a technical
level we did this with the listener and provider stuff. And the key here is that when we create our programmers
we are going to set an authorization header and that's tied to our user. That was easy enough and it meant that
from within our controller we could get the currently authenticated user and behind the scenes it was actually
using the token to get that.
