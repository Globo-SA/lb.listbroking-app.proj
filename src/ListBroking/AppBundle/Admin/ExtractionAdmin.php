<?php

namespace ListBroking\AppBundle\Admin;

use ListBroking\AppBundle\Entity\Extraction;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ExtractionAdmin extends Admin
{

    protected $datagridValues = array(
        '_sort_order' => 'DESC'
    );

    protected function configureRoutes (RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->add('clone', $this->getRouterIdParameter() . '/clone');
        $collection->add('filtering', $this->getRouterIdParameter() . '/filtering');
    }

    public function createQuery ($context = 'list')
    {
        $query = parent::createQuery($context);

        /* @var TokenInterface $security */
        $token = $this->getConfigurationPool()
                      ->getContainer()
                      ->get('security.token_storage')
                      ->getToken()
        ;
        $user = $token->getUser();

        if ( $user )
        {

            // Admins are allowed to view all
            if ( $this->isGranted('ROLE_SUPER_ADMIN') )
            {
                return $query;
            }

            $query->andWhere($query->getRootAlias() . '.created_by = :user_id');
            $query->setParameter('user_id', $user->getId());
        }

        return $query;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters (DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name')
                       ->add('campaign.client', null, array('label' => 'Client'))
                       ->add('status', 'doctrine_orm_string', array(), 'choice', array('choices' => Extraction::$status_names))
                       ->add('sold_at', 'doctrine_orm_date_range')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields (ListMapper $listMapper)
    {
        $listMapper->add('name')
                   ->add('campaign')
                   ->add('status', 'choice', array('choices' => Extraction::$status_names))
                   ->add('quantity')
                   ->add('payout')
                   ->add('created_by')
                   ->add('updated_at')
                   ->add('sold_at')
                   ->add('_action', 'actions', array(
                       'actions' => array(
                           'filtering' => array(
                               'template' => 'ListBrokingAppBundle:Extraction:CRUD/list__action_filtering.html.twig'
                           ),
                           'edit'      => array(
                               'template' => 'ListBrokingAppBundle:Extraction:CRUD/list__action_edit.html.twig'
                           ),
                           'clone'     => array(
                               'template' => 'ListBrokingAppBundle:Extraction:CRUD/list__action_clone.html.twig'
                           )
                       )
                   ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields (FormMapper $formMapper)
    {
        $max_quantity = $this->getConfigurationPool()
                             ->getContainer()
                             ->get('app')
                             ->findConfig('extraction.max_quantity')
        ;

        $extraction = $this->getSubject();
        if ($extraction instanceof Extraction)
        {
            $displaySoldAt = $extraction->getStatus() == 3;
        }
        else
        {
            $displaySoldAt = false;
        }

        $formMapper->tab('Global')
                   ->with('Extraction')
        ;

        $formMapper->add('name')
                   ->add('campaign')
                   ->add('payout')
                   ->add('quantity', null, array('attr' => array('min' => 1, 'max' => $max_quantity)))
        ;

        if ($displaySoldAt)
        {
            $formMapper->add('sold_at');
        }

        $formMapper->end()
                   ->end()
        ;

        // Admins are allowed to view all
        if ( $this->isGranted('ROLE_SUPER_ADMIN') )
        {
            $formMapper->tab('ADMIN')//
                       ->add('status', 'choice', array('choices' => Extraction::$status_names))
                       ->add('deduplication_type', 'choice', array('required' => false, 'choices' => Extraction::$deduplication_names))
                       ->add('is_already_extracted', null, array('required' => false))
                       ->add('is_deduplicating', null, array('required' => false))
                       ->add('is_locking', null, array('required' => false))
                       ->add('is_delivering', null, array('required' => false))
                       ->end()
            ;
        }

    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields (ShowMapper $showMapper)
    {
        $showMapper->add('id')
                   ->add('name')
                   ->add('status')
                   ->add('quantity')
                   ->add('payout')
                   ->add('sold_at')
                   ->add('created_at')
                   ->add('updated_at')
        ;
    }
}
