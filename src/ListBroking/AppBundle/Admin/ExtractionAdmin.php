<?php

namespace ListBroking\AppBundle\Admin;

use Application\Sonata\UserBundle\Entity\User;
use ListBroking\AppBundle\Entity\Extraction;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Tests\Filter\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ExtractionAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC'
    );


    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('clone', $this->getRouterIdParameter().'/clone');
        $collection->add('filtering', $this->getRouterIdParameter() . '/filtering');


    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        /* @var TokenInterface $security */
        $token = $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken();
        $user = $token->getUser();

        if ($user) {

            // Admins are allowed to view all
            if ($this->isGranted('ROLE_SUPER_ADMIN')){
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
            ->add('created_by')
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
                    )
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
