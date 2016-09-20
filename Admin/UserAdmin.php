<?php

namespace Galvesband\TraUserBundle\Admin;

use Galvesband\TraUserBundle\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserAdmin extends AbstractAdmin {

    protected $translationDomain = 'GalvesbandTraUserBundle';

    public function preRemove($object)
    {
        if ($object instanceof User && $object->getIsSuperAdmin()) {
            /** @var User $currentUser */
            /** @noinspection PhpUndefinedMethodInspection */
            $currentUser = $this->getConfigurationPool()->getContainer()->get('security.token_storage')
                ->getToken()->getUser();

            if (!$currentUser->getIsSuperAdmin()) {
                throw new UnauthorizedHttpException('Object going to be deleted is a super-admin.');
            }
        }

        parent::preRemove($object);
    }


    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var User $currentUser */
        $currentUser = $this->getConfigurationPool()->getContainer()->get('security.token_storage')
            ->getToken()->getUser();

        $formMapper
            ->with('Basic Information', [
                'class' => 'col-md-6',
            ])
                ->add('name', 'text')
                ->add('email', 'text');

        if ($currentUser->hasRole('ROLE_ADMIN')) {
            $formMapper
                ->add('isActive')
                ->add('groups', 'sonata_type_model', [
                    'class' => 'Galvesband\TraUserBundle\Entity\Group',
                    'multiple' => true,
                ], [
                    'placeholder' => 'No group selected'
                ]);
        }

        // Only a super-admin can mess with this field.
        // If someone tries to supply a value in a create or edit form while not logged in
        // as super-admin an error will be triggered.
        if ($currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $formMapper
                ->add('isSuperAdmin');
        }

        $formMapper
            ->end()

            ->with('Authentication', ['class' => 'col-md-6'])
                ->add('plainPassword', 'repeated', [
                    'type' => 'password',
                    'translation_domain' => 'GalvesbandTraUserBundle',
                    'first_options' => [
                        'label' => 'Password',
                    ],
                    'second_options' => [
                        'label' => 'Confirmation',
                    ],
                    'invalid_message' => 'The password and its confirmation do not match.',
                    // Required only if we are creating a new user
                    'required' => !$this->getRequest()->get($this->getIdParameter()),
                ])
            ->end();
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('email')
            ->add('isActive', 'boolean')
            ->add('isSuperAdmin')
            ->add('groups', null, [
                'route' => [
                    'name' => 'show'
                ]
            ]);
    }

    public function getExportFields()
    {
        $fields = parent::getExportFields();
        $index = array_search('password', $fields);
        unset($fields[$index]);
        $index = array_search('salt', $fields);
        unset($fields[$index]);

        return array_values($fields);
    }


    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('email')
            ->add('isActive')
            ->add('isSuperAdmin')
            ->add('groups');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('email')
            ->add('isActive')
            ->add('isSuperAdmin')
            ->add('groups', null, [
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

    /**
     * Convierte el objeto de entrada en su representación textual
     * @param \Galvesband\TraUserBundle\Entity\User|mixed $object
     * @return string
     */
    public function toString($object)
    {
        return $object instanceof User
            ? $object->getName()
            : 'User';
    }
}