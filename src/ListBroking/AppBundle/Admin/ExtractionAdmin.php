<?php

namespace ListBroking\AppBundle\Admin;

use ListBroking\AppBundle\Entity\Extraction;
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
        $collection->remove('delete');

        $collection->add('clone', $this->getRouterIdParameter().'/clone');
        $collection->add('filtering', $this->getRouterIdParameter() . '/filtering');
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
            ->add('name')
            ->add('campaign')
            ->add('status', 'choice', array('choices' => Extraction::$status_names))
            ->add('quantity')
            ->add('payout')
            ->add('updated_at')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'filtering' => array(
                        'template' => 'ListBrokingAppBundle:Extraction:CRUD/list__action_filtering.html.twig'
                    ),
                    'edit' => array(
                        'template' => 'ListBrokingAppBundle:Extraction:CRUD/list__action_edit.html.twig'
                    ),
                    'clone' => array(
                        'template' => 'ListBrokingAppBundle:Extraction:CRUD/list__action_clone.html.twig'
                    ),
                    'delete' => array()
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
            ->add('campaign', 'sonata_type_model_list', array())
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
