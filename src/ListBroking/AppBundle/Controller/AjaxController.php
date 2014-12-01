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

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Exception\InvalidExtractionException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller {


    public function lastExceptionsAction(){

        $last = $this->getDoctrine()->getRepository('ListBrokingExceptionHandlerBundle:ExceptionLog')
            ->findBy(array(), null, 10);

        return $this->createJsonResponse($last);
    }
    /**
     * Counts the number of leads by lock for the dashboard
     * @param Request $request
     * @return JsonResponse
     */
    public function countLeadsAction(Request $request){

        try{
            $this->validateRequest($request);

            $ui_service = $this->get('ui');

            $leads_by_lock = $ui_service->countByLock();
            return $this->createJsonResponse($leads_by_lock);
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
     * @param Request $request
     * @param $extraction_id
     * @param $extraction_template_id
     * @return Response
     * @throws InvalidExtractionException
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