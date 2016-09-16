<?php

namespace Galvesband\TraUserBundle\Admin;

use Galvesband\TraUserBundle\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class UserAdmin extends AbstractAdmin {

    // TODO: Crear un SecurityHandler nuevo que delegue en otro security handler según modelo, y si no tiene un
    // TODO  security handler para ese modelo que delegue en uno por defecto.

    // TODO: Crear un SecurityHandler para usuarios que tenga en cuenta la acción a realizar y el rol actual del usuario
    // TODO   Si usuario logeao no es ROLE_SUPER_ADMIN y el usuario sí lo es hay que denegar EDIT y DELETE... y BATCH si existe.

    protected $translationDomain = 'GalvesbandTraUserBundle';

    protected function configureFormFields(FormMapper $formMapper)
    {
        $repository = $this->getConfigurationPool()->getContainer()->get('doctrine')
            ->getManager()->getRepository('GalvesbandTraUserBundle:Group');
        $currentUser = $this->getConfigurationPool()->getContainer()->get('security.token_storage')
            ->getToken()->getUser();

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN'))
        {
            /* This query is used to populate the groups field. We don't want groups
               with ROLE_SUPER_ADMIN listed in there if the user is not ROLE_SUPER_ADMIN */
            $query = $repository->createQueryBuilder('g')
                ->innerJoin('g.roles', 'r')
                ->where('r.role <> :role_name')
                ->setParameter('role_name', 'ROLE_SUPER_ADMIN')
                ->getQuery();
        }
        else {
            $query = $repository->createQueryBuilder('g')->getQuery();
        }

        $formMapper
            ->with('Basic Information', ['class' => 'col-md-6'])
                ->add('name', 'text')
                ->add('email', 'text')
                ->add('isActive')
                ->add('groups', 'sonata_type_model', [
                    'class' => 'Galvesband\TraUserBundle\Entity\Group',
                    'multiple' => true,
                    'query' => $query
                ], [
                    'placeholder' => 'No group selected'
                ])
            ->end()

            ->with('Authentication', ['class' => 'col-md-6'])
                ->add('plainPassword', 'password', [
                    'required' => false,
                    'label' => 'Password',
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('email')
            ->add('isActive');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('email')
            ->add('isActive')
            ->add('groups')
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    /**
     * Convierte el objeto de entrada en su representación textual
     * @param \Galvesband\TraUserBundle\Entity\User|mixed $object
     * @return string
     */
    public function toString($object)
    {
        return $object instanceof \Galvesband\TraUserBundle\Entity\User
            ? $object->getName()
            : 'User';
    }
}