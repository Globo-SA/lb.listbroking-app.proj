<?php

namespace ListBroking\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class ContactAdmin extends Admin
{
    /**
     * Type "3" means that Sonata will make an EXACT MATCH to the filters that are given
     * instead of using a 'LIKE' search
     *
     * @var array
     */
    protected $datagridValues = array(
        'email' => [
            'type'  => 3,
            'value' => ''
        ],
        'lead.phone' => [
            'type'  => 3,
            'value' => ''
        ],
        '_sort_order' => 'DESC'
    );

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('extractionsHistory', $this->getRouterIdParameter() . '/extractionsHistory');
    }

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
            ->add('lead.phone', 'doctrine_orm_callback', array(
                //                'callback'   => array($this, 'getWithOpenCommentFilter'),
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $queryBuilder->leftJoin(sprintf('%s.lead', $alias), 'l');
                    $queryBuilder->andWhere('l.phone = :phone');
                    $queryBuilder->setParameter('phone', $value['value']);

                    return true;
                },
                'field_type' => 'text'
            ))
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
            ->add('lead.phone')
            ->add('firstname')
            ->add('lastname')
            ->add('updated_at')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                    'extractionsHistory' => [
                        'template' => 'ListBrokingAppBundle:Contact:CRUD/list__action_extractionsHistory.html.twig'
                    ],
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
            ->with("Facts")
                ->add('id')
                ->add('externalId')
                ->add('email')
                ->add('firstname')
                ->add('lastname')
                ->add('birthdate')
                ->add('address')
                ->add('postalcode1')
                ->add('postalcode2')
                ->add('ipaddress')
                ->add('date', null, array('label' => 'Acquisition date'))

            ->end()
            ->with("Dimensions")
                ->add('gender')
                ->add('source')
                ->add('owner')
                ->add('subCategory')
                ->add('district')
                ->add('county')
                ->add('parish')
                ->add('country')
            ->end()
            ->with("Stats")
                ->add("is_clean", null, array('label' => 'Is clean ?'))
                ->add('created_at')
                ->add('updated_at')
            ->end()
            ->with('Lead')
                ->add('lead.phone', null, array('label' => 'Phone'))
                ->add('lead.is_mobile', null, array('label' => 'Is Mobile ?'))
            ->end()
            ->with("Lead Stats")
            ->add('lead.is_ready_to_use', null, array('label' => 'Is ready to used ?'))
            ->add('lead.in_opposition', null,  array('label' => 'In opposition list ?'))
            ->add('lead', null, array('label' => 'Edit lead stats'))
            ->end()
        ;
    }
}
