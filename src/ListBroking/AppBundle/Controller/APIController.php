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
        $a_service = $this->get('app');
        try
        {
            $this->checkCredentials($request);

            $lead = $request->get('lead');

            if ( ! $lead )
            {
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
            return $a_service->createJsonResponse($e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getActiveCampaignsAction(Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $this->checkCredentials($request);

            $e_service = $this->get('extraction');
            $end_date = $request->get('end_date');
            $start_date = $request->get('start_date');
            if ($start_date == null)
            {
                if ($end_date != null)
                {
                    return $this->createJsonResponse(Array("error" => "end date can't be defined without a start date"), 400);
                }
                $start_date = date('Y-m-1');
            }
            if ($end_date == null)
            {
                $end_date = date('Y-m-t');
            }
            $data = $e_service->getActiveCampaigns($start_date, $end_date, $request->get('page', 1), $request->get('page_size', 500));
            if ($data == null)
            {
                return $this->createJsonResponse(Array("error" => "invalid request"), 400);
            }
            return $this->createJsonResponse($data);
        } catch (\Exception $e)
        {
            $a_service->logError(sprintf("API Active Campaigns error: %s start_date: %s end_date: %s trace: %s", $e->getMessage(), $request->get('start_date'), $request->get('end_date'), $e->getTraceAsString()));
            return $this->createJsonResponse($e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getExtractionsRevenueAction(Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $this->checkCredentials($request);

            $e_service = $this->get('extraction');
            $end_date = $request->get('end_date');
            $start_date = $request->get('start_date');
            if ($start_date == null)
            {
                if ($end_date != null)
                {
                    return $this->createJsonResponse(Array("error" => "end date can't be defined without a start date"), 400);
                }
                $start_date = date('Y-m-1');
            }
            if ($end_date == null)
            {
                $end_date = date('Y-m-t');
            }
            $data = $e_service->getRevenue($start_date, $end_date, $request->get('page', 1), $request->get('page_size', 500));
            if ($data === null)
            {
                return $this->createJsonResponse(Array("error" => "invalid request"), 400);
            }
            return $this->createJsonResponse($data);
        } catch (\Exception $e)
        {
            $a_service->logError(sprintf("API Active Campaigns error: %s start_date: %s end_date: %s trace: %s", $e->getMessage(), $request->get('start_date'), $request->get('end_date'), $e->getTraceAsString()));
            return $this->createJsonResponse($e->getMessage(), 500);
        }
    }

    /**
     * Wrapper around the authenticate function to throw an exception when request isn't authenticated
     * @param Request $request
     * @throws AccessDeniedException
     */
    private function checkCredentials(Request $request)
    {
        if (! $this->authenticate($request->get('username'), $request->get('token')) )
        {
            throw new AccessDeniedException();
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