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
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    public function countLeads(Request $request){

        try{
            //$this->validateRequest($request);
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
            //$this->validateRequest($request);

            $type = $request->get('type', '');
            $parent_type = $request->get('parent_type', '');
            $parent_id = $request->get('parent_id', '');
            $list = $this->ui_service->getEntityList($type, $parent_type, $parent_id);


            return $this->createJsonResponse($list);

        }catch (\Exception $e){

            return $this->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

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

    public function extractionExcludeLeadAction(Request $request, $extraction_id, $lead_id){
        try{
            $this->validateRequest($request);

            // Parse to integers
            $extraction_id = (int)$extraction_id;
            $lead_id = (int)$lead_id;
            $extraction = $this->e_service->getExtraction($extraction_id, true);
            if (!$extraction)
            {
                throw new HttpException(404, "Extraction not found!", null, array(), 404);
            }

            $filters = $extraction->getFilters();
            if(!array_key_exists('lead:id', $filters) || $filters['lead:id'] == null){
                $filters['lead:id'] = array();
            }

            if(in_array($lead_id, array_values($filters['lead:id']))){
                return $this->createJsonResponse(array(
                    "code" => 400,
                    "response" => 'Lead already excluded'
                ), 400);
            }

            array_push($filters['lead:id'], $lead_id);

            $extraction->setFilters($filters);
            $this->e_service->updateExtraction($extraction);

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