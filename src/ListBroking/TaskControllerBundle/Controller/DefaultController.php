<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\TaskControllerBundle\Controller;


use ListBroking\TaskControllerBundle\Entity\Queue;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

    public function findQueuesByTypeAction(Request $request){
        try{
            $this->validateRequest($request);

            $service = $this->get('task');

            $type = $request->get('type');
            $key = $request->get('key');
            $value = $request->get('value');

            if(empty($type)){
                throw new \Exception('type is required', 400);
            }

            $has_queue = false;
            //Check for Queues
            /** @var Queue[] $queues */
            $queues = $service->findQueuesByType($type);
            if($queues || ($queues && !empty($key) && !empty($value))){
                foreach($queues as $queue){

                    switch($key){
                        case 'value1':
                            $has_queue = $queue->getValue1() == $value;

                            break;
                        case 'value2':
                            $has_queue = $queue->getValue2() == $value;

                            break;
                        case 'value3':
                            $has_queue = $queue->getValue3() == $value;

                            break;
                        case 'value4':
                            $has_queue = $queue->getValue4() == $value;

                            break;
                        default:
                            $has_queue = true;
                    }
                }

                if($has_queue){
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