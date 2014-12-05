<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service;


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
     * Generates a new form view
     * @param $type
     * @param bool $view
     * @param null $data
     * @param $action
     * @return FormConfigBuilderInterface|Form
     */
    function generateForm($type, $action = null, $data = null, $view = false);
}