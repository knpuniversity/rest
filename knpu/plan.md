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

- GET SHOW endpoint for each programmer - no links, no serialization - just
  getting raw programmers data and manually turning it into a JSON array

##### CHAPTER 4: Post, Show and Linking

Here we'll create a new "battle". We'll look up a projectId manually from
the web interface for our user.

- create a new battle (POST) /battles (send projectId and userId in body)
- create a GET /battles/{id} (update the previous POST to have Location header)
- make link back to the programmer from /battles/{id}
- maybe tease RMM level 3 as a segway into the next chapter

##### CHAPTER 5: Hal Basics

- RMM level 3 and "Hypermedia" concept
- Bring in Hal as a way of formalizing the data and links
- fix /battles/{id} to be HAL
- add a content-type to /battles/{id}
- fix the link on /battles/{id} to the progammer
- _self link (mention IANA more later with pagination)

##### CHAPTER 6: Using the HATEOAS library

- refactor links into HATEOAS library
- remove manual serialization logic and replace with HAL stuff
- fix /programmers/{id} to use HAL

##### CHAPTER 7: Hal Embedded resources

- fix /programmers
- add the embedded resources (battles)
- links versus embedded

##### CHAPTER 8: HATEOAS

- add 3 new links to /programmers/{id} for "next battles": 3 possible next
  projects that we could battle
- update our client to no longer create battles via a hardcoded projectId,
  but instead, by choosing one of these links and following it
- HATEOAS
- highlight what it doesn't do - HTTP method, fields, etc

##### CHAPTER 9: URL Structures

- create /programmers/{id}/battles to show the battles for this user
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

- add programmer "edit" PUT
- URL is to the exact resource we want to "replace"
- idempotency
- we're sending a "representation" of the resource, which the server uses
  to update the underlying resource

##### CHAPTER 14: Patch versus PUT

- mention PUT versus PATCH
- Perhaps allow PATCH /programmers/{id} as a valid endpoint

##### CHAPTER 15: Custom Endpoints

- and endpoint to make our programmer study - what should that look like?
- invent something: "studying" POST /programmers/{id}/study
- add another: "drinking coffee" POST /programmers/{id}/drink-caffeine
- add links for these
- idempotency again

##### CHAPTER 16: Custom Endpoint PUT

- custom PUT endpoint - PUT /programmers/{id}/avatar
- idempotency
- add link

##### CHAPTER 17: Allowing application/json submits

- Add some listener? (depends on how we're handling forms) that decodes
  the JSON body to to POST data if the reqeust type is application/json
- update our documentation to say we allow this

##### CHAPTER 18: DELETE

- add a delete endpoint
- introduce the 204 response

##### CHAPTER 19: Pagination

- add pagination links on programmers
- talk about IANA
- add some light details to our documentation

##### CHAPTER 20: Filtering

- add filtering on programmers
- add documentation about the standard way we'll allow things to be filtered
- we still don't know what fields can be used on any filter - that's a docs todo
- why query paramters? To avoid /programmers/{id}/page/{page}/name/{name}

##### CHAPTER 21: API Problems

- handling "problems" API Problem or vnd.error

##### CHAPTER 22: Form Validation errors

- add validation errors to the form (editing/creating programmer)
- handle 404's and other errors

### Topics

- when are we planning out the API design? RESTful Web APIs talks through
  a great process to help you figure out what resources you have and how
  you should link them
- Richardson's maturity model (of course) - but I'd rather show people first, then show this later
- documentation: what should be documented, and why. How can we generate it?
- curies
- Options method and Allow header
- templated links
- CORS
- profiles (for pointing to docs)
- caching
- authentication
- versioning?
- the API "homepage"
- creating an image link for the avatar of a programmer (showing that links
   can be to non/HAL-format resources)
- touch on more HTTP status codes as they should be talked about

### Questions

- Symfony versus non-Symfony? I think we should do non-Symfony and then
  do a Symfony-specific one, which ideally - wouldn't be too long. The
  biggest thing I know of that's different in Symfony is the presence of
  NelmioAPIDocBundle.
- How much of OAuth should we show? A different screencast?
- Best way to generate documentation for each endpoint?
- where to break this into pieces? This is at least 2 screencasts
