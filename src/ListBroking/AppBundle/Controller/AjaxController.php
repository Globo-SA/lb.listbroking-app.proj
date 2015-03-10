<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Controller;

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Exception\InvalidExtractionException;
use ListBroking\AppBundle\Form\ExtractionDeduplicationType;
use ListBroking\AppBundle\Form\StagingContactImportType;
use ListBroking\AppBundle\Service\Helper\AppService;
use ListBroking\TaskControllerBundle\Entity\Queue;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller {

    /**
     * Gets the last exceptions thrown by the system
     * @param Request $request
     * @return JsonResponse
     */
    public function lastExceptionsAction(Request $request){
        try{
            $this->validateRequest($request);
            $service = $this->get('app');
            $last = $service->getExceptions(5);

            return $this->createJsonResponse($last);
        }catch (\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Used to get lists of entities
     * @param Request $request
     * @return JsonResponse
     */
    public function listsAction(Request $request){
        try{
//            $this->validateRequest($request);

            $ui_service = $this->get('app');

            $type = $request->get('type', '');
            $query = $request->get('q', '');
            $bundle = $request->get('b');

            if($bundle){
                $list = $ui_service->getEntityList($type, $query, $bundle);
                return $this->createJsonResponse($list);
            }

            $list = $ui_service->getEntityList($type, $query);
            return $this->createJsonResponse($list);

        }catch (\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    public function extractionContactsAction(Request $request, $extraction_id){
        try{
            $this->validateRequest($request);

            $format = $request->get('format', 'html');

            // Service
            $e_service = $this->get('extraction');

            // Current Extraction
            $extraction = $e_service->getEntity('extraction', $extraction_id, true);

            // Get all contacts in one Query (Better then using $extraction->getContacts())
            $contacts = $e_service->getExtractionContacts($extraction);

            if($format == 'html'){
                // Render Response
                return $this->render('@ListBrokingApp/Extraction/_partials/contacts_table.html.twig',
                    array(
                        'extraction' => $extraction,
                        'contacts' => $contacts
                    )
                );
            }

            return $this->createJsonResponse(array(
                "code" => 200,
                "response" => $contacts,
            ));

        }catch (\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Third step on Lead Extraction, Lead Extraction
     * Note: This is not really an Ajax only request
     * but it's a cool hack to fake an Ajax file download
     * @param $extraction_id
     * @param $extraction_template_id
     * @return Response
     * @throws InvalidExtractionException
     */
    public function extractionDownloadAction($extraction_id, $extraction_template_id){

        //Service
        $e_service = $this->get('extraction');

        // Current Extraction
        $extraction = $e_service->getEntity('extraction', $extraction_id, true, true);

        // Get all contacts in one Query (Better then using $extraction->getContacts())
        $contacts = $e_service->getExtractionContacts($extraction);

        /** @var Extraction $extraction */
        $filename = $e_service->exportExtraction($e_service->getEntity('extraction_template', $extraction_template_id), $contacts);

        // Generate response
        $response = new Response();

        // Set headers for file attachment
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-length', filesize($filename));

        // Sends a "file was downloaded" cookie
        $cookie = new Cookie('fileDownload', 'true', new \DateTime('+1 minute'));
        $response->headers->setCookie($cookie);

        // Send headers before outputting anything
        $response->sendHeaders();
        $response->setContent(readfile($filename));

        return $response;
    }

    /**
     * Generates the requested locks on a given extraction
     * @param Request $request
     * @param $extraction_id
     * @return JsonResponse
     */
    public function extractionLocksAction(Request $request, $extraction_id){
        try
        {
            $this->validateRequest($request);

            $lock_types = $request->get('lock_types', array());
            $e_service = $this->get('extraction');

            // Check if there are lock_types
            if(empty($lock_types)){
                return $this->createJsonResponse(array(
                    "code" => 400,
                    "response" => "No lock_type to generate locks",
                ));
            }

            // Current Extraction
            $extraction = $e_service->getEntity('extraction', $extraction_id, true, true);

            // Generate locks
            $e_service->generateLocks($extraction, $lock_types);

            return $this->createJsonResponse(array(
                "code" => 200,
                "response" => "Locks generated",
            ));

        }catch(\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Request $request
     * @param $extraction_id
     * @param $extraction_template_id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function extractionDeliverAction(Request $request, $extraction_id, $extraction_template_id){
        try{
            $this->validateRequest($request);

            $emails = $request->get('emails', array());
            $e_service = $this->get('extraction');

            // Check if there are emails to send the extraction
            if(empty($emails)){
                return $this->createJsonResponse(array(
                    "code" => 400,
                    "response" => "No emails to send to provided",
                ));
            }

            // Current Extraction
            $extraction = $e_service->getEntity('extraction', $extraction_id);

            $e_service->deliverExtraction($extraction, $emails);

            return $this->createJsonResponse(array(
                "code" => 200,
                "response" => "Emails sent",
            ));
        }catch(\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Excludes leads of a given Extraction
     * @param Request $request
     * @param $extraction_id
     * @param $lead_id
     * @return JsonResponse
     */
    public function extractionExcludeLeadAction(Request $request, $extraction_id, $lead_id){
        try{
            $this->validateRequest($request);

            $e_service = $this->get('extraction');

            $extraction = $e_service->getEntity('extraction', $extraction_id, true, true);

            $e_service->excludeLead($extraction,$lead_id);
            $e_service->deduplicateExtraction($extraction);

            return $this->createJsonResponse(array(
                "code" => 200,
                "response" => "success",
                "extraction_id" => $extraction_id,
                "lead_id" => $lead_id
            ));
        }catch(\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Used to upload a deduplication file and start persistence
     * @param Request $request
     * @param $extraction_id
     * @return JsonResponse
     */
    public function deduplicationAction(Request $request, $extraction_id){
        try{
            $this->validateRequest($request);

            // Service
            $e_service = $this->get('extraction');

            // Current Extraction
            $extraction = $e_service->getEntity('extraction', $extraction_id, true, true);

            // Handle the form and adds file to the Queue
            $form = $e_service->generateForm(new ExtractionDeduplicationType());
            $form->handleRequest($request);

            /** @var Queue $queue */
            $queue = $e_service->addDeduplicationFileToQueue($extraction, $form);

            return $this->createJsonResponse(array(
                "code" => 200,
                "response" => "deduplication has started",
                "filename" => $queue->getValue2(),
                "field" => $queue->getValue3(),
                "queue_id" => $queue->getId()
            ));
        }catch(\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    public function deduplicationQueueAction(Request $request, $extraction_id){
        try{
            $this->validateRequest($request);
            $e_service = $this->get('extraction');

            // Run Extraction
            $extraction = $e_service->getEntity('extraction', $extraction_id);

            //Check for Queues
            /** @var Queue[] $queues */
            $queues = $e_service->getQueues(AppService::DEDUPLICATION_QUEUE_TYPE);
            foreach($queues as $queue){
                if($queue->getValue1() == $extraction->getId()){
                    return $this->createJsonResponse(array(
                        "code" => 200,
                        "response" => "running",
                    ));
                }
            }

            return $this->createJsonResponse(array(
                "code" => 200,
                "response" => 'ended',
            ));
        }catch(\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    public function OppositionListImportAction(Request $request){
        try{
            $this->validateRequest($request);

            $s_service = $this->get('staging');

            $form = $s_service->generateForm('opposition_list_import');
            $form->handleRequest($request);

            $queue = $s_service->addOppositionListFileToQueue($form);
            return $this->createJsonResponse(array(
                "code" => 200,
                "response" => "List added to the queue",
                "type" => $queue->getValue1(),
                "filename" => $queue->getValue2(),
                "clear_old" => $queue->getValue3(),
                "queue_id" => $queue->getId()
            ));
        }catch(\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Action to download the
     * Note: This is not really an Ajax only request
     * but it's a cool hack to fake an Ajax file download
     * @return Response
     * @throws InvalidExtractionException
     */
    public function StagingContactImportTemplateAction(){

        //Service
        $s_service = $this->get('staging');

        $filename = $s_service->getStagingContactImportTemplate();

        // Generate response
        $response = new Response();

        // Set headers for file attachment
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-length', filesize($filename));

        // Send headers before outputting anything
        $response->sendHeaders();
        $response->setContent(readfile($filename));

        return $response;
    }

    public function StagingContactImportAction(Request $request){
        try{
            $this->validateRequest($request);

            $s_service = $this->get('staging');

            $form = $s_service->generateForm(new StagingContactImportType());
            $form->handleRequest($request);

            $queue = $s_service->addStagingContactsFileToQueue($form);
            return $this->createJsonResponse(array(
                "code" => 200,
                "response" => "List added to the queue",
                "type" => $queue->getValue1(),
                "filename" => $queue->getValue2(),
                "clear_old" => $queue->getValue3(),
                "queue_id" => $queue->getId()
            ));
        }catch(\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    public function OperationalEmailDeliverAction(Request $request){
        try{
            $this->validateRequest($request);

            /** @var AppService $a_service */
            $a_service = $this->get('app');

            $subject = $request->get('subject');
            $body = $request->get('body');
            $emails = explode(',',$request->get('emails'));

            $response = $a_service->deliverEmail('ListBrokingAppBundle:KitEmail:operational_email.html.twig', array(
                'body' => $body
            ),$subject, $emails);

            if($response != 1){
                return $this->createJsonResponse(array(
                        "code" => 500,
                        "response" => "Emails could not be delivered"
                    )
                );
            }
            return $this->createJsonResponse(array(
                    "code" => 200,
                    "response" => "Emails delivered"
                )
            );

        }catch(\Exception $e){
            return $this->createJsonResponse($e->getTrace(), $e->getCode());
        }
    }
    public function OperationalEmailPreviewAction(Request $request){
        try{
            $this->validateRequest($request);

            $body = $request->get('body');

        return $this->render('ListBrokingAppBundle:KitEmail:operational_email.html.twig', array(
            'body' => $body
        ));
        }catch(\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Generates a Json Response
     * @param $response
     * @param int $code
     * @return JsonResponse
     */
    private function createJsonResponse($response, $code = 200){

        // Handle exceptions that don't have a valid http code
        if(!is_int($code) || $code == '0'){
            $code = 500;
        }

        return new JsonResponse(array(
            "code" => $code,
            "response" => $response
        ), $code);
    }

    /**
     * Validates the Ajax Request
     * @param $request Request
     * @throws \Exception
     */
    private function validateRequest($request){
        if (!$request->isXmlHttpRequest()) {
            throw new \Exception("Only Xml Http Requests allowed", 400);
        }
    }
} 