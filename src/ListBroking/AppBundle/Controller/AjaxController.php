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
            $last = $this->getDoctrine()->getRepository('ListBrokingExceptionHandlerBundle:ExceptionLog')
                ->findLastExceptions(new \DateTime('- 1 week'), false);

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
            $this->validateRequest($request);

            $ui_service = $this->get('ui');

            $type = $request->get('type', '');
            $parent_type = $request->get('parent_type', '');
            $parent_id = $request->get('parent_id', '');
            $list = $ui_service->getEntityList($type, $parent_type, $parent_id);

            return $this->createJsonResponse($list);

        }catch (\Exception $e){
            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Generic form submission action
     * @param Request $request
     * @param $form_name
     * @return JsonResponse
     */
    public function formSubmissionAction(Request $request, $form_name){
        try{
            $this->validateRequest($request);

            $ui_service = $this->get('ui');

            $result = $ui_service->submitForm($form_name, $request);
            if($result['success']){

                return $this->createJsonResponse(array_merge(
                        array("form_name" => $form_name), $result
                    )
                );
            }

            return $this->createJsonResponse(array_merge(
                array("form_name" => $form_name), $result
            ), 400);

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

            $a_service = $this->get('app');
            $e_service = $this->get('extraction');

            $extraction = $a_service->getEntity('extraction', $extraction_id, true, true);
            $e_service->excludeLeads($extraction, array(array('id' => $lead_id)));

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
     * Third step on Lead Extraction, Lead Extraction
     * Note: This is not really an Ajax only request
     * but it's a cool hack to fake an Ajax file download
     * @param $extraction_id
     * @param $extraction_template_id
     * @return Response
     * @throws InvalidExtractionException
     */
    public function extractionDownloadAction($extraction_id, $extraction_template_id){

        //Services
        $e_service = $this->get('extraction');
        $a_service = $this->get('app');

        // Current Extraction
        $extraction = $a_service->getEntity('extraction', $extraction_id, true, true);

        /** @var Extraction $extraction */
        $filename = $e_service->exportExtraction($a_service->getEntity('extraction_template', $extraction_template_id), $extraction->getContacts());

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
     * Used to upload a deduplication file and start persistence
     * @param Request $request
     * @param $extraction_id
     * @return JsonResponse
     */
    public function deduplicationAction(Request $request, $extraction_id){
        try{
            $this->validateRequest($request);

            // Services
            $u_service = $this->get('ui');
            $e_service = $this->get('extraction');
            $a_service = $this->get('app');

            // Current Extraction
            $extraction = $a_service->getEntity('extraction', $extraction_id, true, true);

            // Handle the form and adds file to the Queue
            $form = $u_service->generateForm(new ExtractionDeduplicationType());
            $form->handleRequest($request);
            $queue = $e_service->handleFileToQueue($form, $extraction);

            return $this->createJsonResponse(array(
                "code" => 200,
                "response" => "deduplication has started",
                "filename" => $queue->getFilePath(),
                "field" => $queue->getField(),
                "queue_id" => $queue->getId()
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