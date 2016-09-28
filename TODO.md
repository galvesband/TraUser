# TODO #

## Commands ##

 - AddUser
   - Validate input

## Aesthetics ##

 - Pass to login 
 - Pass to reset-password
 - Pass to recover-password
 - Pass to reset-password email
   - html
   - txt
   
## Validation ##

 - Check asserts on 
    - users
    - groups
    - roles
    - tokens

## Fixtures ##

 - ~~Integrate Symfony / Doctrine / Fixtures in TraUserBundle's testing, 
   see [LiipFuncitonalTestBundle](https://github.com/liip/LiipFunctionalTestBundle).~~

## Tests ##

 - All
   - Functional
     - ~~Login~~
     - Logout
     - ~~All forgot password use case~~
     - User Admin shows the right fields for SUPER_ADMIN, ADMIN and USER
     - Deletes an user deletes its token (if it exists)
   - Unit
     - Security handlers
     - Whatever