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
      
    sonata.block.service.text:
    #sonata.block.service.rss:
      
    # Some specific block from the SonataMediaBundle
    #sonata.media.block.media:
    #sonata.media.block.gallery:
    #sonata.media.block.feature_media:
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
    prefix:   /admin
```

### Seguridad: autentificando con TraUserBundle ###

Esta parte es muy importante. Hay que hacer varias cosas, algunas específicas
para el TraUserBundle y otras que habría que hacerlas para cualquier proyecto
basado en Sonata.

#### Encoder y proveedor de usuarios ####

Esto es específico de TraUserBundle o de cualquier bundle que proporcione 
usuarios.

Se trata de establecer el hasher de contraseñas, por un lado. 

```yml
# security.yml
security:
  encoders:
    Galvesband\TraUserBundle\Entity\User: bcrypt
    # [...]
```

Y por el otro, definir un proveedor de usuarios:

```yml
# security.yml
security:
  # [...]
  providers:
    tra_user_provider:
      entity:
        class: GalvesbandTraUserBundle:User
        property: name
  # [...]
```

#### Firewalls ####

Esto hay que hacerlo en todo proyecto, se trate de TraUserBundle o no. Por supuesto
varía de un bundle de usuarios a otro y de la naturaleza y diseño del sitio. Aquí
muestro un buen ejemplo típico.

Por lo general se trata de permitir acceso anónimo a la parte general del sitio
y requerir autentificación para la parte de administración.

```yml
# security.yml
security:
  # [...]
  firewalls:
      # No requiere autentificación para recursos de desarrollo
      dev:
          pattern: ^/(_(profiler|wdt)|css|images|js)/
          security: false
    
      # Permite a usuarios no identificados acceso al login
      login_firewall:
          pattern: ^/admin/login$
          anonymous: ~
    
      # Zona de administración
      admin_firewall:
          # Todo lo que empiece por /admin
          pattern: ^/admin
          # Utilizamos el formulario de login de TraUserBundle
          form_login:
              # Estas rutas estan definidas en el archivo de enrutado de TraUserBundle
              login_path: /admin/login
              check_path: /admin/login_check
              csrf_token_generator: security.csrf.token_manager
              # Redirige al dashboard tras identificar con éxito.
              # Si el usuario acabó en el login redirigido desde una url protegida,
              # tras el login irá a la url protegida.
              default_target_path: sonata_admin_dashboard
          # Indicamos cómo cerrar sesión al componente de seguridad.
          logout:
              invalidate_session: false
              path: /admin/logout
              target: /
          # Establecemos nuestro proveedor de usuarios en el firewall
          provider: tra_user_provider
    
      # El sitio principal (todo lo demás)
      main:
          anonymous: ~
  
  # Aquí definimos los roles necesarios para que se permita el acceso.
  # Dependiendo del sitio esto puede cambiar, pero por lo general con esto basta.
  access_control:
      - { path: ^/admin/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
      - { path: ^/admin,       roles: ROLE_SONATA_ADMIN }
      
  # [...]
```

#### Jerarquía de roles ####

Esta es la parte más peliaguda. Además, puede cambiar dependiendo del esquema de
seguridad que se quiera implementar en el proyecto Symfony concreto.

Le podemos decir a Sonata qué acciones del CRUD de un determinado modelo o entidad 
puede o no hacer un usuario a través de los ROLES. Cambiando esto y la configuración 
de grupos y roles en la base de datos tendremos esquemas de seguridad totalmente 
diferentes.

Esta parte es una WIP; aún no tengo claro como permitir a un usuario acceder a
un formulario personalizado de cambio de contraseña, por ejemplo, y cosas así.
Pero a la hora de requerir roles sobre acciones de un CRUD, la cosa va así:

 - Hay que decirle a Sonata que cambie su manejador de seguridad de `noop`
   (permite a cualquier usuario hacer cualquier cosa) a otro. En el ejemplo
   que voy a desarrollar aquí voy a usar roles.

 - Hay que quedarse con el nombre del servicio que proporciona la clase `Admin`
   a sonata. En GalvesbandTraUserBundle, en el caso de los usuarios, es
   `galvesband.tra.user.admin.user`. Se pueden ver en el archivo
   `TraUserBundle/resources/config/services.yml`
   
 - Hay que convertir ese nombre de servicio en "prefijo de rol". Siguiendo con 
   el ejemplo de los usuarios sería `ROLE_GALVESBAND_TRA_USER_ADMIN_USER_`.
   
 - A ese "prefijo de rol" se le puede concatenar cualquiera de los siguientes 
   sufijos para formar el rol necesario para realizar una acción: `CREATE`
   `EDIT`, `DELETE`, `EXPORT`, `LIST`, `SHOW` y `VIEW`.

 - Por último, hay que crear una jerarquía de roles con esto en mente y que unifique
   los permisos de todos los bundles de Sonata en un esquema de seguridad para toda
   la aplicación.
   
Por ejemplo:

 - En la configuración de SonataAdmin:
 
```yml
sonata_admin:
    security:
        # Usar roles para decidir si un usuario tiene acceso a una determinada acción del CRUD.
        handler: sonata.admin.security.handler.role
```

 - En `security.yml` hay que definir una jerarquía de roles deacuerdo a lo mencionado
   más arriba:

```yml
security:
    # [...]
    
    # Por conveniencia recogemos aquí los roles de acceso a usuarios, grupos y roles
    # Y los condensamos en 3 perfiles básicos: USER, ADMIN y ROLESADMIN
    role_hierarchy:
        # Por conveniencia recogemos aquí los roles de acceso a usuarios, grupos y roles
        # Y los condensamos en 3 perfiles básicos: USER, ADMIN y ROLESADMIN
        ROLE_GALVESBAND_TRA_USER_USER:
            # Un USER podrá listar y ver detalles de everything (usuarios, grupos y roles)
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
            # Un ADMIN podrá crear, editar y borrar usuarios y grupos, pero no roles
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_DELETE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_DELETE
        ROLE_GALVESBAND_TRA_USER_ROLESADMIN:
            # Un ROLESADMIN podrá crear, editar y borrar roles
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_DELETE

        # A continuación definimos los auténticos roles que usará la aplicación.
        # En este esquema de ejemplo tendremos usuarios normales (staff), administradores
        # y super-admins. La idea es que haya usuarios que puedan trabajar en la zona de
        # administración sin tocar las cuentas de otros usuarios (STAFF). Los administradores
        # serán tipicamente los "dueños" del sitio. Podrán crear, editar y borrar usuarios.
        # Los super-admins podrán además modificar los roles.

        # Los que tengan Staff podrán entrar en la zona de administración (ROLE_SONATA_ADMIN)
        # y podrán listar y ver usuarios, grupos y roles.
        ROLE_STAFF: [ROLE_SONATA_ADMIN, ROLE_USER, ROLE_GALVESBAND_TRA_USER_USER]
        # Los administradores podrán crear, editar y borrar usuarios y grupos.
        ROLE_ADMIN: [ROLE_STAFF, ROLE_GALVESBAND_TRA_USER_ADMIN]
        # Los super-administradores podrán además crear, editar y borrar roles.
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_GALVESBAND_TRA_USER_ROLESADMIN, ROLE_ALLOWED_TO_SWITCH]
```

Aún quedan cosas por hacer. Por ejemplo, un ROLE_ADMIN es capaz de crear o eliminar 
ROLE_SUPER_ADMINs. O asignar roles SUPER_ADMIN a otro usuario. Para paliar esto 
estoy escribiendo mis propios manejadores de seguridad de Sonata. Todo lo de a
continuación es un WIP.

Para usarlos hay que poner esto en la configuración de sonata_admin:

```yml
sonata_admin:
  # [...]
  security:
    handler: galvesband.tra.user.security.handler.per_model_handler
```

Ese handler representa un servicio proporcionado por GalvesbandTraUserBundle.
Es muy simple: aplica un `security handler` u otro dependiendo de la clase
que se esté administrando. Si no tiene configuración para la clase sobre la
que se le pide permisos actua como el clásico manejador de seguridad por roles.

Lo que queda para que funcione es darle la configuración de manejadores de 
seguridad que tiene que usar para según qué clases. En principio la definición
del servicio le configura como posibilidades el manejador _noop_, el de
_roles_ y dos nuevos: _user_ y _group_ (propios). Hay que añadir al
`config.yml` algo como esto:

```yml
# config.yml
parameters:
    # [...]
    galvesband.tra.user.admin.security.handler_map:
        # Este es el único necesario porque por defecto usa role.
        # Es probable que en el futuro haya más handlers de seguridad
        # personalizados para grupos y roles.
        'Galvesband\TraUserBundle\Entity\User': user
        'Galvesband\TraUserBundle\Admin\UserAdmin': user
        'Galvesband\TraUserBundle\Entity\Group': group
        'Galvesband\TraUserBundle\Admin\GroupAdmin': group
        'Galvesband\TraUserBundle\Entity\Role': role
        'Galvesband\TraUserBundle\Admin\RoleAdmin': role
```

La idea que intentan implementar esto es que podamos aplicar un handler de 
seguridad por modelo o clase de administración. Y para TraUserBundle,
queremos que un usuario que no sea ROLE_SUPER_ADMIN NO pueda modificar o crear
usuarios ROLE_SUPER_ADMIN. A su vez será necesario que no pueda modificar o
crear un grupo que tenga ROLE_SUPER_ADMIN. Y solo un ROLE_SUPER_ADMIN debe poder
editar la tabla de roles.

Aún estoy en ello. 

#### Todo junto ####

A continuación una versión de `security.yml` con todo lo discutido, como referencia.

```yaml
security:
    role_hierarchy:
        # Por conveniencia recogemos aquí los roles de acceso a usuarios, grupos y roles
        # Y los condensamos en 3 perfiles básicos: USER, ADMIN y ROLESADMIN
        ROLE_GALVESBAND_TRA_USER_USER:
            # Un USER podrá listar y ver detalles de everything (usuarios, grupos y roles)
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
            # Un ADMIN podrá crear, editar y borrar usuarios y grupos, pero no roles
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_USER_DELETE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_GROUP_DELETE
        ROLE_GALVESBAND_TRA_USER_ROLESADMIN:
            # Un ROLESADMIN podrá crear, editar y borrar roles
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_CREATE
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_EDIT
            - ROLE_GALVESBAND_TRA_USER_ADMIN_ROLE_DELETE

        # A continuación definimos los auténticos roles que usará la aplicación.
        # En este esquema de ejemplo tendremos usuarios normales (staff), administradores
        # y super-admins. La idea es que haya usuarios que puedan trabajar en la zona de
        # administración sin tocar las cuentas de otros usuarios (STAFF). Los administradores
        # serán tipicamente los "dueños" del sitio. Podrán crear, editar y borrar usuarios.
        # Los super-admins podrán además modificar los roles.

        # Los que tengan Staff podrán entrar en la zona de administración (ROLE_SONATA_ADMIN)
        # y podrán listar y ver usuarios, grupos y roles.
        ROLE_STAFF: [ROLE_SONATA_ADMIN, ROLE_USER, ROLE_GALVESBAND_TRA_USER_USER]
        # Los administradores podrán crear, editar y borrar usuarios y grupos.
        ROLE_ADMIN: [ROLE_STAFF, ROLE_GALVESBAND_TRA_USER_ADMIN]
        # Los super-administradores podrán además crear, editar y borrar roles.
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

#### Bloque de ussuario logeado ####

Esto es para que cuando se identifique alguien en la parte de administración, arriba a la derecha
se muestre algún enlace interesante para el usuario como acceso al propio perfil o cerrar sesión.

```yml
# config.yml
sonata_admin:
    templates:
        user_block: GalvesbandTraUserBundle:blocks:user_block.html.twig
```

#### Correo ####

TraUserBundle puede enviar correos para permitir a un usuario que ha
olvidado su contraseña acceder al sistema o recuperar la contraseña.
Para eso necesita que _SwiftMailer_ este correctamente configurado.

En lo que respecta a desarrollo, sin embargo, no es necesario hacer
mucho; basta con descomentar una línea de `config_dev.yml`:

```yml
swiftmailer:
    disable_delivery: true
```

Con esta opción los correos no serán enviados realmente pero se los podrá
inspeccionar a traves de la barra de depuración. También existe la 
opción `delivery_address: me@example.com` de forma que el correo acabe
siempre en esa dirección.

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