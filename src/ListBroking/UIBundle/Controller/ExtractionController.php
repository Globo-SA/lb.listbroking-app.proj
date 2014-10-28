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
use ListBroking\ExtractionBundle\Entity\Extraction;
use ListBroking\ExtractionBundle\Form\ExtractionType;
use ListBroking\ExtractionBundle\Service\ExtractionService;
use ListBroking\LockBundle\Service\LockService;
use ListBroking\UIBundle\Service\UIService;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Router;


class ExtractionController
{


    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var Router
     */
    private $router;
    /**
     * @var ExtractionService
     */
    private $e_service;
    /**
     * @var LockService
     */
    private $l_service;
    /**
     * @var UIService
     */
    private $ui_service;

    function __construct(\Twig_Environment $twig, Router $router, ExtractionService $e_service, LockService $l_service, UIService $ui_service)
    {
        $this->twig = $twig;
        $this->router = $router;

        $this->e_service = $e_service;
        $this->l_service = $l_service;
        $this->ui_service = $ui_service;
    }

    public function indexAction()
    {

        $extractions = $this->e_service->getExtractionList();

        return new Response($this->twig->render(
            'ListBrokingUIBundle:Extraction:0-index.html.twig',
            array(
                'extractions' => $extractions
            )
        ));
    }

    public function configurationAction()
    {

        $forms = array(
            'client' => $this->ui_service->generateForm(new ClientType(), true),
            'campaign' => $this->ui_service->generateForm(new CampaignType(), true),
            'extraction' => $this->ui_service->generateForm(new ExtractionType(), true)

        );

        return new Response($this->twig->render(
            'ListBrokingUIBundle:Extraction:1-configuration.html.twig',
            array(
                'forms' => $forms
            )
        ));
    }

    public function filteringAction(Request $request, $extraction_id)
    {
        /** @var Extraction $extraction */
        $extraction = $this->e_service->getExtraction($extraction_id, true);
        if (!$extraction)
        {
            throw new HttpException(404, "Extraction not found!");
        }

        $leads = array();

        /** @var FormBuilderInterface $filters_form_builder */
        $filters_form_builder = $this->ui_service->generateForm('filters', false);
        $filters_form_builder->setAction($this->router->generate(
                'extraction_filtering', array('extraction_id' => $extraction_id))
        );
        $filters_form = $filters_form_builder->getForm();
        $filters_form->setData($extraction->getFilters());

        if ($request->getMethod() == 'POST')
        {
            // Handle the filters form
            $filters_form = $filters_form->handleRequest($request);
            $filters  = $filters_form->getData();

            // Update extraction with new filters
            $extraction->setFilters($filters);
            $this->e_service->updateExtraction($extraction);

            // Start the filtering Engine and query the DB
            $engine = $this->l_service->startEngine();
            $qb = $engine->compileFilters($engine->prepareFilters($filters));
            $qb->setMaxResults($extraction->getQuantity());

            $leads = $qb->getQuery()->execute();
        }

        /** @var Form[] $forms */
        $forms = array(
            'filters' => $filters_form->createView()
        );


        return new Response($this->twig->render(
            'ListBrokingUIBundle:Extraction:2-filtering.html.twig',
            array(
                'extraction' => $extraction,
                'forms' => $forms,
                'leads' => $leads
            )
        ));
    }
}