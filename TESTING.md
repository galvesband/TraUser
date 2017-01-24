# Testing TraUserBundle #

## Requirements ##

Pretty much composer and a recent php configured with the `pdo_sqlite` driver on.

## How to ##

*TraUserBundle* comes with its own embedded *Symfony* application to host the
execution of the test suite. You just need to install the requeriments via
`composer`:

```bash
$ composer install
```

This will install a few libraries in a `vendor` subdirectory, including `php-unit`.
The next step would be to execute `php-unit`. Beware that you will get an error
if `pdo_sqlite` is not properly configured; the test-suite makes use of SQLite
as the backend for Doctrine.

```bash
$ vendor/bin/phpunit
```

## About the Embedded Application ##

As stated earlier, the bundle comes with a pre-configured Symfony app inside 
its `Tests` directory. It actually lives in `Tests/test-app` and comes with
its own configuration files (`Tests/test-app/app/config`) and webroot.
You can use that app in development by just creating a database somewhere
and pointing the app's `parameters.yml` to it.

The internal app does not expose any public web, just an admin environment
with Sonata and traUser running on top. Check the configuration files and
routing to learn what urls you can check.

## Known issues in the test suite ##

The execution of the test suite will create a test database in the application's 
test cache and populate it before executing a test. Thanks to the 
`liip/functional-test-bundle` everything is pretty much magic. 

There is a catch. That bundle provides a *caching* feature to avoid recreating 
the entire database before every test. It works pretty well and actually speed up 
the test suite execution a lot, but be careful. Some tests depend on dates (like 
the password-recovery-token feature) and the caching logic might slip an outdated 
test database. In those cases just remove the entire testing cache directory:

```bash
$ rm Tests/test-app/var/cache/test/* -R
```