<?php

namespace Galvesband\TraUserBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class UserAdmin extends AbstractAdmin {

    protected $translationDomain = 'GalvesbandTraUserBundle';

    protected function configureFormFields(FormMapper $formMapper)
    {
        $repository = $this->getConfigurationPool()->getContainer()->get('doctrine')
            ->getManager()->getRepository('GalvesbandTraUserBundle:Group');
        $currentUser = $this->getConfigurationPool()->getContainer()->get('security.token_storage')
            ->getToken()->getUser();

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
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
            ->with('Basic Information', [
                'class' => 'col-md-6',
            ])
                ->add('name', 'text')
                ->add('email', 'text')
                ->add('isActive')
                ->add('groups', 'sonata_type_model', [
                    'class' => 'Galvesband\TraUserBundle\Entity\Group',
                    'multiple' => true,
                    'query' => $query,
                ], [
                    'placeholder' => 'No group selected'
                ])
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
            ->add('is_active', 'boolean')
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
        return $object instanceof \Galvesband\TraUserBundle\Entity\User
            ? $object->getName()
            : 'User';
    }
}