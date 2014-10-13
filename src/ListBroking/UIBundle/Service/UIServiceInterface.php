<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\UIBundle\Service;


interface UIServiceInterface
{

    /**
     * Gets a list of entities using the services
     * provided in various bundles
     * @param $type
     * @param $parent
     * @param $parent_id
     * @throws \Exception
     * @internal param $name
     * @return mixed
     */
    function getEntityList($type, $parent, $parent_id);

    /**
     * @param $name
     * @param $request
     * @return mixed
     */
    function submitForm($name, $request);

    /**
     * Generates a new form view
     * @param $name
     * @param $type
     * @return mixed
     */
    function generateFormView($name, $type);

    /**
     * Generates a new CSRF token
     * @param $intention
     * @return mixed
     */
    function generateNewCsrfToken($intention);
}