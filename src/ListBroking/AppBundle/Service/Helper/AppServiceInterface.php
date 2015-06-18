<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Helper;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

interface AppServiceInterface
{

    /**
     * @param      $code
     * @param bool $hydrate
     *
     * @return mixed
     */
    public function getCountryByCode ($code, $hydrate = true);

    /**
     * Gets a list of entities using the services
     * provided in various bundles
     *
     * @param        $type
     * @param        $ids
     * @param string $query
     * @param string $bundle
     *
     * @throws \Exception
     * @return mixed
     */
    public function getEntityList($type, $ids, $query, $bundle);

    /**
     * Deliver emails using the system
     *
     * @param $template
     * @param $parameters
     * @param $subject
     * @param $emails
     *
     * @internal param $body
     * @return int
     */
    public function deliverEmail ($template, $subject, $parameters, $emails);

    /**
     * Generates a Json Response
     *
     * @param     $response
     * @param int $code
     *
     * @return JsonResponse
     */
    public function createJsonResponse ($response, $code = 200);

    /**
     * Validates the Ajax Request
     *
     * @param $request Request
     *
     * @throws \Exception
     */
    public function validateAjaxRequest (Request $request);
}