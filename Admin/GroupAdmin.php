<?php

namespace Galvesband\TraUserBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class GroupAdmin extends AbstractAdmin {

    protected $translationDomain = 'GalvesbandTraUserBundle';

    protected function configureFormFields(FormMapper $formMapper) {
        $repository = $this->getConfigurationPool()->getContainer()->get('doctrine')
            ->getManager()->getRepository('GalvesbandTraUserBundle:Role');
        $currentUser = $this->getConfigurationPool()->getContainer()->get('security.token_storage')
            ->getToken()->getUser();

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            /* This query is used to populate the roles field. We don't want
               ROLE_SUPER_ADMIN listed in there if the user is not ROLE_SUPER_ADMIN */
            $query = $repository->createQueryBuilder('r')
                ->where('r.role <> :role_name')
                ->setParameter('role_name', 'ROLE_SUPER_ADMIN')
                ->getQuery();
        }
        else {
            $query = $repository->createQueryBuilder('r')->getQuery();
        }

        $formMapper
            ->with('Basic Information')
                ->add('name', 'text')
                ->add('description', 'text')
            ->end()
            ->with('Roles')
                ->add('roles', 'sonata_type_model', [
                    'class' => 'Galvesband\TraUserBundle\Entity\Role',
                    'multiple' => true,
                    'query' => $query
                ])
            ->end();
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('description')
            ->add('users', null, [
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('roles', null, [
                'route' => [
                    'name' => 'show'
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
            ->add('name')
            ->add('description')
            ->add('roles');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
            ->addIdentifier('name', null, [
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('description')
            ->add('roles', null, [
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);

        return $actions;
    }

    public function toString($object)
    {
        return $object instanceof \Galvesband\TraUserBundle\Entity\Group
            ? $object->getName()
            : 'Group';
    }
}