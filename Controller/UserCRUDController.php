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

    // TODO QUIZÁS SEA MEJOR HACERLO MEDIANTE LOS EVENTOS preCreate, preYonoseque...

    // TODO: createAction
    //   Si usuario logeao no es ROLE_SUPER_ADMIN y algún grupo de los del nuevo usuario es ROLE_SUPER_ADMIN -> throw
    // TODO: editAction o update o lo que sea
    //   Si usuario logeao no es ROLE_SUPER_ADMIN y el usuario editado sí lo es -> Unauthorized
    //   Si usuario logeao no es ROLE_SUPER_ADMIN y entre los grupos del usuario editado esta ROLE_SUPER_ADMIN -> throw
    // TODO: batchDelete
    //   Si usuario logeao no es ROLE_SUPER_ADMIN y entre los usuarios a borrar hay algún ROLE_SUPER_ADMIN -> flash-error y redirect a list

    /**
     * Delete action.
     *
     * @param int|string|null $id
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     * @throws Exception If the type of the objects managed is not Galvesband\TraUserBundle\Entity\User or derived.
     */
    public function deleteAction($id)
    {
        $request = $this->getRequest();
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        if (!($object instanceof User)) {
            throw new Exception("This CRUD controller can only manage Galvesband/TraUserBundle/Entity/User instances.");
        }

        // If $object has ROLE_SUPER_ADMIN but we are not ROLE_SUPER_ADMIN throw
        if ($object->hasRole('ROLE_SUPER_ADMIN') && !$this->get('security.token_storage')->getToken()->getUser()->hasRole('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException("User can't delete a ROLE_SUPER_ADMIN user because he is not a ROLE_SUPER_ADMIN powered user.");
        }

        return parent::deleteAction($id);
    }
}