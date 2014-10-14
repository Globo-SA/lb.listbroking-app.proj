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


use ListBroking\ClientBundle\Form\CampaignType;
use ListBroking\ClientBundle\Form\ClientType;
use ListBroking\ExtractionBundle\Form\ExtractionType;
use ListBroking\ExtractionBundle\Service\ExtractionService;
use ListBroking\UIBundle\Service\UIService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        $forms = array(
            'client' => $this->ui_service->generateFormView(new ClientType()),
            'campaign' => $this->ui_service->generateFormView(new CampaignType()),
            'extraction' => $this->ui_service->generateFormView(new ExtractionType())
        );

        return new Response($this->twig->render(
            'ListBrokingUIBundle:Extraction:configuration.html.twig',
            array(
                'forms' => $forms
            )
        ));
    }

    public function filteringAction(Request $request, $extraction_id){

        $extraction = $this->e_service->getExtraction($extraction_id);
        if(!$extraction){
            throw new HttpException(404, "Extraction not found!");
        }

        return new Response($this->twig->render(
            'ListBrokingUIBundle:Extraction:filtering.html.twig',
            array(
            )
        ));
    }
} 