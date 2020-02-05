<?php

namespace ListBroking\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class StagingContactDQPAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC'
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('in_opposition')
            ->add('phone')
            ->add('created_at')
            ->add('validations', null, array('template' => '@ListBrokingApp/CRUD/validations_array.html.twig'))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('in_opposition')
            ->add('is_mobile')
            ->add('phone')
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
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
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
            ->add('validations', null, array('template' => '@ListBrokingApp/CRUD/validations_array.html.twig'))
            ->add('created_at')
            ->add('updated_at')
        ;
    }
}
