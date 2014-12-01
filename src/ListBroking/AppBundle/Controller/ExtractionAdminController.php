<?php

namespace ListBroking\AppBundle\Controller;

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Form\AdvancedExcludeType;
use ListBroking\AppBundle\Form\AdvancedExternalExcludeType;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ExtractionAdminController extends CRUDController
{

    public function filteringAction()
    {
        $u_service = $this->get('ui');
        $a_service = $this->get('app');
        $e_service = $this->get('extraction');

        $request = $this->get('request');
        $extraction_id = $request->get($this->admin->getIdParameter());

        /** @var Extraction $extraction */
        $extraction = $a_service->getEntity('extraction', $extraction_id, true, true);
        $e_service->runExtraction($extraction);

        // Get all contacts in one Query (Better then using $extraction->getContacts())
        $contacts = $e_service->getExtractionContacts($extraction);

        // Advanced Exclusion Form
        $adv_exclusion = $u_service->generateForm(
            new AdvancedExcludeType(),
            $this->generateUrl(
                'admin_listbroking_app_extraction_lead_deduplication', array('id' => $extraction_id)
            ),
            null,
            true
        );
        // Advanced External Exclusion Form
        $adv_external_exclusion = $u_service->generateForm(
            new AdvancedExternalExcludeType(),
            $this->generateUrl(
                'admin_listbroking_app_extraction_lead_deduplication', array('id' => $extraction_id, 'external' => true)
            ),
            null,
            true
        );

        // Filters Form
        $filters_form = $u_service->generateForm(
            'filters',
            $this->generateUrl(
                'admin_listbroking_app_extraction_filtering', array('id' => $extraction_id)),
            $extraction->getFilters()
        );

        return $this->render('@ListBrokingApp/Extraction/filtering.html.twig',
            array(
                'extraction' => $extraction,
                'contacts' => $contacts,
                'forms' =>  array(
                    'filters' => $filters_form->createView(),
                    'adv_exclusion' => $adv_exclusion,
                    'adv_external_exclusion' => $adv_external_exclusion
                )
            )
        );
    }

    /**
     * Fourth step on Lead Extraction, Deduplication
     * @return RedirectResponse
     */
    public function leadDeduplicationAction(){

        $request = $this->get('request');
        $extraction_id = $request->get($this->admin->getIdParameter());
        $is_external = $request->get('external', false);

        $u_service = $this->get('ui');
        $a_service = $this->get('app');
        $e_service = $this->get('extraction');

        $extraction = $a_service->getEntity('extraction', $extraction_id, true, true);

        if($is_external){
            $form = $u_service->generateForm(new AdvancedExternalExcludeType());
        }else{
            $form = $u_service->generateForm(new AdvancedExcludeType());
        }

        $form->handleRequest($request);
        $data = $form->getData();

        $field = isset($data['field']) ? $data['field'] : 'id';

        /** @var UploadedFile $file */
        $file = $data['upload_file'];
        $filename = $e_service->generateFilename($file->getClientOriginalName(), null, 'imports/');
        $file->move('imports', $filename);

        $contacts_array = $e_service->importExtraction($filename);
        unlink($filename);

        $e_service->excludeLeads($extraction, $contacts_array, $field);
        $a_service->updateEntity($extraction);

        // Save a session variable for reprocessing
        $this->get('session')->getFlashBag()->add('extraction', 'reprocess');

        return $this->redirect($this->generateUrl('admin_listbroking_app_extraction_filtering', array('id' => $extraction_id)));
    }
}
