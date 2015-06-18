<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Controller;

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Exception\InvalidExtractionException;
use ListBroking\AppBundle\Form\StagingContactImportType;
use ListBroking\AppBundle\Service\Helper\AppService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{

    /**
     * Gets the last exceptions thrown by the system
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function lastExceptionsAction (Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            $a_service = $this->get('app');
            $last = $a_service->getExceptions(5);

            return $a_service->createJsonResponse($last);
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Publishes a new message to the Queue System
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function publishMessageAction (Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            $m_service = $this->get('messaging');

            $producer_id = $request->get('producer');
            $msg = $request->get('message');

            // Publish it !
            $m_service->publishMessage($producer_id, $msg);

            return $a_service->createJsonResponse(array());
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Used to get lists of entities
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listsAction (Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            $a_service = $this->get('app');

            $type = $request->get('type', '');
            $query = $request->get('q', '');
            $ids = $request->get('id', '');
            $bundle = $request->get('b', 'ListBrokingAppBundle');

            $list = $a_service->getEntityList($type, $ids, $query, $bundle);

            return $a_service->createJsonResponse($list);
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    public function oppositionListImportAction (Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            $s_service = $this->get('staging');

            $form = $s_service->generateForm('opposition_list_import');
            $form->handleRequest($request);

            $queue = $s_service->addOppositionListFileToQueue($form);

            return $a_service->createJsonResponse(array(
                "response"  => "List added to the queue",
                "type"      => $queue->getValue1(),
                "filename"  => $queue->getValue2(),
                "clear_old" => $queue->getValue3(),
                "queue_id"  => $queue->getId()
            ))
                ;
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Action to download the
     * Note: This is not really an Ajax only request
     * but it's a cool hack to fake an Ajax file download
     * @return Response
     * @throws InvalidExtractionException
     */
    public function stagingContactImportTemplateAction ()
    {
        //Service
        $s_service = $this->get('staging');

        $filename = $s_service->getStagingContactImportTemplate();

        // Generate response
        $response = new Response();

        // Set headers for file attachment
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-length', filesize($filename));

        // Send headers before outputting anything
        $response->sendHeaders();
        $response->setContent(readfile($filename));

        return $response;
    }

    public function stagingContactImportAction (Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            $s_service = $this->get('staging');

            $form = $s_service->generateForm(new StagingContactImportType());
            $form->handleRequest($request);

            $queue = $s_service->addStagingContactsFileToQueue($form);

            return $a_service->createJsonResponse(array(
                "response"  => "List added to the queue",
                "type"      => $queue->getValue1(),
                "filename"  => $queue->getValue2(),
                "clear_old" => $queue->getValue3(),
                "queue_id"  => $queue->getId()
            ))
                ;
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    public function operationalEmailDeliverAction (Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            /** @var AppService $a_service */
            $a_service = $this->get('app');

            $subject = $request->get('subject');
            $body = $request->get('body');
            $emails = explode(',', $request->get('emails'));

            $response = $a_service->deliverEmail('ListBrokingAppBundle:KitEmail:operational_email.html.twig', array(
                'body' => $body
            ), $subject, $emails)
            ;

            if ( $response != 1 )
            {
                return $a_service->createJsonResponse(array(
                    "response" => "Emails could not be delivered"
                ), 500)
                    ;
            }

            return $a_service->createJsonResponse(array(
                "response" => "Emails delivered"
            ))
                ;
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getTrace(), $e->getCode());
        }
    }

    public function operationalEmailPreviewAction (Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            $body = $request->get('body');

            return $this->render('ListBrokingAppBundle:KitEmail:operational_email.html.twig', array(
                'body' => $body
            ))
                ;
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }


} 