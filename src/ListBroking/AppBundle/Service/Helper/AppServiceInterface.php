<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Helper;


use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigBuilderInterface;

interface AppServiceInterface {

    /**
     * @param $code
     * @param bool $hydrate
     * @return mixed
     */
    public function getCountryByCode($code, $hydrate = true);

    /**
     * Gets a list of entities using the services
     * provided in various bundles
     * @param $type
     * @param $query
     * @param string $bundle
     * @throws \Exception
     * @return mixed
     */
    public function getEntityList($type, $query, $bundle = 'ListBrokingAppBundle');

    /**
     * Deliver emails using the system
     * @param $template
     * @param $parameters
     * @param $subject
     * @param $emails
     * @internal param $body
     * @return int
     */
    public function deliverEmail($template, $subject, $parameters, $emails);
}