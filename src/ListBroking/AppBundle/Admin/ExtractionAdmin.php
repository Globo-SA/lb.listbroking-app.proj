<?php

namespace ListBroking\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class ExtractionAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC'
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('filtering', $this->getRouterIdParameter() . '/filtering');
        $collection->add('lead_deduplication', $this->getRouterIdParameter() . '/deduplication');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('campaign')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('name')
            ->add('campaign')
            ->add('status')
            ->add('quantity')
            ->add('payout')
            ->add('updated_at')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'filtering' => array(
                        'template' => 'ListBrokingAppBundle:CRUD:list__action_filtering.html.twig'
                    ),
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
            ->add('campaign')
            ->add('status')
            ->add('quantity')
            ->add('payout')
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
            ->add('status')
            ->add('quantity')
            ->add('payout')
            ->add('created_at')
            ->add('updated_at')
        ;
    }
}
