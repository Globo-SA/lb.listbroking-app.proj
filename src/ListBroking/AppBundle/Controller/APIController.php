<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Controller;

use ListBroking\AppBundle\Exception\LeadValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class APIController extends Controller
{
    /**
     * Dumb Action that just saves the lead for the ETL process
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createStagingLeadAction(Request $request){
        try{

            $this->authenticate($request->get('username'), $request->get('token'));

            $s_service = $this->get('staging');
            $lead = $request->get('lead');

            if(!$lead){
                throw new LeadValidationException('Lead is empty');
            }
            $s_service->addStagingContact($lead);

            return $this->createJsonResponse('Lead added');
        }catch (\Exception $e){
                return $this->createJsonResponse($e->getMessage(), 400);
        }
    }

    /**
     * Simple API user authentication by username, token
     * and role ROLE_API_USER
     * @param $username
     * @param $token
     */
    private function authenticate($username, $token){
        $user = $this->get('fos_user.user_manager')->findUserBy(array('username' => $username, 'token' => $token));
        if(!$user || !$user->hasRole('ROLE_API_USER')){
            throw new AccessDeniedException();
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

//    public function testUploadAction(){
//        $this->api_service  = $this->get('listbroking.api.service');
//        $filename = $this->get('kernel')->getRootDir() . "/../web/uploads/teste1.xls";
//        $owner = 'adclick';
//        $source = 4;
//        $sub_category = 'mystic';
//        $country = 'pt';
//        $response = $this->api_service->setLeadsByCSV($filename, $owner, $source, $sub_category, $country);
//
//        return $response;
//    }
}