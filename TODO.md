# TODO #

## Seguridad ##

 - ~~Usuarios que no sean ROLE_SUPER_ADMIN no deben poder
           editar o borrar usuarios que sean ROLE_SUPER_ADMIN~~
 
 - ~~Usuarios que no sean ROLE_SUPER_ADMIN no deben poder 
           editar o borrar grupos que sean ROLE_SUPER_ADMIN.~~

 - Usuarios que no sean ROLE_SUPER_ADMIN no deben poder borrar 
   usuarios o grupos que sean ROLE_SUPER_ADMIN a través de 
   acciones BATCH.
   
 - Usuarios que no sean ROLE_SUPER_ADMIN no deben poder
   crear usuarios o grupos con rol ROLE_SUPER_ADMIN.
   - ~~No mostrar SuperGrupos o SuperRoles en combo~~
   - Lanzar Unauthorized cuando un nuevo usuario, grupo o rol
     es ROLE_SUPER_ADMIN si usuario logeado no es ROLE_SUPER_ADMIN.
     
 - No exportar la contraseña hasheada y el salt en EXPORT.
   
## Integración con Sonata ##

 - Bloque usuario arriba a la derecha
   - ~~Integrar con Sonata~~
   - Acceso a perfil
   - Acceso a Cambio de contraseña
   - Logout
     
 - Crear vistas 'show' para Usuarios, Grupos y Roles.

## Acciones ##

 - Cambio de contraseña para usuario logeado
 
 - Usuario: CREATE | EDIT: mostrar dos campos para la contraseña

 - Forgot password. 
   - Formulario que acepte nombre y email
   - Si es correcto, mandar a usuario correo con nuevo password aleatorio
   - Si no, disimular 

## Tests ##

 - All