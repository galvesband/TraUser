# TODO #

## Seguridad ##

 - Usuarios que no sean ROLE_SUPER_ADMIN no deben poder
   crear usuarios o grupos con rol ROLE_SUPER_ADMIN.

   - Lanzar Unauthorized cuando un nuevo usuario, grupo o rol
     es ROLE_SUPER_ADMIN si usuario logeado no es ROLE_SUPER_ADMIN.

## Acciones ##

 - Cambio de contraseña para usuario logeado
   
## Integración con Sonata ##

 - Bloque usuario arriba a la derecha
   - Acceso a Cambio de contraseña
 
 - ~~Usuario: CREATE | EDIT: mostrar dos campos para la contraseña~~

## Comandos ##

 - AddUser
 
 - RemoveUser
 
 - AddGroup
 
 - RemoveGroup
 
 - AddRole
 
 - RemoveRole
 
 - AddUserToGroup
 
 - RemoveUserFromGroup
 
 - AddRoleToGroup
 
 - RemoveToleToGroup

## Estética ##

 - Pasada a login 
 - Pasada a reset-password
 - Pasada a recover-password
 - Pasada a email reset-password
   - html
   - txt

## Fixtures ##

 - Integrar Symfony / Doctrine / Fixtures en GalvesbandTraUserBundle
 - Preparar un set para arrancar un sitio con valores por defecto razonables
   - SuperAdministradores: Un gran poder conlleva una gran responsabilidad y toda esa mierda
   - Administradores: De todo menos tocar a SuperAdmins
   - Editores: Editar cualquier cosa que no sea otro usuario
   - Usuarios

## Tests ##

 - All