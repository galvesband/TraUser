# A simple User bundle for Symfony #

Hey, `FOSUserBundle` seems so... powerful it is almost scary. Look at me, i'm a simple
user bundle! (try to think on this like in a Rick And Morty-ish background character voice).

## What does it do? ##

Users. It does users. 

Also, groups.

But what about security? Groups have roles and users hopefully have groups. Login forms
and that kind of funcionality is planned. Just that, planned.

## Requirements ##

I don't really know. Im using Symfony 3.1 with its standard distribution, and
I'm gonna require Doctrine for sure. Not sure what else.

## Corolary ##

I just want to learn how does Symfony work. A simple bundle for users seemed like a
good starting point. So keep in mind that...

**I don't really know what I'm doing. Act accordingly.**

## What to do if you want to use this Bundle? ##

Oh boy you are so fucked. 

I'll throw a hint at how composer.json should look in your proyect:

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

You'll have to replace `TBD` with a real url. I don't know where will I host this yet, tho.

# Configuración de un proyecto #

Hay que crear un proyecto Symfony 3.1 vacío y clonar el repositorio en 
`src/Galvesband/traUser/`.

Con eso hecho, hay que configurar el proyecto. Por un lado activar el bundle en
`AppKernel.php`, después añadir a `config.yml` la carga del `services.yml` del bundle
y del `routing.yml` y tal.

Por último, para que Symfony utilice el usuario para autentificación, de momento hay 
que añadir lo siguiente a `security.yml` (o algo parecido):

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