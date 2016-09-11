<?php

namespace Galvesband\TraUserBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class UserAdmin extends AbstractAdmin {

    protected $translationDomain = 'GalvesbandTraUserBundle';

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
            ->with('Basic Information', ['class' => 'col-md-6'])
                ->add('name', 'text')
                ->add('email', 'text')
                ->add('isActive')
                ->add('groups', 'entity', [
                    'class' => 'Galvesband\TraUserBundle\Entity\Group',
                    'multiple' => true
                ])
            ->end()

            ->with('Authentication', ['class' => 'col-md-6'])
                ->add('plainPassword', 'password', [
                    'required' => false,
                    'label' => 'Password',
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
            ->add('name')
            ->add('email')
            ->add('isActive');
    }

    protected function configureListFields(ListMapper $listMapper) {
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
    public function toString($object) {
        return $object instanceof \Galvesband\TraUserBundle\Entity\User
            ? $object->getName()
            : 'User';
    }
}