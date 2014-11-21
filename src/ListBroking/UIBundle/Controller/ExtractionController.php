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


use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Form\CampaignType;
use ListBroking\AppBundle\Form\ClientType;
use ListBroking\AppBundle\Form\ExtractionType;

use ListBroking\UIBundle\Form\AdvancedExcludeType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtractionController extends Controller
{
    /**
     * First step on Extraction process
     * @return Response
     */
    public function indexAction()
    {
        $extractions = $this->get('app')->getEntities('extraction');

        return $this->render('ListBrokingUIBundle:Extraction:0-index.html.twig',
            array(
                'extractions' => $extractions
            ));
    }

    /**
     * First step on Lead Extraction, Configuration
     * @return Response
     */
    public function configurationAction()
    {
        $ui_service = $this->get('ui');

        return $this->render('ListBrokingUIBundle:Extraction:1-configuration.html.twig',
            array(
                'forms' => array(
                    'client' => $ui_service->generateForm(new ClientType(), null, null, true),
                    'campaign' => $ui_service->generateForm(new CampaignType(), null, null, true),
                    'extraction' => $ui_service->generateForm(new ExtractionType(), null, null, true)
                )
            )
        );
    }

    /**
     * Second step on Lead Extraction, Filtering
     * @param Request $request
     * @param $extraction_id
     * @return Response
     * @throws \ListBroking\LockBundle\Exception\InvalidFilterObjectException
     */
    public function filteringAction(Request $request, $extraction_id)
    {
        /** @var Extraction $extraction */
        $extraction = $this->get('app')->getEntity('extraction', $extraction_id, true, true);

        $reprocess = false;
        $flashes = $this->get('session')->getFlashBag()->get('extraction');
        if(in_array('reprocess', array_values($flashes))){
            $reprocess = true;
        }

        // Change the Extraction Status to Filtering if it's on configuration
        if($extraction->getStatus() == Extraction::STATUS_CONFIGURATION){
            $extraction->setStatus(Extraction::STATUS_FILTRATION);
            $this->get('app')->updateEntity($extraction);
        }

        // Extraction Form
        $extraction_form = $this->get('ui')->generateForm(
            new ExtractionType(),
            null,
            $extraction,
            true
        );

        // Advanced Exclusion Form
        $adv_exclusion = $this->get('ui')->generateForm(
            new AdvancedExcludeType(),
            $this->generateUrl(
                'lead_deduplication', array('extraction_id' => $extraction_id)
            ),
            null,
            true
        );

        // Filters Form
        $filters_form = $this->get('ui')->generateForm(
            'filters',
            $this->generateUrl(
                'extraction_filtering', array('extraction_id' => $extraction_id)),
            $extraction->getFilters()
        );

        // Update filters
        if ($request->getMethod() == 'POST')
        {
            // Handle the filters form
            $filters_form = $filters_form->handleRequest($request);
            $filters = $filters_form->getData();

            // Serializes filters and compares them with a saved version
            // to check for changes on filters
            $serialized_filter = md5(serialize($filters));
            if(!in_array($serialized_filter, array_values($flashes))){

                // Sets the new Filters and mark the Extraction to reprocess
                $extraction->setFilters($filters);
                $this->get('app')->updateEntity($extraction);
                $reprocess = true;
            }

            $this->get('session')->getFlashBag()->add('extraction', $serialized_filter);
        }

        // Reprocess leads list
        if($reprocess){
            $this->get('extraction')->runExtraction($extraction);
            $this->get('app')->updateEntity($extraction);
        }

        // Get all contacts in one Query (Better then using $extraction->getContacts())
        $contacts = $this->get('extraction')->getExtractionContacts($extraction);
        return $this->render('ListBrokingUIBundle:Extraction:2-filtering.html.twig',
            array(
                'extraction' => $extraction,
                'contacts' => $contacts,
                'forms' =>  array(
                    'filters' => $filters_form->createView(),
                    'adv_exclusion' => $adv_exclusion,
                    'extraction' => $extraction_form,
                )
            )
        );
    }

    /**
     * Third step on Lead Extraction, Lead Extraction
     * @param Request $request
     * @param $extraction_id
     * @param $extraction_template_id
     * @return Response
     * @throws \ListBroking\ExtractionBundle\Exception\InvalidExtractionException
     */
    public function extractionDownloadAction(Request $request, $extraction_id, $extraction_template_id){

        $e_service = $this->get('extraction');
        $a_service = $this->get('app');

        $extraction = $a_service->getEntity('extraction', $extraction_id, true, true);

        /** @var Extraction $extraction */
        $filename = $e_service->exportExtraction($a_service->getEntity('extraction_template', $extraction_template_id), $extraction->getContacts());

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

    /**
     * Fourth step on Lead Extraction, Deduplication
     * @param Request $request
     * @param $extraction_id
     * @return RedirectResponse
     */
    public function leadDeduplicationAction(Request $request, $extraction_id){

        $u_service = $this->get('ui');
        $a_service = $this->get('app');
        $e_service = $this->get('extraction');

        $extraction = $a_service->getEntity('extraction', $extraction_id, true, true);

        $form = $u_service->generateForm(new AdvancedExcludeType());
        $form->handleRequest($request);
        $data = $form->getData();

        /** @var UploadedFile $file */
        $file = $data['upload_file'];
        $filename = $e_service->generateFilename($file->getClientOriginalName(), null, 'imports/');
        $file->move('imports', $filename);

        $contacts_array = $e_service->importExtraction($filename);
        unlink($filename);

        $e_service->excludeLeads($extraction, $contacts_array);
        $a_service->updateEntity($extraction);

        // Save a session variable for reprocessing
        $this->get('session')->getFlashBag()->add('extraction', 'reprocess');

        return $this->redirect($this->generateUrl('extraction_filtering', array('extraction_id' => $extraction_id)));
    }
}