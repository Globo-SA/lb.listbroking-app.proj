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
use ListBroking\LeadBundle\Exception\LeadValidationException;
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
     */
    public function getLeadAction(Request $request){
        if (!$request->isMethod('GET')){
            // TODO: throw new APIException or just error??
        }

        try{
            $this->api_service->processRequest();
        } catch (LeadValidationException $e){
            $response = $e->getMessage();
        }
    }
}