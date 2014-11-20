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


use ListBroking\ExtractionBundle\Service\ExtractionService;
use ListBroking\UIBundle\Service\UIService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController {

    /**
     * @var UIService
     */
    private $ui_service;

    /**
     * @var ExtractionService
     */
    private $e_service;

    function __construct(UIService $ui_service, ExtractionService $e_service){
        $this->ui_service = $ui_service;
        $this->e_service = $e_service;
    }

    /**
     * Counts the number of leads by lock for the dashboard
     * @param Request $request
     * @return JsonResponse
     */
    public function countLeads(Request $request){

        try{
            $this->validateRequest($request);
            $leads_by_lock = $this->ui_service->countByLock();
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

            $type = $request->get('type', '');
            $parent_type = $request->get('parent_type', '');
            $parent_id = $request->get('parent_id', '');
            $list = $this->ui_service->getEntityList($type, $parent_type, $parent_id);

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

            $result = $this->ui_service->submitForm($form_name, $request);

            if($result['success']){

                return $this->createJsonResponse(
                    array_merge(
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

            $extraction = $this->e_service->getExtraction($extraction_id, true);
            $this->e_service->excludeLeads($extraction, array(array('id' => $lead_id)));

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