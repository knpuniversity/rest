## Planning

### Project

CodeBattles: You create a *programmer*, then battle *projects*.
You'll be able to do things (e.g. drink caffeine) to increase your *energy*
and do things (e.g. "study") to increase your *experience*. Then you can
battle more *projects*.

**Resources**:

- programmers
- projects
- battles

#### Overall Notes

- we'll write Guzzle tests as our "client" for everything

- add every step, we'll ask "how would a client know how to use this endpoint"?
  This means, where is the documentation, how would a user know to find it,
  where does it say which fields are needed, which request content-types
  are allowed/expected, where does it say how I can filter a collection, etc etc

### Project

#### CHAPTER 0

- start with a basic (but finished) site where you can click around, create
    programmers, battle projects, etc

- start also with the ability to register (and get an API key tied to your account)

- In chapter 0, introduce the world domination plan: make the student think
  about what we're going to be doing any why before coding. Sure we can't
  think about everything but we can start with a basic but complete plan,
  and improve things later, saying "client now wants to ...". This would
  be where we get *some* of the "RESTful Web APIs" process to help you figure
  out what resources you have and how you should link them

- I like the idea of laying out the resources and endpoints - the current
    web interface will help with this a lot

##### CHAPTER 1: API basics

- a bit of intro theory - but not too much to overdo it!
- mention Richardson Maturity Model (RMM) 0 and 1 
- Very basic REST introduction - the HTTP message, the GET method, status code
- Resources and representations (but not too heavy)

##### CHAPTER 2: POST (Creating things)

- create a POST endpoint to create a new programmer
  - no validation
  - intro to 201 response
- probably start by writing a "test" via Guzzle before making the endpoint
- add Location header to 201 response
- RMM level 2 (HTTP verbs)

##### CHAPTER 3: GET (Reading things)

- GET /programmers endpoint - very simple
- GET SHOW endpoint for each programmer - no links, no serialization - just
  getting raw programmers data and manually turning it into a JSON array

#### CHAPTER 3.5: Testing

##### CHAPTER 4: Editing Resources

- add programmer "edit" PUT
- URL is to the exact resource we want to "replace"
- idempotency
- we're sending a "representation" of the resource, which the server uses
  to update the underlying resource
- how PUT should equie the whole body (or next chpt?)

##### CHAPTER 5: Patch versus PUT

- mention PUT versus PATCH
- Perhaps allow PATCH /programmers/{id} as a valid endpoint

##### CHAPTER 6: DELETE

- show 405 response (method not allowed)
- add a delete endpoint
- introduce the 204 response
- tease "custom verbs" - like what happens if you are doing something
  beyond creating, showing, editing or deleting a resource?

##### CHAPTER 7: Form Validation errors and other API Problems

- introduce the idea of a standard! Api Problem
- add validation errors to the form (editing/creating programmer)
- handle 404's and other errors
- handling bad input - e.g. invalid JSON

#### Authentication

- create (or just hook up) a basic token-based authentication system
- talk about how users might get authentication tokens (we will need a UI
  for making one or another endpoint), how it relates to OAuth etc
- potentially implement throttling
- 401, 403

##### CHAPTER 8: Post, Show and Linking

Here we'll create a new "battle". We'll look up a projectId manually from
the web interface for our user.

- create a new battle (POST) /battles (send projectId and userId in body)
- create a GET /battles/{id} (update the previous POST to have Location header)
- make link back to the programmer from /battles/{id}
- maybe tease RMM level 3 as a segway into the next chapter

##### CHAPTER 9: Hal Basics

- RMM level 3 and "Hypermedia" concept
- Bring in Hal as a way of formalizing the data and links
- fix /battles/{id} to be HAL
- add a content-type to /battles/{id}
- fix the link on /battles/{id} to the progammer
- _self link (mention IANA more later with pagination)

##### CHAPTER 10: Using the HATEOAS library

- refactor links into HATEOAS library
- remove manual serialization logic and replace with HAL stuff
- fix /programmers/{id} to use HAL
- show a difficult serialization situation? Somewhere else?

##### CHAPTER11: Hal Embedded resources

- fix /programmers
- add the embedded resources (battles)
- links versus embedded

##### CHAPTER 12: HATEOAS

- add 3 new links to /programmers/{id} for "next battles": 3 possible next
  projects that we could battle
- update our client to no longer create battles via a hardcoded projectId,
  but instead, by choosing one of these links and following it
- HATEOAS
- highlight what it doesn't do - HTTP method, fields, etc
- add the API homepage
- HAL Browser

##### CHAPTER 13:  Content-Negotiation

- content-negotiation and serialization to HAL-XML
- read the Accept header

##### CHAPTER 14: Documenting Links

- some systematic way to document the links
- potentially here changing the links to URLs (or maybe we did it earlier)
- mention what documentation is still missing

##### CHAPTER 15: Starting Other Documentation

- something manual for now with basic details:

1. Authentication is done with OAuth
2. Representations are sent in JSON or XML
3. Submitted data is expected to be application/x-www-form-urlencoded

- fix the ApiProblem to be a URL that points to some page here

##### CHAPTER 16: URL Structures

- create /programmers/{id}/battles to show the battles for this user
- talk about URL structures and sub-ordinate resources
- and then say that it doesn't matter due to HATEOAS

##### CHAPTER 17: Custom Endpoints

- and endpoint to make our programmer study - what should that look like?
- invent something: "studying" POST /programmers/{id}/study
- add another: "drinking coffee" POST /programmers/{id}/drink-caffeine
- add links for these
- idempotency again

##### CHAPTER 18: Custom Endpoint PUT

- custom PUT endpoint - PUT /programmers/{id}/avatar
- https://github.com/knpuniversity/rest/issues/1#issuecomment-37961745
- idempotency
- add link: show how links can be anything, and how this is kind of like
  embedding an image on an HTML page

##### CHAPTER 19: Allowing application/json submits

- Add some listener? (depends on how we're handling forms) that decodes
  the JSON body to to POST data if the reqeust type is application/json
- update our documentation to say we allow this
- 406? 415?

##### CHAPTER 20: Pagination

- add pagination links on programmers
- talk about IANA
- add some light details to our documentation

##### CHAPTER 21: Filtering

- add filtering on programmers
- add documentation about the standard way we'll allow things to be filtered
- we still don't know what fields can be used on any filter - that's a docs todo
- why query paramters? To avoid /programmers/{id}/page/{page}/name/{name}

##### CHAPTER 22: Versioning

- Versioning: we could say that there is no need for versioning if we rely
  on HATEOAS, and also that URL-based versioning is a bad practice.

##### CHAPTER 23: Intro to Caching

- Show some basic headers that you should consider returning to your client
- Show how Guzzle can automatically respond to these cache headers
- Mention an HTTP Cache for performance

### TODOS


- Options method + Allow header: why not, but we would have to explain CORS then.
  It should be added somewhere, maybe where we talk about the clients (consumers).
  Speaking of which, aren't we supposed to talk about them? Apart our test
  client, we don't mention that we are building an API for consumers, that
  are softwares.
  -> if we cover this, do it very briefly

- where to break this into pieces? This is at least 2 screencasts

- where to configure that exceptions under /api should be json?

- when/how should we transform the avatar into into an actual path? Should
    we turn this into its own resource later that we upload?

- consider using getters/setters so we can make sure items are cast into
    the right type

- we should do a schema change

- should mention all of the HTTP methods somewhere, and that you could support
    HEAD and options if you want

- some sort of /me endpoint, or a way to get "my" programmers

- use Symfony's Form component?

### Questions


### Notes

- Do a Silex app. Later we will plan to have a smaller tutorial on doing
  this in Symfony.

- For OAuth, we might just mention "you're now going to send an XX resource, so you'd 
  want to check to see if this client has access - e.g. by looking at an access token if 
  you're using OAuth and using whatever your biz logic is).

