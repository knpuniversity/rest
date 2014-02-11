## Planning

### Project

- CodeBattles: fight against other programmers
- programmers  (M21 Avatar)
- projects (M2M Battle)
- battle (M21 Programmer, M21 project)

#### Overall Notes

- we'll write Guzzle tests as our "client" for everything

- add every step, we'll ask "how would a client know how to use this endpoint"?
  This means, where is the documentation, how would a user know to find it,
  where does it say which fields are needed, which request content-types
  are allowed/expected, where does it say how I can filter a collection, etc etc

### Project

#### CHAPTER 0

- start with a basic (but finished) site where you can click around, create
    characters, battle projects, etc
- start also with the ability to register (and get an API key tied to your account?)

##### CHAPTER 1: Basic API endpoint

- make an API request to get a list of characters from a "test" via Guzzle
  (doesn't exist yet). In fact, I think we use Guzzle and write a "test" for
  each endpoint before creating it
- create this basic endpoint - no links, no serialization - just getting
  raw characters data and manually turning it into a JSON array
- Very basic REST introduction - the HTTP message, the GET method, status code
- Resources and representations (but not too heavy)

##### CHAPTER 2: POST

- create a POST endpoint to create a new character
  - no validation
  - intro to 201 response

##### CHAPTER 3: Show and Link

- create SHOW endpoint for each character
- add Location header to 201 response

##### CHAPTER 4: Post, Show and Linking

Here we'll creat ea new "battle". We'll look up a projectId manually from
the web interface for our user.

- create a new battle (POST) /characters/{id}/battle/{projectId}
- create a /battles/{id} (update POST to have Location header)
- make link back to the the character from /battles/{id}

**Question**: How about the above URL structure? 

##### CHAPTER 5: Hal Basics

- Bring in Hal as a way of formalizing the data and links
- Hypermedia
- fix /battles/{id}
- content-type
- characeter link
- _self link (mention IANA more later with pagination)
- fix /characters/{id}

##### CHAPTER 6: Using the HATEOAS library

- refactor links into HATEOAS library
- remove manual serialization logic and replace with HAL stuff

##### CHAPTER 7: Hal Embedded resources

- fix /characters
- add the embedded resources
- links versus embedded

##### CHAPTER 8: HATEOAS

- add 3 new links to /characters/{id} for "next battles": 3 possible next
  projects that we could battle
- update our client to no longer create battles via a hardcoded projectId,
  but instead, by choosing one of these links and following it
- HATEOAS
- highlight what it doesn't do - HTTP method, fields, etc

##### CHAPTER 9: URL Structures

- create /characters/{id}/battles to show the battles for this user
- talk about URL structures and sub-ordinate resources
- and then say that it doesn't matter at all anyways, because of HATEOAS :)

##### CHAPTER 10:  Content-Negotiation

- content-negotiation and serialization to HAL-XML

##### CHAPTER 11: Documenting Links

- some systematic way to document the links
- potentially here changing the links to URLs (or maybe we did it earlier)
- mention what documentation is still missing

##### CHAPTER 12: Starting Other Documentation

- something manual for now with basic details:

1. Authentication is done with OAuth
2. Representations are sent in JSON or XML
3. Submitted data is expected to be application/x-www-form-urlencoded

##### CHAPTER 13: Editing Resources

- add character "edit" PUT
- URL is to the exact resource we want to "replace"
- idempotency
- we're sending a "representation" of the resource, which the server uses
  to update the underlying resource

##### CHAPTER 14: Patch versus PUT

- mention PUT versus PATCH
- Perhaps allow PATCH /characters/{id} as a valid endpoint

##### CHAPTER 15: Allowing application/json submits

- Add some listener? (depends on how we're handling forms) that decodes
  the JSON body to to POST data if the reqeust type is application/json
- update our documentation to say we allow this

##### CHAPTER 16: DELETE

- add a delete endpoint
- introduce the 204 response

##### CHAPTER 17: Custom Endpoints

- and endpoint to make our character study - what should that look like?
- invent something: "studying" POST /characters/{id}/study
- add a link for this
- idempotency again

##### CHAPTER 18: Pagination

- add pagination links on characters
- talk about IANA
- add some light details to our documentation

##### CHAPTER 19: Filtering

- add filtering on characters
- add documentation about the standard way we'll allow things to be filtered
- we still don't know what fields can be used on any filter - that's a docs todo
- why query paramters? To avoid /characters/{id}/page/{page}/name/{name}

##### CHAPTER 20: API Problems

- handling "problems" API Problem or vnd.error

##### CHAPTER 21: Form Validation errors

- add validation errors to the form (editing/creating character)
- handle 404's and other errors


#### Issues

- missing an example where I create a new sub-ordinate resource (POST /teams/5/matches)
- missing an endpoint similar to the "payment" on an order example (PUT /orders/5/payment)
- when are we planning out the API design?
- consider a PUT endpoint for creating battles: /characters/{id}/projects/{id}/battle
- creating an image link for the avatar of a character
- where to break this into pieces?
- Symfony2 versus Silex?
- best way to collaborate
- documentation?
- documenting the input and output content-types
- different request/response content-types (the API might allow urlencoded
  OR json-body requests. based on the request content-type)
- error codes
- how much to talk about authentication?

### Topics

+ basic intro to REST's meaning
+ very basic HTTP intro
+ resources versus representations
+ Content-Negotiation
- Richardson's maturity model (of course) - but I'd rather show people first, then show this later
+ POST versus PUT (via examples), idempotency
+ 201 response, Location header (our first "link"!)
+ 204 response, when appropriate, etc
+ serialization
+ URL structures (even though it doesn't matter), subordinate resources, etc
+ adding "links"
+ pagination and those standard links (+, using query parameters)
+ filtering
+ using Hal as a standard format
+ Request media type [representation] (e.g. application/json) does not match
  the response media type [representation] (e.g. application/hal+json)
+ custom endpoints (e.g. "publishing a blog post/forfeiting a match" - URI, HTTP method, documentation)
- documentation: what should be documented, and why. How can we generate it?
+ standard link relations versus custom relations (and documentation)
- curies
- Options method and Allow header
- templated links
- CORS
- profiles (for pointing to docs)
+ validation error responses (API Problem, vnd.error)
+ embedded resources versus links
- caching
- authentication
+ functionally testing the API
- versioning?
- the API "homepage"

### Questions

- Symfony versus non-Symfony?
- Best client to build with? A functional test from the beginning with Guzzle?
- How should we handle authentication? Probably OAuth
- Best way to generate documentation for each endpoint?