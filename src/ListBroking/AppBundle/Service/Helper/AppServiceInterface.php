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
     * @param $parent_type
     * @param $parent_id
     * @internal param $parent
     * @internal param $name
     * @return mixed
     */
    function getEntityList($type, $parent_type, $parent_id);

    //TODO: Check if this is still needed
//    /**
//     * @param $form_name
//     * @param $request
//     * @return mixed
//     */
//    function submitForm($form_name, $request);
//
//    /**
//     * Generates a new CSRF token
//     * @param $intention
//     * @return mixed
//     */
//    function generateNewCsrfToken($intention);

}