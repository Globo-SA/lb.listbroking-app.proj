<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [JOBENGINE_URL_LICENSE_HERE]
 * [JOBENGINE_DISCLAIMER]
 */

namespace ListBroking\AppBundle\AdminBlock;

use ListBroking\AppBundle\Service\Helper\AppServiceInterface;
use ListBroking\AppBundle\Service\Helper\StatisticsServiceInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class StatisticsBlockService extends BaseBlockService
{

    /**
     * @var RequestStack
     */
    private $request_stack;

    /**
     * @var AppServiceInterface
     */
    private $a_service;

    /**
     * @var StatisticsServiceInterface
     */
    private $s_service;

    public function __construct ($name, RequestStack $request_stack, AppServiceInterface $a_service, StatisticsServiceInterface $s_service, EngineInterface $templating)
    {
        parent::__construct($name, $templating);
        $this->request_stack = $request_stack;
        $this->a_service = $a_service;
        $this->s_service = $s_service;
    }

    public function execute (BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $blockContext->getSettings();

        $request = $this->request_stack->getCurrentRequest();

        /** @var Form $form */
        $form = $this->a_service->generateForm('data_card_filter');
        $form->handleRequest($request);
        $data = $form->getData();
        if ( ! $data )
        {
            $data = $this->a_service->findConfig('default_datacard');
        }

        $stats = $this->s_service->generateStatisticsQuery($data);

        return $this->renderResponse('ListBrokingAppBundle:AdminBlock:statistics_block.html.twig', array(
            'settings' => $settings,
            'block'    => $blockContext->getBlock(),
            'title'    => 'Lead Statistics',
            'form'     => $form->createView(),
            'stats'    => $stats
        ), $response)
            ;
    }
} 