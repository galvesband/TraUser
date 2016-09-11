<?php

namespace Galvesband\TraUserBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class GroupAdmin extends AbstractAdmin {

    protected $translationDomain = 'GalvesbandTraUserBundle';

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
            ->with('Basic Information')
                ->add('name', 'text')
                ->add('description', 'text')
            ->end()
            ->with('Roles')
                ->add('roles', 'entity', [
                    'class' => 'Galvesband\TraUserBundle\Entity\Role',
                    'multiple' => true
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
            ->add('name')
            ->add('description')
            ->add('roles');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
            ->addIdentifier('name')
            ->add('description')
            ->add('roles')
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    public function toString($object) {
        return $object instanceof \Galvesband\TraUserBundle\Entity\Group
            ? $object->getName()
            : 'Group';
    }
}