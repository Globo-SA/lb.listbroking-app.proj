<?php

namespace ListBroking\AppBundle\Controller;

use ListBroking\AppBundle\Form\ExtractionDeduplicationType;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

class ExtractionAdminController extends CRUDController
{

    public function filteringAction()
    {
        // Services
        $u_service = $this->get('ui');
        $a_service = $this->get('app');
        $e_service = $this->get('extraction');

        // Current Extraction
        $extraction_id = $this->get('request')->get($this->admin->getIdParameter());

        // Run Extraction
        $extraction = $a_service->getEntity('extraction', $extraction_id, true, true);
        $e_service->runExtraction($extraction);

        // Get all contacts in one Query (Better then using $extraction->getContacts())
        $contacts = $e_service->getExtractionContacts($extraction);

        // Forms
        $adv_exclusion = $u_service->generateForm(new ExtractionDeduplicationType());
        $adv_external_exclusion = $u_service->generateForm(new ExtractionDeduplicationType());
        $filters_form = $u_service->generateForm(
            'filters',
            $this->generateUrl(
                'admin_listbroking_app_extraction_filtering', array('id' => $extraction_id)),
            $extraction->getFilters()
        );

        //TODO: Cache this !
        //Check for Queues
        $deduplication_queues = $this->get('doctrine')->getManager()->getRepository('ListBrokingAppBundle:ExtractionDeduplicationQueue')
            ->findOneBy(array('extraction' => $extraction));

        // Render Response
        return $this->render('@ListBrokingApp/Extraction/filtering.html.twig',
            array(
                'extraction' => $extraction,
                'contacts' => $contacts,
                'deduplication_queues' => $deduplication_queues,
                'forms' =>  array(
                    'filters' => $filters_form->createView(),
                    'adv_exclusion' => $adv_exclusion->createView(),
                    'adv_external_exclusion' => $adv_external_exclusion->createView()
                )
            )
        );
    }

    //TODO: REMOVE THIS
//    /**
//     * Fourth step on Lead Extraction, Deduplication
//     * @return RedirectResponse
//     */
//    public function leadDeduplicationAction(){
//
//        $request = $this->get('request');
//        $extraction_id = $request->get($this->admin->getIdParameter());
//        $is_external = $request->get('external', false);
//
//        $u_service = $this->get('ui');
//        $a_service = $this->get('app');
//        $e_service = $this->get('extraction');
//
//        $extraction = $a_service->getEntity('extraction', $extraction_id, true, true);
//
//        if($is_external){
//            $form = $u_service->generateForm(new AdvancedExternalExcludeType());
//        }else{
//            $form = $u_service->generateForm(new AdvancedExcludeType());
//        }
//        $form->handleRequest($request);
//        $data = $form->getData();
//        $field = isset($data['field']) ? $data['field'] : 'lead_id';
//
//        /** @var UploadedFile $file */
//        $file = $data['upload_file'];
//        $filename = $e_service->generateFilename($file->getClientOriginalName(), null, 'imports/');
//        $file->move('imports', $filename);
//
//        $e_service->persistDeduplications($filename, $extraction, $field, true);
//        unlink($filename);
//
//        //$e_service->excludeLeads($extraction, $data_array, $field);
////        $a_service->updateEntity($extraction);
//
//        // Save a session variable for reprocessing
//        $this->get('session')->getFlashBag()->add('extraction', 'reprocess');
//
//        return $this->redirect($this->generateUrl('admin_listbroking_app_extraction_filtering', array('id' => $extraction_id)));
//    }
}
