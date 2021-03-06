<?php

namespace ListBroking\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class StagingContactAdmin extends Admin
{

    protected $datagridValues = array(
        '_sort_order' => 'DESC'
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters (DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('valid', 'doctrine_orm_callback', array('callback' => function($queryBuilder, $alias, $field, $value) {
                if (!$value['value']) {
                    return;
                }

                $queryBuilder->andWhere("{$alias}.valid = 1");

                return true;
            },
               'field_type' => 'checkbox'
            ))
            ->add('processed', 'doctrine_orm_callback', array('callback' => function($queryBuilder, $alias, $field, $value) {
                if (!$value['value']) {
                    return;
                }

                $queryBuilder->andWhere("{$alias}.processed = 1");

                return true;
            },
                'field_type' => 'checkbox'
            ))
            ->add('update', 'doctrine_orm_callback', array('callback' => function($queryBuilder, $alias, $field, $value) {
                if (!$value['value']) {
                    return;
                }

                $queryBuilder->andWhere("{$alias}.update = 1");

                return true;
            },
            'field_type' => 'checkbox'
            ))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields (ListMapper $listMapper)
    {
        $listMapper->add('valid')
                   ->add('processed')
                   ->add('update')
                   ->add('contact_id')
                   ->add('lead_id')
                   ->add('owner')
                   ->add('phone')
                   ->add('updated_at')
        ;
        if ( $this->isGranted('ROLE_SUPER_ADMIN') )
        {
            $listMapper->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                        'edit' => array(),
                        'delete' => array(),
                        )
                ));
        }
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields (FormMapper $formMapper)
    {
        $formMapper->add('id')
                   ->add('valid')
                   ->add('processed')
                   ->add('phone')
                   ->add('is_mobile')
                   ->add('in_opposition')
                   ->add('email')
                   ->add('firstname')
                   ->add('lastname')
                   ->add('birthdate')
                   ->add('address')
                   ->add('postalcode1')
                   ->add('postalcode2')
                   ->add('ipaddress')
                   ->add('gender')
                   ->add('district')
                   ->add('county')
                   ->add('parish')
                   ->add('country')
                   ->add('owner')
                   ->add('source_name')
                   ->add('source_external_id')
                   ->add('source_country')
                   ->add('sub_category')
                   ->add('post_request')
                   ->add('validations')
                   ->add('created_at')
                   ->add('updated_at')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields (ShowMapper $showMapper)
    {
        $showMapper->add('id')
                   ->add('valid')
                   ->add('processed')
                   ->add('phone')
                   ->add('is_mobile')
                   ->add('in_opposition')
                   ->add('email')
                   ->add('firstname')
                   ->add('lastname')
                   ->add('birthdate')
                   ->add('address')
                   ->add('postalcode1')
                   ->add('postalcode2')
                   ->add('ipaddress')
                   ->add('gender')
                   ->add('district')
                   ->add('county')
                   ->add('parish')
                   ->add('country')
                   ->add('owner')
                   ->add('source_name')
                   ->add('source_external_id')
                   ->add('source_country')
                   ->add('sub_category')
                   ->add('post_request')
                   ->add('validations')
                   ->add('created_at')
                   ->add('updated_at')
        ;
    }
}
