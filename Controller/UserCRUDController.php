<?php

namespace Galvesband\TraUserBundle\Controller;

use Exception;
use Galvesband\TraUserBundle\Entity\User;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserCRUDController extends CRUDController {
    // TODO: createAction
    //   Si usuario logeao no es ROLE_SUPER_ADMIN y algún grupo de los del nuevo usuario es ROLE_SUPER_ADMIN -> throw
    // TODO: editAction o update o lo que sea
    //   Si usuario logeao no es ROLE_SUPER_ADMIN y el usuario editado sí lo es -> Unauthorized
    //   Si usuario logeao no es ROLE_SUPER_ADMIN y entre los grupos del usuario editado esta ROLE_SUPER_ADMIN -> throw

    // TODO: Change password
}