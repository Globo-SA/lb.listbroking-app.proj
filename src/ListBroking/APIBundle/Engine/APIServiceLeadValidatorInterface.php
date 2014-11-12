<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\APIBundle\Engine;



interface APIServiceLeadValidatorInterface {
    public function __construct();

    /**
     * @param Request $request
     * @return mixed
     */
    public function checkEmptyFields($lead);
}