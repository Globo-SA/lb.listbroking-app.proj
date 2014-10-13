<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\UIBundle\Controller;


use ListBroking\ClientBundle\Form\ClientType;
use ListBroking\ExtractionBundle\Service\ExtractionService;
use ListBroking\UIBundle\Service\UIService;
use Symfony\Component\HttpFoundation\Response;

class ExtractionController {


    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var ExtractionService
     */
    private $e_service;
    /**
     * @var UIService
     */
    private $ui_service;

    function __construct(\Twig_Environment $twig, ExtractionService $e_service, UIService $ui_service)
    {
        $this->twig = $twig;
        $this->e_service = $e_service;
        $this->ui_service = $ui_service;
    }

    public function indexAction(){

        $extractions = $this->e_service->getExtractionList();

        return new Response($this->twig->render(
            'ListBrokingUIBundle:Extraction:index.html.twig',
            array(
                'extractions'  => $extractions
            )
        ));
    }

    public function configurationAction(){

        $instance = uniqid();
        $forms = array(
            'client' => $this->ui_service->generateFormView("client_{$instance}", new ClientType())
        );

        return new Response($this->twig->render(
            'ListBrokingUIBundle:Extraction:configuration.html.twig',
            array(
                'forms' => $forms
            )
        ));
    }
} 