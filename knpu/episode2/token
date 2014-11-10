Token
=====

Now that we have a way for our API clients to send a token with the request
to get authenticated, we need a way for them to actually create these tokens.

I already have a way through our web interface to do this actually. First, let
me delete our SQLite database. Because the tests use that same databse so it
screws up my default user. Now, we can login as ryan@knplabs.com password foo
and if you go to the url /tokens you see I have a little interface here, I can
add a token, put a message, click `tokenify-me` and there we go, I get a token!

And this is something we can use right now in an API request to authenticate as
the ryan@knplabs user. So this is great and this might actually be enough for
you. But for me personally I also want a way to do this through my API. I want
to actually make a request through an endpoint that says "give me a new API
token."

So like always let's start with creating a new Behat Scenario. In fact this is a
whole new feature! Let's start with a new behat feature and we're going to
call it `token.feature` and then I'm going to start describing the business
value for this. In this case it's pretty easy you need to get tokens so you can
actually use our API. Perfect.

Next, let's put our first scenario here which is going to be the working
scenario for creating a token. One thing to remember is that even though
token relates to security it's really no different than any other resource we're
creating like a programmer resource. So the scenario for this is going to look
really similar. The only difference is that we can't authenticate with an API
token, because that is what we are trying to get. So instead we are going to 
authenticate with http basic. So first let's make sure there is a user in the 
database with a certain password. And just like you saw with the web interface
every token as a note describing how it's being used. So in our payload to the
request we'll send JSON with a little note. Great!

To send http basic headers with my request I have a built in line for this and 
then after that we are going to request just like normal. I'm making up this URL
but you can see I'm being consistent because we also had /api/programmers before.

After this it's just like our programmer endpoint, we know the status code should
be 201, that there should be a location header and we'll expect that the token
resource is going to be returned to us. And we'll expect that there's actually
a key called token that is the newly generated string. Awesome!

So let's try this, we know it's going to fail but we want to confirm that. Perfect!
And it does with a 404 because we don't actually have an endpoint for this.

So this is our second resource. We have a programmer resource, now we are going 
to handle a token resource. So I'm going to create an entirely new controller
class to handle this. And I'm going to make it look a bit like my programmer 
controller. Which means that it extends this base conroller class which we've 
been adding more helper methods into. Notice that I did just add a use statement
to that. And it expects us to have one method called addRoutes. This is special
to my implementation of Silex, but you'll remember that in programmer Controller
at the top we have this add routes method and that's just where we build all 
of our endpoints. So we can do the same things here. We have a post endpoint
and when we go to that endpoint we'll execute a method called new action and let's
just see if that works first. Perfect!

So go back, rerun the tests and it is working. It's still failing but instead of a
404 we see a 200 status code because we're returning foo. So let's do as little work
as possible to actually make this function. The first thing to know is that we
do have a token table and just like with our other tables like the programmer table
where we have a programmer object, I've also created an API token object. So I have
a little ORM in the system, and basically if we can create this new API token object
then we can save this to the database. So when we construct it we'll pass it the
user id and then we'll also set the notes on it and that's it. 

So let's start doing that. $token = new ApiToken(); I'll add the use statement 
for that. You can see immediately it's angry with me because I need to pass
the userid there. Now, what is the id of the current user? Remember, in our scenario
we're passing http basic authenitaction. So here we need to grab the http basic
username and look that up in the database. I'm not going to worry about checking
the password yet, we'll do that in a second. Now in my version of Silex whenever
you need request information you just type hint request variable there and it will
be passed in. Don't forget your use statement. 

Now you may or may not remember this, I had to look it up, but if you want to get
the http basic username that's sent with the request you can say 
$request->headers->get('PHP_AUTH_USER'). And don't forget your equals sign. Next
I'll look this up in our user database. For now we'll just assume it exists, I'm
not going to do any error handling. And then, we're going to say user id. Perfect!

Next, we need to set the notes. In our scenario we're sending a JSON body with 
the notes so here what we can do is just grab that off, and we did this before
in Episode 1, in fact we are going to centralize some of our logic in a second.
So, $request->getContent() gets us that JSON and this will return an array.
So, $token->notes = $data['notes']; and we'll get the notes key off of that. And
that's really it all we need to do now is save the token object. Which I'll do 
that with my simple little ORM system. And now we need to return our normal API
response, which remember we're using the Serializer at this point and in the last
couple of chapters we created a nice new function in our base controller called
createApiResponse. All we need to do is pass it the object we want to serialize
and the status code, which in this case is 201, and that is going to build and return
the response for us. And that's it! Let's try it out. Awesome! So it's failing because
we don't have a location header set, but if you look this is actually what's being
returned from the endpoint and you can tell it's actually working and inserting this
in the database. We're missing the location header and we should have it, but for
now I'm just going to comment that line out. Because I don't want to take the time
to build the endpoint to view a single token right now, I'll let you do that. So
run the test, perfect it passes!

So next, I'm not actually checking the password yet, so let's add another scenario
for that. And we can copy most of this scenario but we'll change a couple of things.

So first instead of the right password we'll send something different and instead
of 201 this time it's going to be a 401. And remember whenever we have an error
response we are always keeping that same format. Great! So let's run just this
one scenario which starts on line 21. And again, we're expecting it to fail 
but I like to see my failures before I actually do the code. Perfect! 
And it's getting 201 because it's working but it's expecting 401. So what we need
to do on our controller is check to see if the password is correct for the user.
I'm not going to do that, it would be different in every framework. But in my
main application class, where I configure my security, I've already configured things
to allow http basic to happen. By adding this little key here, when the http basic
username and password come into the request the Silex security system automatically
going to look up the user object and deny access if they have the wrong password.
It's kind of like our API token system, but instead of sending a token it's going
to be reading it off of the http basic username and password headers, so it's
really handy.

So that means that inside of here, if we need the actual user object you don't
need to query for it directly. The security system alrady did that for us. We
can just say $this->getLoggedInUser(). We don't really know if the user logged
in via http basic or even passed a different token and frankly we don't really
care. And since we need our user to be logged in we can use our nice 
$this->enforceUserSecurity() step right there. Perfect, let's try that out.

And it passes with almost no effort! 

So that's really it for creating the token resource but I do want to do a couple
of other cleanup things. First thing, whenever we need to read information off
of the request that the client is sending us we're going to use this same
json_decode of the request content. In fact, we use this inside of programmer
controller inside of our handle request method. So we have the same logic duplicated
there. So let's centralize this by putting it into our base controller. So let's
open up base controller and create a new protected function at the bottom here, 
called decodeRequestBodyIntoParameters. I know, really short name. And we'll
take in request object as an argument. And at first this is going to be really simple.
We can go to the token controller, just grab this line here, go into the base
controller and return it. So now in the token controller 
$data = $this->decodeRequestBodyIntoParameters($request) and we've got it. 
So just to be sure let's go back and run our entire feature. Everything still 
passes so we're good. So, why did we do this? Back in programmer controller, 
when we decoded the body there in handle request we actually also checked to
see if maybe the json that was sent to us had a bad format. If the JSON is bad 
then json decode is going to return null which is what we are checking for here.

So let's move that into our new base controller method, because that is a really
nice check. And then it's creating an api problem and throwing an api problem
exception so we can that really nice consistent format. But we just need to 
add the use statements for both of these. Perfect. So let's rerun these again
to make sure things are happy and they are!

One other little detail here is that if the request body is blank this is going
to blow up with an invalid request body format because json encode is going to 
return null. Now technically sending a blank request is not invalid json so I 
don't want to blow up in that way. This doesn't affect anything now but it's 
planning for the future. So if not (!$request->getContent) then just set data
to an array. Else, we'll do all of our logic down here that actually decodes
the json. And just to make sure we didn't screw anything up we'll rerun the tests.
And one last little thing that is going to make our code even easier to deal with.
Back in token controller because the decode request body in parameters returns
an array we need to code a bit more defensively here. Because what if they don't
actually send a notes key, we don't want some sort of PHP error. 

And that's not that big of a deal but it's kind of annoying and error prone.
So instead, in our new function I want to return a different type of object
called a parameter bag. This comes from a component inside of Symfony that Silex
uses. It's an object but it acts just like an array with some extra nice methods.
Let me show you what I mean, back in token controller instead of using it like 
an array we can now say $data->get and if that key doesn't exist it's not going to
throw some bad index warning. We can also use the second argument as the 
default value. Nice, so once again let's rerun the tests and everything is happy!

So we have this really nice new function inside of our base controller and 
I want to take advantage of it also inside of our programmer controller. So I'll
go down to handle request where we are doing this and now we can just say
$data = $this->decodeRequestBodyIntoParameters pass the request object. Next
this big if block is no longer needed and now because data is an object
instead of an array we need to update our two or three usages of it down here
which is going to make things a lot simpler. So instead of using the isset 
function we can say if not data has property because that's one of the methods
on that parameter bag object. And down here instead of having to code defensively
using the isset we can just say $data->get($property);. In fact let's just do this
all in one line. Perfect! Now that was a fairly fundamental change so there is
a good chance that we broke something.

So let's go back and run our entire programmer feature, beautiful! We didn't actually
break anything which is so good to know. So finally, let's add a little bit of
validation to our token. This should start to feel easy because we did all of
this before with our programmer. Now let me remind you in the programmer controller,
to validate things we call this validate function which is something I created
for this project before we even started. But the way the validation works is that
on our programmer class we have these @Assert things. So when we call the
validate function on our controller it reads this and makes sure the nickname isn't
blank. It's as simple as that!

Now if that validate function returns an array that actually has some errors in it
then we call this throwApiProblemValidationException function. Which is something
that we created inside this controller, so you can see it's further down inside this
same file. What does it do? No surprises, it creates a new API problem object sets
the errors as a nice property on it then throws a new ApiProblemException. We can
see this if we look inside the programmer.feature class. I'll search for 400 because
validation errors return a 400 status code. And you can see this is an example of
us testing our validation situation. We're checking to see that's there's a nickname
and avatar numbers property errors. 

So the api token class also has one of these not blank things on it. So all we
need to do in our controller is call these same methods. So first, let's move that
throwApiProblemValidationException into our base controller, because that's going
to be really handy. And of course we'll make it protected so we can use it in the sub
classes. Perfect!

Next, let's steal a little bit of code from our programmer controller and put
that into our token controller. So once we're done updating our token object we'll
just call the same function, pass it the token instead of the programmer and throw
that same error. Perfect, so this actually should all be setup. Of course what I
forgot to do was write the scenario first, so shaem on me. Let's write the 
scenario to make sure this is working. I'll copy most of the working version, so
here we won't actually pass any request body. Fortunately we've made our decode
function able to handle that. We know the status code is going to be 400. We
can check to see that the errors.notes property will equal the message that is
actually on the api token class. It will be this message right here. 

Perfect!

This starts on line 33, so let's run just this scenario. Ahh and it actually
passes, instead of the 400 it is giving us the 201, which means that things are ok.
You can actually see for the note it says 'default note' if you look back in
our token controller...Ah ha! It's because I forgot to take off the default value.
So now it's either going to be set to whatever the note is or null and if it's 
set to null we should see our validation kick in, and we do, perfect!





