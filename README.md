# TraUserBundle #

Un Bundle para Symfony 3 que proporciona una implementación no demasiado complicada
de usuarios y grupos.

La idea es tener usuarios y grupos. Los usuarios en sí mismo no tienen permisos
asociados, pero pertenecen a grupos, y estos si tienen permisos asociados (roles).

## Usar el proyecto ##

No lo llamaría "usable" de momento... pero en fin. Al final del `readme.md` he incluído
unos enlaces con información que puede resultar útil. Hay varios sistemas, la cosa es
encontrar uno que no sea un coñazo.

La opción más tentadora es usar `composer` y `packagist` para añadir el bundle como
uno más y después simplemente requerirlo en los proyectos que lo necesitamos. También
se puede añadir al `composer.json` información sobre el paquete directamente desde
un VCS, pero eso supongo que implicaría tener acceso desde el servidor a ese VCS,
cosa que no parece sencilla  con nuestro actual despliegue en 
[Galvesband's](https://galvesband.ddns.info/code/).

```json
    "require" : {
        "SomeOther/Bundles" : "some-branch-or-version",
        "Galvesband/TraUserBundle" : "dev-master"
    },
    "repositories" : [{
        "type" : "vcs",
        "url" : "TBD" 
    }],
```

Habría que reemplazar `TBD` con la url pública del repositorio.

# Configuración de un proyecto para desarrollo #

En resumen estos son los pasos:

 - Crear un proyecto Symfony vacío.
 
 - Descargar el código de TraUserBundle.
 
 - Instalar y configurar en el proyecto las dependencias de TraUserBundle.
 
 - Configurar TraUserBundle
 
 - Configurar la conexión a la base de datos del proyecto.
 
 - Puesta en marcha.

## Proyecto para desarrollar el bundle dentro ##

Hay que crear un proyecto Symfony 3.1 vacío y clonar el repositorio en 
`src/Galvesband/TraUserBundle/`.

```bash
# Crear proyecto Symfony
$ composer create-project symfony/framework-standard-edition traUser "3.1.*"

# Clonar repositorio de traUser para desarrollo
$ cd traUser/src
$ mkdir Galvesband
$ cd Galvesband
$ git clone ssh://git@galvesband.ddns.info:10022/galvesband/traUser.git TraUserBundle 
```

## Requerimientos de TraUserBundle ##

### Sonata Core Bundle ###

[Referencia](https://sonata-project.org/bundles/core/master/doc/reference/installation.html).

Hay que añadir a `composer.json` los siguientes requerimientos:

 - `sonata-project/core-bundle : "3.1.*"`
 
 - `twig/extensions : 1.3.*`, que parece ser necesario aunque no está
   incluido en los requerimientos de SonataCoreBundle. *Parece* que la 
   versión `1.3` funciona bien con Sonata.

Hay que activar el SonataCoreBundle, añadiendo la siguiente línea al `app/AppKernel.php`:

```php
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

Aunque no parece necesario si no se toca la configuración por defecto, 
puede ser buena idea incluir esto en `app/config/config.yml`:

```yaml
sonata_core: ~
```

### Sonata Admin Bundle ###

[Referencia](https://sonata-project.org/bundles/admin/3-x/doc/index.html).

Esto implica varios bundles:

 - SonataAdminBundle: núcleo del entorno de administración de Sonata.
 
 - SonataDoctrineORMAdminBundle: integra Doctrine en Sonata. Si 
   usaramos otro almacenamiento como Mongo o Propel habría que
   usar otro Bundle, pero TraUser usa Doctrine. 

Lo cual se traduce en añadir los siguientes requerimientos al proyecto Symfony:

 - `"sonata-project/admin-bundle" : "3.6.*"`. Esto introducirá una serie
   de Bundles nuevos en el proyecto, requeridos por SonataAdmin.
 
 - `"sonata-project/doctrine-orm-admin-bundle" : "3.0.*"`

Tras ello habrá que configurar los nuevos bundles.

#### Activando los Bundles ####

Hay que añadir el siguiente código a `app/AppKernel.php`:

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

#### Configurando los Bundle ####

El administrador usa el SonataBlockBundle para ponerlo todo en bloques. Esto aparentemente
significa que sólo tenemos que decirle al SonataBlockBundle que existe el bloque Admin, 
o algo así, en `app/config/config.yml`.

```yaml
sonata_block:
  default_contexts: [cms]
  blocks:
    sonata.admin.block.admin_list:
      contexts: [admin]
```

#### Activando Symfony translator ####

SonataAdmin requiere el componente de traducción. Hay documentación 
[aquí](http://symfony.com/doc/current/book/translation.html#book-translation-configuration).

Basicamente, es ir a `app/config/config.yml` y editar o añadir lo siguiente:

```yaml
framework:
  translator: { fallbacks: ["es_ES", "en"] } 
```

#### Configurando el enrutado de Admin ####

Hay que editar `app/config/routing.yml` y añadir lo siguiente:

```yaml
admin_area:
  resource: "@SonataAdminBundle/Resources/config/routing/sonata_admin.xml"
  prefix: /admin
```

Además, SonataAdminBundle genera rutas al vuelo para las clases de tipo `Admin`. Para que
funcione hay que asegurarse de que el cargador de enrutado de SonataAdminBundle se ejecuta:

```yaml
# app/config/routing.yml

_sonata_admin:
  resource: .
  type: sonata_admin
  prefix: /admin
```

#### Último paso - Caché y assets ####

Teoricamente después de instalar bundles lo suyo es hacer esto. En
este caso sobre todo lo de los assets.
 
```bash
$ bin/console cache:clear
$ bin/console assets:install --symlink
```

**Nota:** Recuerda que si usas PhpStorm puede convenir refrescar
el comando Symfony en el entorno porque habrá nuevos comandos de Sonata.

**Nota:** Tras esto el entorno de administración debería ser accesible en
[localhost:8000/admin](http://localhost:8000/admin) (suponiendo que uses el
servidor web de php).

## Configurando TraUserBundle ##

Lo siguiente es configurar Symfony para que use traUserBundle.

### Activando el bundle ###
 
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

### Importando la configuración y el enrutado ###

 - Configuración

```yaml
imports:
    - { resource: parameters.yml }
    - { resource: security.yml }
    - { resource: services.yml }
    # Cargar servicios de TraUserBundle:
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
        # Si es posible (MySQL o MariaDB son versiones razonablemente recientes)
        # usar utf8mb4 como collation (soporte unicode de 4 bytes)
        charset:  utf8mb4
        default_table_options:
            charset: utf8mb4
            collate: utf8mb4_unicode_ci
```

 - Importar enrutado del bundle
 
```yaml
galvesband_tra_user:
    resource: "@GalvesbandTraUserBundle/Controller/"
    type:     annotation
    # Usar el prefijo que convenga para el desarrollo y testeo
    prefix:   /
```

### Seguridad: autentificando con TraUserBundle ###

Hay que configurar firewalls y demás mierdas para que use las clases de TraUserBundle. Esta
parte aún no está muy clara. De momento dejo esto como referencia futura, ya lo actualizaré
cuando sepa exactamente cómo va la cosa.

```yaml
# To get started with security, check out the documentation:
# http://symfony.com/doc/current/book/security.html
security:
    encoders:
        Galvesband\TraUserBundle\Entity\User: bcrypt

    # http://symfony.com/doc/current/book/security.html#where-do-users-come-from-user-providers
    providers:
        tra_user_provider:
            entity:
                class: GalvesbandTraUserBundle:User
                property: name

    firewalls:
        # disables authentication for assets and the profiler, adapt it according to your needs
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        # Allows anonymous users on login form
        login_firewall:
            pattern: ^/admin/login$
            anonymous: ~

        # Requires full authentication for anything starting with /admin
        # Uses TraUserBundle's login form
        admin_firewall:
            pattern: ^/admin
            form_login:
                login_path: /admin/login
                check_path: /admin/login_check
                csrf_token_generator: security.csrf.token_manager
                # Redirects to dashboard on success by default
                # (If user tried to access another protected url then redirects there)
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

    role_hierarchy:
        ROLE_SONATA_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
        ROLE_ADMIN: [ROLE_USER]
```

## Configurando la conexión a la base de datos ##

Por último, hay que configurar la conexión de base de datos del proyecto.
Lo que yo personalmente suelo hacer es usar `docker`:

 - Crear directorio en algún lugar, da igual dónde (yo suelo usar
 un subdirectorio `docker` dentro del proyecto Symfony) llamado (por ejemplo) 
 `traUser-database-only` y dentro un archivo con nombre `docker-compose.yml` 
 con el siguiente contenido:
 
```yaml
version: '2'

# Un único contenedor para proveernos de una base de datos 
services:
db:
 image: mariadb:10.1
 volumes:
   # Guardamos los volúmenes de la base de datos en ./data.
   # Borra el directorio para resetear las bases de datos
   - "./data/db:/var/lib/mysql"
 restart: always
 environment:
   # Datos de conexión
   MYSQL_ROOT_PASSWORD: changeme
   MYSQL_USER: traUser_user
   MYSQL_PASSWORD: traUser_pwd
   MYSQL_DATABASE: traUser_db
 ports:
   # Acceso a la base de datos a través de localhost:3306
   - "127.0.0.1:3306:3306"
```
 
 - cambiar a ese directorio y ejecutar:
  
```bash
$ docker-compose up -d
```

 - Configurar Symfony creando o editando el archivo `app/config/parameters.yml` con estos parámetros:

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
    # Cambiar por algo aleatorio
    secret: blahblah-some-secret
```

Ya esta todo. Lo que queda son los pasos que habría que seguir en todo
proyecto Symfony para crear/actualizar el esquema de la base de datos, 
etcétera:

```bash
$ php bin/console doctrine:schema:create
```

## Lanzando el proyecto ##

Con la base de datos andando, lo mejor es usar el servidor de desarrollo
de php, lo que es muy fácil porque hay un comando de Symfony para ello:

```bash
$ app/console server:start
```

La aplicación debe estar disponible en 
[localhost:8000](http://localhost:8000).

## Referencia ##

 - Método de trabajo: 
   http://stackoverflow.com/questions/21523481/symfony2-creating-own-vendor-bundle-project-and-git-strategy
   
 - Configuración de un usuario a mano:
   http://symfony.com/doc/3.1/security/entity_provider.html

 - Tutorial sobre crear un user bundle simplón (parecido a este en un momento dado):
   https://github.com/ponceelrelajado/loginBundle