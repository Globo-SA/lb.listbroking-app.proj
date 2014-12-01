<?php

namespace ListBroking\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class LockAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC'
    );

    private $type_values = array(
        1 => 'NoLocksLockFilter',
        2 => 'ReservedLockFilter',
        3 => 'ClientLockFilter',
        4 => 'CampaignLockFilter',
        5 => 'CategoryLockFilter',
        6 => 'SubCategoryLockFilter',
    );
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('status')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('status')
            ->add('type', 'choice', array('choices' => $this->type_values))
            ->add('expiration_date')
            ->add('client')
            ->add('campaign')
            ->add('category')
            ->add('sub_category')
            ->add('lead')
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
            ->add('status')
            ->add('type', 'choice', array('choices' => $this->type_values))
            ->add('expiration_date')
            ->add('client')
            ->add('campaign')
            ->add('category')
            ->add('sub_category')
            ->add('lead')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('status')
            ->add('type')
            ->add('expiration_date')
            ->add('client')
            ->add('campaign')
            ->add('category')
            ->add('sub_category')
            ->add('lead')
            ->add('created_at')
            ->add('updated_at')
        ;
    }
}
