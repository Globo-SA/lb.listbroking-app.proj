<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\Helper;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

interface AppServiceInterface
{

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
    public function getEntityList ($type, $ids, $query, $bundle);

    /**
     * Generates a Form instance
     * @param      $type
     * @param null $action
     * @param null $data
     * @param bool $view
     *
     * @return mixed
     */
    public function generateForm ($type, $action = null, $data = null, $view = false);

    /**
     * Deliver emails using the Mailer System
     *
     * @param      $template
     * @param      $parameters
     * @param      $subject
     * @param      $emails
     * @param null $filename
     *
     * @internal param $body
     * @return int
     */
    public function deliverEmail ($template, $parameters, $subject, $emails, $filename = null);

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