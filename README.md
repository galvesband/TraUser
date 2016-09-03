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

Quedaría descargar las posibles dependencias de TraUserBundle. Aún tengo que pensar
en la mejor forma de hacer esto con `composer`. Como de momento no hay dependencias
realmente pues pasando...

Lo siguiente es configurar Symfony para que use traUserBundle.

 - Activar bundle
 
```php
public function registerBundles()
{
    $bundles = [
        // Bundles por defecto...
        // [...]
        new AppBundle\AppBundle(),
        // Añadir la siguiente línea
        new Galvesband\TraUserBundle\GalvesbandTraUserBundle(),
    ];

    // [...]
}
```

  - Importar configuración (config.yml)

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

 - Configurar firewalls y demás mierdas para que use las clases de TraUserBundle. Esta
 parte aún no está muy clara. De momento dejo esto como referencia futura, ya lo actualizaré
 cuando sepa exactamente cómo va la cosa.

```yaml
security:
  [...]
  encoders:
    Galvesband\TraUserBundle\Entity\User:
      algorithm: bcrypt
  
  providers:
    [...]
    tra_user_provider:
      entity:
        class: GalvesbandTraUserBundle:User
        property: name
  
  firewalls:
    [...]
    
    protected:
      pattern: ^/
      http_basic: ~
      provider: tra_user_provider
```

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

## Referencia ##

 - Método de trabajo: 
   http://stackoverflow.com/questions/21523481/symfony2-creating-own-vendor-bundle-project-and-git-strategy
   
 - Configuración de un usuario a mano:
   http://symfony.com/doc/3.1/security/entity_provider.html

 - Tutorial sobre crear un user bundle simplón (parecido a este en un momento dado):
   https://github.com/ponceelrelajado/loginBundle