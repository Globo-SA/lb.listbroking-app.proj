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


use ListBroking\UIBundle\Service\UIService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController {

    /**
     * @var UIService
     */
    private $ui_service;

    function __construct(UIService $ui_service){
        $this->ui_service = $ui_service;
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
     * Validates the Ajax Resquest
     * @param $request Request
     * @throws \Exception
     */
    private function validateRequest($request){
        if (!$request->isXmlHttpRequest()) {
            throw new \Exception("Only Xml Http Requests allowed", 400);
        }
    }
} 