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
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatisticsBlockService extends BaseBlockService {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct($name, EngineInterface $templating, EntityManagerInterface $entity_manager)
    {
        parent::__construct($name, $templating);
        $this->entity_manager = $entity_manager;
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

        return $this->renderResponse('ListBrokingAppBundle:AdminBlock:statistics_block.html.twig', array(
            'block'    => $blockContext->getBlock(),
            'title' => 'Lead Statistics',
            'statistics' => $statistics
        ), $response);
    }
} 