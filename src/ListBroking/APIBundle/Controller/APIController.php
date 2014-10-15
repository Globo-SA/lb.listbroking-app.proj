<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\APIBundle\Controller;

use ListBroking\APIBundle\Service\APIService;
use Symfony\Component\HttpFoundation\Request;

class APIController
{
    protected $api_service;

    /**
     * @param APIService $APIService
     */
    function __construct(APIService $APIService)
    {
        $this->api_service  = $APIService;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function setLeadAction(Request $request){
        $token = $request->get('token');
        return $this->api_service->processRequest($token);
    }
}