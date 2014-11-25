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

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('filtering', $this->getRouterIdParameter().'/2-filtering');
        $collection->add('lead_deduplication', $this->getRouterIdParameter().'/deduplication-4');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('status')
            ->add('quantity')
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
