<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\APIBundle\Service;


use ListBroking\APIBundle\Repository\ORM\APITokenRepository;
use ListBroking\CoreBundle\Service\CoreService;
use ListBroking\LeadBundle\Service\ContactDetailsService;
use ListBroking\LeadBundle\Service\LeadService;

interface APIServiceInterface {
    /**
     * @param LeadService $leadService
     * @param CoreService $coreService
     * @param ContactDetailsService $contactDetailsService
     * @param APITokenRepository $APITokenRepository
     */
    public function __construct(LeadService $leadService, CoreService $coreService, ContactDetailsService $contactDetailsService, APITokenRepository $APITokenRepository);

    /**
     * @return mixed
     */
    public function processRequest($token);

    /**
     * @param $token
     * @return mixed
     */
    public function addToken($token);

    /**
     * @param $token_id
     * @return mixed
     */
    public function removeToken($token_id);

    /**
     * @param $token_id
     * @param bool $hydrate
     * @return mixed
     */
    public function getToken($token_id, $hydrate = false);

    /**
     * @param $token
     * @return mixed
     */
    public function updateToken($token);

    /**
     * @param $token
     * @return mixed
     */
    public function getTokenByName($token);
} 