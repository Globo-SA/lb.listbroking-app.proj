<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [JOBENGINE_URL_LICENSE_HERE]
 *
 * [JOBENGINE_DISCLAIMER]
 */

namespace ListBroking\AppBundle\AdminBlock;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;

class StatisticsBlockService extends BaseBlockService {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct($name, EngineInterface $templating, EntityManagerInterface $entity_manager)
    {
        parent::__construct($name, $templating);
        $this->em = $entity_manager;
    }

    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'title' => 'Lead Statistics',
            'template' => 'ListBrokingAppBundle:AdminBlock:statistics_block.html.twig',
        ));
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $blockContext->getSettings();

        $statistics = array(
          array(
              'label' => 'Leads by Country',
              'attr' => 'col-md-6',
              'headers' => array('Country', 'Total'),
              'results' => array(
                  array(
                      'country' => 'PT',
                      'total' => 1000
                  ),
                  array(
                      'country' => 'ES',
                      'total' => 5000
                  ),
              ),
              'totals' => array(null, 6000)
          ),
          array(
              'label' => 'Leads by Lock',
              'attr' => 'col-md-6',
              'headers' => array('Country', 'Total'),
              'results' => array(
                  array(
                      'country' => 'Locked',
                      'total' => 6540
                  ),
                  array(
                      'country' => 'Unlocked',
                      'total' => 3000
                  ),
              ),
              'totals' => array(null, 9540)
          ),
        );

        return $this->renderResponse($blockContext->getTemplate(), array(
            'block'    => $blockContext->getBlock(),
            'settings' => $settings,
            'statistics' => $statistics
        ), $response);
    }
} 