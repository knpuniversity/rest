Auth
=====

Let's talk about API Authentication. The web part of our site already has
Authentication. I can login as ryan@knplabs.com password foo. By the way, if that
ever doesn't work for you, go back and just delete the SQLight database we're using. Right now
our tests and our web interface are using the same database. So whenever you run
the tests it's going to reset your web interface database. So when we are logged
in as Ryan if we create a programmer, any programmer we create is attached to the Ryan
user. So we have authentication and we then know who created the programmers.

In our API that's not the case at all. we have no authentication right now. And we kind of
fake the attachment to a user so in newAction we call this function handleRequest. 
Which is just in this same class. Then down here on the bottom you can see where we
fake it. We basically assume there's a user called weaverryan in the database, grab
it's id and attach that to every programmer. So obviously that's just
hardcoded magic and we need to get rid of that. We need some way for the API request
to authenticate themselves as a user. The way we are going to do this is with tokens.
We are going to create our own simple token system or you can do something bigger like
OAuth. The important thing to realize is that the end results are the same.

The request is going to be sending a token to you and you are going to look that up
in some database and figure out who the user is and if they have access. Using OAuth is just
a different way to help you handle those tokens and get those tokens to your users.

Before we start anything let's write a new Behat feature, because we haven't
done anything with authentication yet. If you haven't used Behat much this might
seem a little bit new to you, but stick with me. What I'm going to describe here
is the business value of our authentication feature and who benefits from it
which is the API client. That part is not important technically, but it's a good
way to clarify why you're building this authentication feature. 

Below that let's add our first scenario. Which is going to be one where we try to create a programmer without
authentication because ultimately we want to require authentication. Which means if
we try this we should get a 401 authentication required error. 

If I just request a post/api/programmers the response I get should be 401. We don't know what the response
is going to look like yet but let's imagine that the response is JSON and has some detail
key on it which gives us a little bit of information of what went wrong. 

So there we go, no coding yet but we just described how we want the first scenario of this
feature to work. So if we tried this right now, which is always a good idea, it should
not pass. We should see it fail. By the way we can run one feature file at a time which
is going to be really useful for us. And there we go you can see its giving us a 400 error 
because it's failing validation, because we're not sending any valid JSON to it instead of the 401. 

SO let's go into programmerController and fix this as easily as possible. up in newAction
I already have a function called getLoggedinUser. It's either going to return a user object
or its going to return null. So what we can do is check if not get logged in user then we want to throw
the Access Denied page. In Silex you do this by throwing a special exception called 
accessdeniedexception. Make sure you have the right use statement for this, which is in the security
component. And there we go. So that should deny access so let's try our test. 

Boom! and this time you can actually see that we are getting past the 401, so it is returning 
a 401 authentication required. The only difference is that the response is coming back slightly different
there is a detail property but instead of it being set to authentication required it's set to
this not privileged to request the resource string. Which we don't necessarily understand where
that's coming from.

So first, how is this even working behind the scenes? If you were with us for episode 1 we did
some pretty advanced error handling stuff at the bottom of this application class. What we basically
did was add a function that's called anytime there's an error or an exception thrown anywhere
in the system. Our goal was to always consistently transform that into the same type of JSON response which
is specifically this API problem format which specifies exactly how your JSON response should look on 
errors. Because we are throwing this accessDeniedException it's ending up in this loop and our code
is doing its best to figures out what to do with it. Which means it is creating an API problem format
and it is creating one with the details on it right out of the box! The only thing we need to change is this message
"not privileged to request the resource" where the heck does that come from? In Silex's security
component when we throw different types of exceptions like the accessdeniedexception they have built
in messages attached to them. SO if you want to customize those, and this is specific to Silex, what
we need to do is just translate it. 

And we setup the translations in episode 1, this is just a little key value pair. And instead whenever that
error happens we want to tell the user authentication required. So again, this not privileged request
is something that is built into Silex's security system so we need to use that and translate it. 

Let's rerun the tests and there we go! Great, we've denied access and now we need to read some
sort of token authorization system so we can actually create programmers. Once again we are going to
start by going into one of our feature files and creating a scenario for this. WE are going to modify
an existing scenario. You see the background of the our programmer feature, that one of the things that
we do before every single scenario is to make sure the weaverryan user exists in the database. We aren't sending
authentication headers, just that the user exists in the database. So I’m going to extend this a little bit
and say the way I want our system to work is that I want to attach tokens to the users. So in a second
we are going to create a system where I can login as a specific user and then generate tokens that are 
associated with my account. When I make an api request I’m going to send one of the tokens and the system
will be able to identify who I actually am. 

So this is a built in line that I created that will create an authentication token that is attached to my 
user in the database. So I’ve already set up all the database structure for the fact that users can have
many tokens. And this is the important part, this says that on whatever request we make inside of our scenario
I want to have an authorization header sent. Authorization headers values are going to be token space abc123.
Why did I pick authorization header or token [space]? technically the name of the header authorization and
the format of token space and some token number aren't really important. If you use OAuth it has
directions on the type of names you should give these things. So I’m just using authorization header
and the word token space and the actual authentication token that we're sending. By the way, we aren't doing
it in this tutorial but one that that’s really important for authentication across your API is that you only
do it over SSL. SO the easiest thing to do is to require all your users connect to your api over https. Otherwise
these authentication tokens are going over the internet via plain text and it would be really easy for someone
to steal that and get unauthorized access. 

If we rerun one of our tests right now it's not going to make any difference. So let's rerun programmer.feature
I'm just going to run the first scenario which starts on line 11. SO we say :11 and it’s going to
fail. It's setting that authorization header but we aren't actually doing anything with it yet so we are getting
that 401 authentication required message. authorization part is going to get a little bit technical with 
Silex's security system . Which, if you are using it, great we are going to walk you through how. 
but if not we are going to go high level enough that you will see what types of things you need to do in your
system to make it happen. 

So inside this security directory here I’ve already set up a bunch of things for an API token type authentication system.
First thing we're going to do is open this API token listener. I've written some fake code in here as you can
see. The job of the listener is to look at the request object and find the token information off of it. Since
we're sending the token information on the authorization header, we are going to look for the information there.
So let's get rid of this hard coded text and instead we are going to go get that authentication header. You can say
request -> header [get] authorization. That's going to get you that actual raw token space abcd123 type of thing. 

Next, the actual token is the second part of that. so token string = this parse authorization header. Parse authorization
header is the function I’ve already created down here. It's a private function that expects a format
of token space and gets the second part for you. Perfect!

At this point the token string is abcd123. So that's all I want to talk about this token listener, it's the only
job of this class.

Next, I’m going to open up the api token provider, it's job is to take the token string abcd1234 and try to look
up a valid user object in the database for it. First, I have an API token table in my database. In fact,
if I show you some of the code behind the scenes it will help make sense. You can see here I am creating
a API token table, that's all this is. API_token that's the name of the table And it has token which is the string
and the user id which is the user it relates to. So you can image a big giant table full of tokens and each
token is related to exactly one user. So if we look up the entry in the token table we can figure out yes
this is a valid token and it is a valid token for a user whose id is 5, for example. 

So here the first thing we'll do is actually go and look that up. Again, I don't want to get into the details of how 
exactly all of this hooks up because I want to focus on REST. But I’ve already configured this class and
configured some code behind the scenes to take in a token string which is the abc123 thing in our case and
return to me an API token object which represents a row in that table. So we've taken the string and we've
queried for a row in the table we don't have that row, we throw an exception which is a 401 bad credentials
type of a system.

Next, once we have that we just need to look up the user object from it. Remember, the job of this class is start with
the token string and eventually give us a user object. And it does that by going through the api token table.

Same thing, I've already set up some code here, API token, user id. So we started with token string
used that to query for an api token object, and I'll show you that class right here. This is the api
token object and it represents a single row in the api token table. And then we used it's api user id property
to figure out what user it relates to. And that is the job of this api token provider class. It's fairly
technical and at the core of Silex's security system so I just want you to internalize that that is what it does.

At this point between these two classes and the other classes that I’ve set up, if we send this authorization
header with a valid token by the time we get it to our programmerController this getloggedinuser will actually
return to us the user object that's attached to the token that was sent. SO in the case of our scenario, since
we're sending a token of abcd123 it means that we'll get a user object that represents this weaverryan user. We will actually
be logged in, except we are logged in via the api token. So, let's try this out. And there it is!

Behind the scenes the system is actually logging us in via the api token. The guts for getting this all working
can be a little more complicated but the end result is very simple. Send an api token, send an authorization header
with the api token, use that to look in your database and figure out which user object if any is this token
attached to. 

So now what we can do in handleRequest, I have this ugly hard coded logic that assumed that there is a user called
weaverryan which we can replace with thisgetLoggedinUser which gives us the user object id and that will
get us the actual id of whoever we happen to be logged in as. Not some hard coded weaverryan object. 

So now that we have this great system where users are actually being authenticated we can start to deny access.
Let's do a little bit more of that. So newaction is requiring that you are logged in. Awesome! in showAction
and listaction we are going to leave those anonymous. In our API it's ok to read data as an anonymous user. You only
need to be logged in to update data. In updateAction we do need some extra security, it's more than just being
logged in we need to check to see if our user is actually the owner of that programmer or not. So we just add
some if statement logic, if programmer user id is not equal this getloggedinuser id throw accessdeniedexception.

Easy enough!

Since we're also going to use this in deleteAction let's go into our base controller and actually make this
a generic function. Open up the base controller, create a new protected function enforceProgrammerOwnershipsecurity. 
Let's copy the logic in there and don't forget to add your accessDeniedException use statement.
Boom! Perfect, so now go back to our programmer controller it's a lot easier to just reuse this logic. Let's
also use this down in deleteAction. Perfect, now the only other thing that could go wrong, is if the user is
not logged in at all and they hit updateAction, then we are actually going to die inside this function. The 
problem is that we're not actually logged in at all so this getloggedinuser is going to be null we call id
property on the null object. So one of the things before we call this function is that we need to make sure
the user is at least logged, if they are not logged in then they are definitely not the owner of this programmer.

So let's create another function here called enforceusersecurity. In this case go back to programmerController
and we can grab the logic right here. There we go. And from inside this one we can just make sure that the user
is actually logged in. And in programmer controller we can actually do the same thing, and save ourselves a little bit of
code. So between these two new functions enforceusersecurity and enforceprogrammerownershipsecurity we have a
really easy way to go function by function inside of our controller make sure that we're enforcing the right type of
security.

Because we are sending our authentication headers in the background of our scenarios we should be able
to run our entire programmer feature and it passes. Perfect! SO just like that we have our entire application
locked down you do need to send an authentication token header and we've even locked it down so you can't
delete or edit someone else's programmer. 

So this is great the only question now is in the real world if I'm an API client how do I get a token so I can
access the API? Right now we use a little bit of magic that I did in the background but obviously that's not going to work for 
our end users.


