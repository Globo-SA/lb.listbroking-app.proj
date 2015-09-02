<?php
/**
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Controller;

use ListBroking\AppBundle\Exception\Validation\LeadValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class APIController extends Controller
{

    /**
     * Dumb Action that just saves the lead for the ETL process
     *
     * @param Request $request
     *
     * @throws LeadValidationException
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createStagingLeadAction (Request $request)
    {
        try
        {
            $is_authenticated = $this->authenticate($request->get('username'), $request->get('token'));

            if ( ! $is_authenticated )
            {
                $code = 403;

                throw new AccessDeniedException();
            }

            $lead = $request->get('lead');

            if ( ! $lead )
            {
                $code = 400;

                throw new LeadValidationException('Lead is empty');
            }

            $s_service = $this->get('staging');
            $s_service->addStagingContact($lead);
            $s_service->flushAll();
            $s_service->clearEntityManager();

            return $this->createJsonResponse('Lead added');
        }
        catch ( \Exception $e )
        {
            return $this->createJsonResponse($e->getMessage(), $code);
        }
    }

    /**
     * Simple API user authentication by username, token
     * and role ROLE_API_USER
     *
     * @param $username
     * @param $token
     *
     * @return bool
     */
    private function authenticate ($username, $token)
    {
        $user = $this->get('fos_user.user_manager')->findUserBy(array('username' => $username, 'token' => $token));

        return $user && $user->hasRole('ROLE_API_USER');
    }

    /**
     * Generates a Json Response
     *
     * @param     $response
     * @param int $code
     *
     * @return JsonResponse
     */
    private function createJsonResponse ($response, $code = 200)
    {
        return new JsonResponse(array(
            'code'     => $code,
            'response' => $response
        ), $code);
    }
}