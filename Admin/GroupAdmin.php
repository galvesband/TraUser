<?php

/*
 * This file is part of the Galvesband TraUserBundle.
 *
 * (c) Rafael Gálvez-Cañero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Galvesband\TraUserBundle\Admin;

use Galvesband\TraUserBundle\Entity\Group;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class GroupAdmin extends AbstractAdmin {

    protected $translationDomain = 'GalvesbandTraUserBundle';

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
            ->with('Basic Information', ['class' => 'col-md-6 col-xs-12'])
                ->add('name', 'text')
                ->add('description', 'text')
            ->end()
            ->with('Roles', ['class' => 'col-md-6 col-xs-12'])
                ->add('roles', 'sonata_type_model', [
                    'class' => 'Galvesband\TraUserBundle\Entity\Role',
                    'multiple' => true,
                ])
            ->end();
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Basic Information', ['class' => 'col-md-6 col-xs-12'])
                ->add('name')
                ->add('description')
            ->end()
            ->with('Relations', ['class' => 'col-md-6 col-xs-12'])
                ->add('users', null, [
                    'route' => ['name' => 'show']
                ])
                ->add('roles', null, [
                    'route' => ['name' => 'show']
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
        return $object instanceof Group
            ? $object->getName()
            : 'Group';
    }
}