Authentication
==============

1) Create an endpoint to get tokens for users
2) Create somewhere visual to see these tokens, revoke them
3) Implement API authentication system
4) Add security to controllers
5) Updating tests

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