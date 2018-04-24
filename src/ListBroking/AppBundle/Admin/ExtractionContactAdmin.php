<?php

namespace ListBroking\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ExtractionContactAdmin extends Admin
{
    /**
     * @var array
     */
    protected $datagridValues = [
        '_sort_by'    => 'extraction.sold_at',
        '_sort_order' => 'DESC',
    ];

    /**
     * Configure the available routes in the ExtractionContact list interface
     *
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
        $collection->remove('export');
    }

    /**
     * Configure the list of filters in the ExtractionContact list interface
     *
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('contact.email', null, ['label' => 'Email'])
            ->add('contact.lead.phone', null, ['label' => 'Phone']);
    }

    /**
     * Configure the list of fields in the ExtractionContact list interface
     *
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('contact.email', null, ['label' => 'Email'])
            ->add('contact.lead.phone', null, ['label' => 'Phone'])
            ->add('contact.date', null, ['label' => 'Acquisition Date'])
            ->add('extraction.name')
            ->add('extraction.campaign.name', null, ['label' => 'Campaign'])
            ->add('extraction.sold_at');
    }
}