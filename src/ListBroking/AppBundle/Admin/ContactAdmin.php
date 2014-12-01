<?php

namespace ListBroking\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ContactAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC'
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('birthdate')
            ->add('address')
            ->add('postalcode1')
            ->add('postalcode2')
            ->add('ipaddress')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('birthdate')
            ->add('address')
            ->add('postalcode1')
            ->add('postalcode2')
            ->add('ipaddress')
            ->add('updated_at')
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
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('birthdate')
            ->add('address')
            ->add('postalcode1')
            ->add('postalcode2')
            ->add('ipaddress')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('birthdate')
            ->add('address')
            ->add('postalcode1')
            ->add('postalcode2')
            ->add('ipaddress')
            ->add('created_at')
            ->add('updated_at')
        ;
    }
}
