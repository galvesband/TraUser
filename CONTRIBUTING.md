# Contributing to TraUserBundle #

TODO

## Setting up the internal Symfony project for demo, development or testing ##

TraUserBundle comes with a Symfony project inside its `Test` directory. It
is useful for automatic testing, demo or development of the bundle. 

To make it run you need to perform the following steps:

 - Fill `/Tests/test-app/app/config/parameters.yml` with the parameters
   needed to connect to the database server. Look at README.md database instructions
   for an example using `docker-compose`.

 - Install dependencies, included development ones:
 
```bash
$ composer install
```

 - Install assets in the public directory of the test application:
 
```bash
$ Tests/test-app/bin/console assets:install Tests/test-app/web --symlink
```

 - The first time you access the data base there is no model. You need to
   create it first:

```bash
$ Tests/test-app/bin/console doctrine:schema:create
# Or if it is not the first time and there are changes in the model...
$ Tests/test-app/bin/console doctrine:schema:update --force
```

 - Add a super-admin user to enter the system if you don't have one.
 
```bash
$ Tests/test-app/bin/console galvesband_tra_user:add_command Rafa galvesband@gmail.com password
```

 - Start the development php server. After this step, the application should
   be available in [localhost:8000/admin](http://localhost:8000/admin). You
   can log in with the authentication data from the user created in the previous step.
 
```bash
$ Tests/test-app/bin/console server:start
```

# Testing #

Just copy phpunit.xml.dist to phpunit.xml. Also, SQLite will be needed at some point
to do some funcional tests.

PHPUnit is listed in the development requirements of TraUserBundle, so this should run
GalvesbandTraUserBundle's test suite:

```bash
$ vendor/phpunit/phpunit/phpunit
```
