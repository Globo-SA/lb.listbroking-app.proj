<?php

namespace ListBroking\TaskControllerBundle\Admin;

use ListBroking\TaskControllerBundle\Entity\Task;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class TaskAdmin extends Admin
{

    protected $datagridValues = array(
        '_sort_order' => 'DESC'
    );
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
        $collection->remove('edit');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('status')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('status', null, array('editable' => true))
            ->add('pid')
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
            ->add('status', 'choice', array('editable' => true, 'choices' => array(
                Task::STATUS_SUCCESS => Task::STATUS_SUCCESS,
                Task::STATUS_ERROR => Task::STATUS_ERROR,
                Task::STATUS_RUNNING => Task::STATUS_RUNNING)))            ->add('pid')
            ->add('msg')
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
            ->add('pid')
            ->add('msg')
            ->add('created_at')
            ->add('updated_at')
        ;
    }
}
