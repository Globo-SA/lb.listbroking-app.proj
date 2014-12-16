<?php

namespace ListBroking\AppBundle\Controller;

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Form\ExtractionDeduplicationType;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

class ExtractionAdminController extends CRUDController
{

    public function filteringAction()
    {
        // Services
        $e_service = $this->get('extraction');

        // Current Extraction and step
        $extraction_id = $this->get('request')->get($this->admin->getIdParameter());

        // Run Extraction
        $extraction = $e_service->getEntity('extraction', $extraction_id, true, true);
        $e_service->runExtraction($extraction);

        // Get all contacts in one Query (Better then using $extraction->getContacts())
        $contacts = $e_service->getExtractionContacts($extraction);

        // Forms
        $adv_exclusion = $e_service->generateForm(new ExtractionDeduplicationType());
        $adv_external_exclusion = $e_service->generateForm(new ExtractionDeduplicationType());
        $filters_form = $e_service->generateForm(
            'filters',
            $this->generateUrl(
                'admin_listbroking_app_extraction_filtering', array('id' => $extraction_id)),
            $extraction->getFilters()
        );

        //Check for Queues
        $deduplication_queues = $e_service->getDeduplicationQueuesByExtraction($extraction);

        // Render Response
        return $this->render('@ListBrokingApp/Extraction/filtering.html.twig',
            array(
                'lock_time' => $e_service->getConfig('lock.time')->getValue(),
                'extraction' => $extraction,
                'contacts' => $contacts,
                'deduplication_queues' => $deduplication_queues,
                'forms' =>  array(
                    'filters' => $filters_form->createView(),
                    'adv_exclusion' => $adv_exclusion->createView(),
                    'adv_external_exclusion' => $adv_external_exclusion->createView()
                ),
                'elements' => $this->admin->getShow(),
            )
        );
    }
}