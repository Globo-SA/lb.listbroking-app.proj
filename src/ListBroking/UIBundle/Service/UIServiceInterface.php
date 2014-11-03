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


use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

interface UIServiceInterface
{

    /**
     * Group leads by lock and count them
     * @return array
     */
    public function countByLock();

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

    /**
     * @param $form_name
     * @param $request
     * @return mixed
     */
    function submitForm($form_name, $request);

    /**
     * Generates a new form view
     * @param $type
     * @param bool $view
     * @param null $data
     * @param $action
     * @return FormBuilderInterface|Form
     */
    function generateForm($type, $action = null, $data = null, $view = false);

    /**
     * Generates a new CSRF token
     * @param $intention
     * @return mixed
     */
    function generateNewCsrfToken($intention);
}