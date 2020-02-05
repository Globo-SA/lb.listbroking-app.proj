<?php

namespace ListBroking\AppBundle\Admin;

use ListBroking\AppBundle\Entity\Configuration;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ConfigurationAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_by' => 'name',
        '_sort_order' => 'DESC'
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('type', null, array(), 'choice', array('choices' => Configuration::$type_values))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('type', 'choice', array('choices' => Configuration::$type_values))
            ->add('value')
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
            ->add('name')
            ->add('type', 'choice', array('choices' => Configuration::$type_values))
            ->add('value')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('type', 'choice', array('choices' => Configuration::$type_values))
            ->add('value')
            ->add('created_at')
            ->add('updated_at')
            ->add('created_by')
            ->add('updated_by')
        ;
    }
}
