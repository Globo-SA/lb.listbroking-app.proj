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

use Ladybug\Plugin\Symfony2\Inspector\Object\Symfony\Component\HttpFoundation\Request;
use ListBroking\APIBundle\Service\APIService;

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
        $token['name'] = $request->get('token_name');
        $token['key'] = $request->get('token');
        $lead = $request->get('lead');
        $this->api_service->setLead($lead);
        return $this->api_service->processRequest($token);
    }
}