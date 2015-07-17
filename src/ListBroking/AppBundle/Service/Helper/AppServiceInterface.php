<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\Helper;

use ListBroking\AppBundle\Service\Base\BaseServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

interface AppServiceInterface extends BaseServiceInterface
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
     * Generates a Response with File Attachment Headers and optional cookie
     *
     * @param      $filename
     * @param bool $with_cookie
     *
     * @return mixed
     */
    public function createAttachmentResponse($filename, $with_cookie = true);

    /**
     * Validates the Ajax Request
     *
     * @param $request Request
     *
     * @throws \Exception
     */
    public function validateAjaxRequest (Request $request);
}