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
[...]
    "require" : {
        [...]
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
`src/Galvesband/traUser/`.

```bash
# Crear proyecto sf
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

 - Importar enturado del bundle
 
```yaml
galvesband_tra_user:
    resource: "@GalvesbandTraUserBundle/Controller/"
    type:     annotation
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

## Referencia ##

 - Método de trabajo: 
   http://stackoverflow.com/questions/21523481/symfony2-creating-own-vendor-bundle-project-and-git-strategy
   
 - Configuración de un usuario a mano:
   http://symfony.com/doc/3.1/security/entity_provider.html

 - Tutorial sobre crear un user bundle simplón (parecido a este en un momento dado):
   https://github.com/ponceelrelajado/loginBundle