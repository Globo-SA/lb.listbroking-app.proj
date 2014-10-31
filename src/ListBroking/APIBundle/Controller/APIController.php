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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function setLeadAction(){
        return $this->api_service->processRequest();
    }
}