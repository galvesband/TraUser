[![Build Status](https://travis-ci.org/galvesband/TraUser.svg?branch=master)](https://travis-ci.org/galvesband/TraUser)

# TraUserBundle #

A Bundle for Symfony 3 and Sonata that provides users and groups.

The main developer of this bundle is Rafael Gálvez-Cañero (galvesband -at- gmail.com).
Whenever you see first person used in this documentation, that's the guy. 

The initial motivation of this project is to learn Symfony and Sonata. Along the way
I'm gonna try to build a reusable user-and-permission bundle usable in some of my future
projects, which is this bundle. To be more precise with the idea behind the bundle, I'm
trying to provide users, groups and roles, where groups link users and roles and roles
provide the permissions to do stuff to the users. 

Why another bundle instead of FOSUserBundle or Sonata's own UserBundle? Those bundles 
support very different use cases and are quite flexible, which is good, but makes them
a little bit too complex when you just want to build a simple yet dynamic web page.
Here I'm trying to simplify a lot of things just by fixating on a single persistence
backend (Doctrine) and limiting use-cases to just users and groups with roles. 

In the future the bundle might implement some new tricks, but I'm gonna try to keep
things relatively simple.


## Requirements ##

 - PHP. The bundle is being developed with PHP 7, although PHP 5.6 or later should work.

 - Symfony. I'm using stable release 3.1. Also I'm trying to
   avoid use of deprecated calls whenever possible, so that version is probably close to the 
   minimum required. My goal is to update the bundle to work with recent Symfony 
   versions up until a new LTS release of Symfony happens. We'll see where it ends.
    
 - Sonata and friends. I'm developing with `core-bundle` 3.1, `admin-bundle` 3.6 and 
 `doctrine-orm-admin-bundle` 3.0.
   
You can see the complete list of requirements in `composer.json`.


## Using the bundle ##

I want to make this bundle available through `packagist` but until then you will need to 
manually clone the repository or download a tarball.

For future reference, requiring this through `packagist` will look something like this.

```json
{
    "require" : {
        "SomeOther/Bundles" : "some-branch-or-version",
        "Galvesband/TraUserBundle" : "dev-master"
    },
    "repositories" : [{
        "type" : "vcs",
        "url" : "TBD" 
    }]
}
```

Skip to the section talking about creating a new empty project; there I describe the 
configuration process of an application from start to finish in a typical fashion.


### Parameters ###

You need to set up the proper _from_ address that will fill the `from` field
in emails sent from the application (for example, when an user forgot his password).
Look at `Tests/test-app/config/parameters.yml.dist`.

```yml
# parameters.yml
parameters:
    # [...]
    galvesband.tra_user.mail.from: some-address@not-real.net
```


# Developing TraUserBundle #

I've created an embedded Symfony application inside the `Tests/test-app` directory. TraUserBundle
is fully functional inside that application so it might be a good choice to develop the bundle.
Information about how to make it run is listed in `DEVELOPMENT.md`. There are also instructions
on how to run the test suite.


# The Test Suite #

The test suite is becoming decent lately. You can check how to run it in `TESTING.md`.


# Using TraUserBundle in a Symfony project #

## Configuring an empty Symfony project from the start ##

Here I will list the steps needed to build a Symfony project from the start to get to a 
point similar to the internal testing app. This might be useful for future projects of mine 
and also to document the bundle itself. These are the steps, more or less.

 - Create a new empty Symfony project.
 
 - Add and configure TraUserBundle's requirements.
 
 - Add and configure TraUserBundle.
 
 - Set up a database for the project.
 
 - Start it up.
 
In what follows I will tell you to manually add requirements to `composer.json`.
This is probably not needed because those are already listed in TraUserBundle's `composer.json`
file, but right now TraUserBundle is not included in any `packagist` repo and this is a
manual installation.

### Create a new empty Symfony project ###
 
```bash
$ composer create-project symfony/framework-standard-edition traUser "3.1.*"
```

### Add and configure TraUserBundle's requirements ###

#### Sonata Core Bundle ####

[Reference](https://sonata-project.org/bundles/core/master/doc/reference/installation.html).

First, we need to add to the `composer.json` of our project this requirements:

 - `sonata-project/core-bundle : "3.1.*"`
 
 - `twig/extensions : 1.3.*`, which seems to be needed but not included in the requirements of `sonata-core`.
   Version 1.3 seems to work well.

Then we need to enable SonataCoreBundle:

```php
    // app/AppKernel.php
    public function registerBundles() {
        $bundles = [
            // [...]
            // Sonata stuff
            new Sonata\CoreBundle\SonataCoreBundle(),

            new AppBundle\AppBundle(),
            // [...]
        ];
        // [...]
    }
```

The default configuration for `sonata_core` seems to work fine but it is a good practice
to add its entry in the configuration:

```yaml
# app/config/config.yml
sonata_core: ~
```


#### Sonata Admin Bundle ####

[Reference](https://sonata-project.org/bundles/admin/3-x/doc/index.html).

This means actually some bundles:

 - SonataAdminBundle: the core of the administration framework of Sonata.
 
 - SonataDoctrineORMAdminBundle: SonataAdminBundle supports different persistence
   layers, but TraUserBundle is fixed on Doctrine. 

These translates to this lines in `composer.json`:

 - `"sonata-project/admin-bundle" : "3.6.*"`
 
 - `"sonata-project/doctrine-orm-admin-bundle" : "3.0.*"`
 
Those requirements will suck other required bundles themselves as needed.

Next is enabling and setting up the bundles. We need to touch `AppKernel.php` again:

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // [...]
            
            // Sonata Admin requirements
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            // This is a requirement of SonataAdminBundle, we need it too
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            
            // Sonata Admin
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            
            // Other stuff, like... TraUserBundle
            new Galvesband\TraUserBundle\GalvesbandTraUserBundle(),
                        
            new AppBundle\AppBundle(),
            // [...]
        ];
        // [...]
    }
    // [...]
}
```

SonataAdminBundle uses SonataBlockBundle to render stuff in blocks. This apparently means we just
need to inform SonataBlockBundle of the existence of some blocks:

```yaml
# app/config/config.yml
sonata_block:
  default_contexts: [cms]
  blocks:
    # Main block
    sonata.admin.block.admin_list:
      contexts: [admin]
    
    # Search results blocks
    sonata.admin.block.search_result:
      contexts:   [admin]
      
    sonata.block.service.text:
    #sonata.block.service.rss:
      
    # Some specific block from the SonataMediaBundle
    #sonata.media.block.media:
    #sonata.media.block.gallery:
    #sonata.media.block.feature_media:
```

If we want internationalization (which I usually want as my clients are mainly from Spain) we should
enable the Symfony translation component. 
[Reference here](http://symfony.com/doc/current/book/translation.html#book-translation-configuration).

```yaml
# app/config/config.yml
framework:
  translator: { fallbacks: ["es_ES", "en"] } 
```

Now, setting up Sonata's routing system:

```yaml
# app/config/routing.yml
# This sets up main Sonata's routes
admin_area:
  resource: "@SonataAdminBundle/Resources/config/routing/sonata_admin.xml"
  prefix: /admin
  
# This one generates routes on runtime for the `Admin` classes of Sonata.
_sonata_admin:
  resource: .
  type: sonata_admin
  prefix: /admin
```


#### RandomLib ####

TraUserBundle leverages on [RandomLib](https://github.com/ircmaxell/RandomLib), versión 1.2.*
to generate reset-password tokens and password when a random one is needed. Add this to 
`composer.json`.

 - `"ircmaxell/random_lib":"1.2.*"`


### Add and configure TraUserBundle ###

In the furute TraUserBundle will (probably) be available through `packagist`. In the mean time
we need to clone its repository manually some place our project will work with. There are several options:

 - Clone elsewhere and link into the project OR clone directly into the project (maybe as a git sub-module).
 
 - Set it up in `src/Galvesband/TraUserBundle` OR in `vendor/Galvesband/TraUserBundle`. I think it will
   work well in both places. 
 
Whatever you do, this are the steps needed to make Sonata and TraUserBundle work together.


#### Enabling the bundle ####
 
 ```php
     public function registerBundles() {
         $bundles = [
             // [...]
             // Sonata stuff
             new Sonata\CoreBundle\SonataCoreBundle(),
             // [...]
             
             // Añadir la siguiente línea
             new Galvesband\TraUserBundle\GalvesbandTraUserBundle(),
 
             new AppBundle\AppBundle(),
             // [...]
         ];
         // [...]
     }
 ```


#### Importing configuration and routing ####

 - Configuration:

```yaml
# app/config/config.yml
imports:
    - { resource: parameters.yml }
    - { resource: security.yml }
    - { resource: services.yml }
    # TraUserBundle's services
    - { resource: "@GalvesbandTraUserBundle/Resources/config/services.yml" }

# [...]

# Doctrine Configuration
doctrine:
    dbal:
        driver:   pdo_mysql
        host:     "%database_host%"
        port:     "%database_port%"
        dbname:   "%database_name%"
        user:     "%database_user%"
        password: "%database_password%"
        # Symfony and MySQL good practice: recent versions of MariaDB and MySQL supports
        # utf8mb4 collation, which supports 4 bytes unicode
        charset:  utf8mb4
        default_table_options:
            charset: utf8mb4
            collate: utf8mb4_unicode_ci
```

 - Routing
 
```yaml
# app/config/routing.yml
galvesband_tra_user:
    resource: "@GalvesbandTraUserBundle/Controller/"
    type:     annotation
    # Use the prefix you want
    prefix:   /admin
```


#### Security: Authenticating with TraUserBundle ####

This is a quite important step. Some of the next actions are specific for
TraUserBundle and others are needed by anything based upon Sonata.


##### Password hasher and user provider #####

Specific for TraUserBundle or any bundle that provides users. We need to
set up the hasher for password on one side: 

```yml
# app/config/security.yml
security:
  encoders:
    Galvesband\TraUserBundle\Entity\User: bcrypt
    # [...]
```

On the other side we need to set up our user provider:

```yml
# app/config/security.yml
security:
  # [...]
  providers:
    tra_user_provider:
      entity:
        class: GalvesbandTraUserBundle:User
        property: name
  # [...]
```


##### Firewalls #####

This is configuration step needed in any Symfony project. It is where we tell Symfony
where we need authenticated users and where we allow anonymous one, and it also tells
symfony where the authenticated users are allowed and where not.

This of course is very different from project to project, independently of TraUserBundle.
The next bits implements a tipical example. Usually we need anonymous access to the public
parts of the site and require authentication for the administration zone.

```yml
# app/config/security.yml
security:
  # [...]
  firewalls:
      # Don't require authentication for development related assets
      dev:
          pattern: ^/(_(profiler|wdt)|css|images|js)/
          security: false
    
      # Allows anonymous users to the admin's login route
      login_firewall:
          pattern: ^/admin/login$
          anonymous: ~
    
      # Admin zone firewall
      admin_firewall:
          # Everything that begins with /admin
          pattern: ^/admin
          # Use the login form from TraUserBundle
          form_login:
              # The following routes are from TraUserBundle
              login_path: /admin/login
              check_path: /admin/login_check
              csrf_token_generator: security.csrf.token_manager
              # Redirects to Sonata's dashboard after a successful authentication.
              # If the user ended up in the login form redirected from a protected url
              # he will be redirected to that initial url after a successfull login.
              default_target_path: sonata_admin_dashboard
          # Tell security component how to close a session
          logout:
              invalidate_session: false
              path: /admin/logout
              target: /
          # Set up our user provider for this firewall
          provider: tra_user_provider
    
      # Everything else
      main:
          anonymous: ~
  
  # Here we define the needed roles to be allowed in different urls. It is the first
  # security layer.
  access_control:
      - { path: ^/admin/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
      - { path: ^/admin,       roles: ROLE_SONATA_ADMIN }
      
  # [...]
```


##### Role hierarchy #####

This a very TraUserBundle and Sonata specific step and depends entirely on the security
scheme you want to implement in the application.

We can tell Sonata which actions from the CRUD controller of a given entity or
model can the user do or not based on ROLES. Changing this and the group and role
configuration in TraUserBundle will change the security in very different ways.

Here a showcase a particular scheme that I think will be useful in my future projects:

 - We need to tell Sonata which security handler we want. By default it uses `noop`,
   which basically allows everything. It supports two main schemes: `role` and `acl`.
   `acl` is too much for my projects and `roles` fit almost perfectly. TraUserBundle
   provides a few new security handlers that modify slightly the behaviour of `role`
   and are the ones being used in this example.

 - The roles used by Sonata will be derived for a particular entity CRUD from the
   name of the service that provides the `Admin` class for that entity. You can
   peek those in `TraUserBundle/resources/config/services.yml`. 
   
 - The actual roles user by sonata will be prefixed uppercasing the service name.
   For example, for the `User` entity the `Admin` class is provided by the service 
   `galvesband.tra.user.admin.user`, so the prefix will be something like
   `ROLE_GALVESBAND_TRA_USER_ADMIN_USER_`.
 
 - After that prefix one of the following strings can be concatenated to build
   a full role: `CREATE` `EDIT`, `DELETE`, `EXPORT`, `LIST`, `SHOW` and `VIEW`.

 - Lastly, we need to create a role hierarchy with all this sub-roles in mind that
   unifies permissions for all the bundles that work under Sonata in a coordinated
   security scheme.
   
An example for roles without our custom handlers yet:

 - In SonataAdmin configuration:
 
```yml
# app/config/config.yml
sonata_admin:
    security:
        # Use roles to decide if an user has access to a given CRUD action
        handler: sonata.admin.security.handler.role
```

 - And a role hierarchy following the upper rules. We want to allow USER read
   access to everything, ADMIN to be able to edit users and groups and 
   ROLESADMIN to be able to edit roles:

```yml
# app/config/security.yml
security:
    # [...]
    
    # For convenience we group here roles for users, groups and roles entities into
    # 3 big profiles: USER, ADMIN and ROLESADMIN.
    role_hierarchy:
        ROLE_GALVESBAND_TRA_USER_USER:
            # An USER will be able to list and see details of everything (users, groups and roles)
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_LIST
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_VIEW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_SHOW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_EXPORT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_LIST
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_VIEW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_SHOW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_EXPORT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_LIST
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_VIEW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_SHOW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_EXPORT
        ROLE_GALVESBAND_TRA_USER_ADMIN:
            # An ADMIN will be able to create, edit and delete users and groups, but not roles
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_DELETE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_DELETE
        ROLE_GALVESBAND_TRA_USER_ROLESADMIN:
            # A ROLESADMIN will be able to create, edit and delete roles
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_DELETE

        # Now we define the real roles the group entities will use (through the role entities).
        # In this example scheme we will have normal users (staff), admins and super-admins.
        # We want users to be allowed to enter in the admin zone and edit the site's content
        # but not screwing up with other users accounts. Admins will ussually be the "owners" or
        # clients of the site. They will be able to create, edit and delete users and groups.
        # Finally, super-admins are allowed to modify the role entities, which in the end map
        # what a group can do.
        
        # Staffers will be allowed into Admin zone (ROLE_SONATA_ADMIN) and can list and 
        # see users, groups and roles.
        ROLE_STAFF: [ROLE_SONATA_ADMIN, ROLE_USER, ROLE_GALVESBAND_TRA_USER_USER]
        # Admins will be able to create, edit and delete users and groups
        ROLE_ADMIN: [ROLE_STAFF, ROLE_GALVESBAND_TRA_USER_ADMIN]
        # SuperAdmins in addition will be able to create, edit and delete roles
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_GALVESBAND_TRA_USER_ROLESADMIN, ROLE_ALLOWED_TO_SWITCH]
```

But there is still some stuff missing. A ROLE_ADMIN will be able to create or delete a 
ROLE_SUPER_ADMIN, or assign ROLE_SUPER_ADMIN to other user. To work around this I writed
a few custom security handlers. To use them we need to make the following changes to the previous
security configuration:

```yml
# app/config/config.yml
sonata_admin:
  # [...]
  security:
    # Our security handler 
    handler: galvesband.tra.user.security.handler.per_model_handler
```

That handler service is provided by TraUserBundle and is very simple. It relies on
other security handlers to decide it some action is allowed, based on the type
of the object being secured. Internally has a map of handler's name and handlers
on one hand and a map of type's name and handler's name on the other so when
he need to decide if a given action is allowed for some object it matches the object's
type with a handler's name and then the name to a handler, deriving the inquiry to it.
If no handler is found for that particular type it uses the `role` security handler
as a fall-back.

So this handler allows us to use a different handler for different entities or admins.
If we don't set up a handler for a particular type it will act as the `role` security
handler. What we lack is custom security handlers that disallow some actions when the
object secured is UserAdmin, User, GroupAdmin or Group, which are the ones which need
special rules.

The only thing we need to make it work after that is to provide our per-sole handler
with the map of types and handlers. The service definition sets up a parameter for that, 
so to set it up we need to add this to our configuration:

```yml
# app/config/config.yml
parameters:
    # [...]
    galvesband.tra.user.admin.security.handler_map:
        # If object is User or UserAdmin use our user security handler
        'Galvesband\TraUserBundle\Entity\User': user
        'Galvesband\TraUserBundle\Admin\UserAdmin': user
        # If object is Group or GroupAdmin use our group security handler
        'Galvesband\TraUserBundle\Entity\Group': group
        'Galvesband\TraUserBundle\Admin\GroupAdmin': group
        # We need a default entry to use when object is not one of those things
        'default': role
```

See the definition of the per-role security handler service in 
`Galvesband/TraUserBundle/resources/config/services.yml` for more information. 


##### All together #####

Next is a version of `security.yml` with everything discussed up, as reference.

```yaml
security:
    role_hierarchy:
        ROLE_GALVESBAND_TRA_USER_USER:
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_LIST
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_VIEW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_SHOW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_EXPORT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_LIST
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_VIEW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_SHOW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_EXPORT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_LIST
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_VIEW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_SHOW
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_EXPORT
        ROLE_GALVESBAND_TRA_USER_ADMIN:
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_DELETE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_DELETE
        ROLE_GALVESBAND_TRA_USER_ROLESADMIN:
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_DELETE

        ROLE_STAFF: [ROLE_SONATA_ADMIN, ROLE_USER, ROLE_GALVESBAND_TRA_USER_USER]
        ROLE_ADMIN: [ROLE_STAFF, ROLE_GALVESBAND_TRA_USER_ADMIN]
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_GALVESBAND_TRA_USER_ROLESADMIN, ROLE_ALLOWED_TO_SWITCH]

    encoders:
        Galvesband\TraUserBundle\Entity\User: bcrypt

    providers:
        tra_user_provider:
            entity:
                class: GalvesbandTraUserBundle:User
                property: name

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        login_firewall:
            pattern: ^/admin/login$
            anonymous: ~

        admin_firewall:
            pattern: ^/admin
            form_login:
                login_path: /admin/login
                check_path: /admin/login_check
                csrf_token_generator: security.csrf.token_manager
                default_target_path: sonata_admin_dashboard
            logout:
                invalidate_session: false
                path: /admin/logout
                target: /
            provider: tra_user_provider

        main:
            anonymous: ~

    access_control:
        - { path: ^/admin/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin,       roles: ROLE_SONATA_ADMIN }

```


##### Logged in user block in Sonata's admin zone #####

In Sonata everything is ready to work with the SonataUserBundle, which is awesome, but we need to
set up an special entry in sonata's configuration to tell it to use the user block from
TraUserBundle or we won't see anything in the top menu where the user menu is exposed.
To do that modify the configuration of sonata like this:

```yml
# app/config/config.yml
sonata_admin:
    templates:
        user_block: GalvesbandTraUserBundle:blocks:user_block.html.twig
```


##### Mail #####

TraUserBundle uses email to allow an user that has forgotten its password to generate
a new random one. For that it need _SwiftMailer_ to be correctly set up. For development,
it is enough to set this up:

```yml
# app/config/config_dev.yml
swiftmailer:
    disable_delivery: true
```

With this configuration email will not be really sent but will be accessible through
Symfony's profiler and debug bar. There is also an option to set up a forced
delivery address: `delivery_address: me@example.com`.


### Set up a database for the project ###

What I usually do in development is use `docker-compose`. I create a directory `docker`
somewhere, usually inside the Symfony project, and inside another one called
`traUser-database-only` or something like that with a `docker-compose.yml` file
like this:
 
```yaml
version: '2'

# A single container with the database server
services:
db:
 image: mariadb:10.1
 volumes:
   # Database data in a local file-system volume.
   # Delete the directory to reset the database
   # (or... you know, drop and recreate the database)
   - "./data/db:/var/lib/mysql"
 restart: always
 environment:
   # Connection information
   MYSQL_ROOT_PASSWORD: changeme
   MYSQL_USER: traUser_user
   MYSQL_PASSWORD: traUser_pwd
   MYSQL_DATABASE: traUser_db
 ports:
   # Database access through localhost:3306
   - "127.0.0.1:3306:3306"
```
 
 - Switch to that directory and do:
  
```bash
$ docker-compose up -d
```

 - Configure Symfony by creating or editing `app/config/parameters.yml` with this parameters
   (change it to your database if you are not using my docker solution):

```yaml
parameters:
    database_host: 127.0.0.1
    database_port: 3306
    database_name: traUser_db
    database_user: traUser_user
    database_password: traUser_pwd
    mailer_transport: smtp
    mailer_host: 127.0.0.1
    mailer_user: null
    mailer_password: null
    # Put something *random* here
    secret: blahblah-some-secret
```

And that's it. All that is left is a couple calls standard to any Symfony project.


### Start up ###

```bash
# First time
$ php bin/console doctrine:schema:create
# Or updating the schema
$ php bin/console doctrine:schema:update --force

$ Importing assets to web directory
$ php bin/console assets:install --symlinks

# Start php development server
$ php bin/console server:start
```

The application should be accessible through 
[localhost:8000](http://localhost:8000). Go to `/admin/login` to see the login form.
Users, groups and roles are empty. To add an user use the command provided by
TraUserBundle:

```bash
$ php bin/console galvesband:tra-user:add-user --super MyUserName my-email@somehost.com password
```

The you will need to set up roles and groups.
