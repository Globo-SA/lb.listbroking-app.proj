<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Engine;


use Symfony\Component\HttpFoundation\Request;

interface LeadValidatorInterface {

    /**
     * @param $service
     * @param $request
     */
    public function __construct($service, Request $request);

    /**
     * @param $value
     * @param $field
     * @return mixed
     */
    public function validateEmpty($value, $field);

    /**
     * @return mixed
     */
    public function getField();
} 