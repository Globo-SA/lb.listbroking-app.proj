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
use ListBroking\UIBundle\Form\AdvancedExcludeType;
use ListBroking\UIBundle\Service\UIService;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    function __construct(
        \Twig_Environment $twig,
         Router $router,
         ExtractionService $e_service,
         LockService $l_service,
         UIService $ui_service
    )
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
        $extraction = $this->e_service->getExtraction($extraction_id, true);
        if (!$extraction)
        {
            throw new HttpException(404, "Extraction not found!");
        }

        $reprocess = $request->get('reprocess', false);

        // Change the Extraction Status to Filtering if it's on configuration
        if($extraction->getStatus() == Extraction::STATUS_CONFIGURATION){
            $extraction->setStatus(Extraction::STATUS_FILTRATION);
            $this->e_service->updateExtraction($extraction);
        }

        // Extraction Form
        $extraction_form = $this->ui_service->generateForm(new ExtractionType(), null, $extraction, true);

        // Advanced Exclusion Form
        $adv_exclusion = $this->ui_service->generateForm(new AdvancedExcludeType(), $this->router->generate(
                'lead_deduplication', array('extraction_id' => $extraction_id))
        );

        // Filters Form
        $filters_form = $this->ui_service->generateForm('filters', $this->router->generate(
                'extraction_filtering', array('extraction_id' => $extraction_id)),  $extraction->getFilters()
        );

        // Update filters
        if ($request->getMethod() == 'POST')
        {
            $filters_form = $filters_form->handleRequest($request);

            // Handle the filters form
            $filters = $filters_form->getData();

            // Update extraction with new filters
            $extraction->setFilters($filters);
            $this->e_service->updateExtraction($extraction);

            $reprocess = true;
        }

        // Reprocess leads list
        if($reprocess){

            // Start the filtering Engine and query the DB
            $engine = $this->l_service->startEngine();
            $qb = $engine->compileFilters($engine->prepareFilters($extraction->getFilters()), $extraction->getQuantity());

            // Add Contacts to the Extraction
            $contacts = $qb->getQuery()->execute();
            $extraction = $this->e_service->addExtractionContacts($extraction, $contacts);

            // Change the Extraction Status to Confirmation if it's on filtration and has contacts
            if($extraction->getStatus() == Extraction::STATUS_FILTRATION && count($contacts) > 0){
                $extraction->setStatus(Extraction::STATUS_CONFIRMATION);
                $this->e_service->updateExtraction($extraction);
            }
        }

        /** @var Form[] $forms */
        $forms = array(
            'filters' => $filters_form->createView(),
            'adv_exclusion' => $adv_exclusion->createView(),
            'extraction' => $extraction_form
        );

        return new Response($this->twig->render(
            'ListBrokingUIBundle:Extraction:2-filtering.html.twig',
            array(
                'extraction' => $extraction,
                'forms' => $forms,
            )
        ));
    }

    public function extractionDownloadAction(Request $request, $extraction_id, $extraction_template_id){

        /** @var Extraction $extraction */
        $extraction = $this->e_service->getExtraction($extraction_id, true);
        $filename = $this->e_service->exportExtraction($this->e_service->getExtractionTemplate($extraction_template_id), $extraction->getContacts());

        // Generate response
        $response = new Response();

        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-length', filesize($filename));

        $cookie = new Cookie('fileDownload', 'true', new \DateTime('+1 minute'));
        $response->headers->setCookie($cookie);

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(readfile($filename));

        return $response;
    }

    public function leadDeduplicationAction(Request $request, $extraction_id){

        $extraction = $this->e_service->getExtraction($extraction_id, true);
        if (!$extraction)
        {
            throw new HttpException(404, "Extraction not found!");
        }
        $form = $this->ui_service->generateForm(new AdvancedExcludeType());
        $form->handleRequest($request);
        $data = $form->getData();

        /** @var UploadedFile $file */
        $file = $data['upload_file'];
        $filename = uniqid() . "_" . $file->getClientOriginalName();
        $file->move('imports', $filename);

        $contacts_array = $this->e_service->importExtraction('imports/'. $filename);
        unlink('imports/' . $filename);

        $this->e_service->excludeLeads($extraction, $contacts_array);

        return new RedirectResponse($this->router->generate('extraction_filtering', array('extraction_id' => $extraction_id, 'reprocess' => true)));
    }
}