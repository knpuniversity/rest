Authentication
==============

1) Show how I can create a token on the web
2) Write a scenario against programmers for authentication
3) require authentication in a programmer controller
4) Finish up the entry point (with translator?)
5) Finish up listener and provider

6) Create an endpoint to create a token (scenario first)
7) Add in HTTP Basic

- update existing tests
- use real user when creating a programmer

Questions/TODOS
---------------

- add createdAt and notes to ApiToken
- duplicated logic in checking for valid JSON
- what about "notes" validation?

- need to mention HTTPs
- send proper response back if the username/password are blank/invalid
- since we didn't do that little extra bit that exposes the exception
    messages, are we accurately communicating clear messages if the
    user/password is blank?

- I hate how we have exception messages that can be thrown from all
    over the place, with their text hidden