<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Controller;

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\InvalidExtractionException;
use ListBroking\AppBundle\Service\Helper\MessagingServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
    public function pingAction(Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);


            return $a_service->createJsonResponse(array());
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

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

            $last = $a_service->findExceptions(5);

            return $a_service->createJsonResponse($last);
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

    /**
     * Checks if a producer is Locked from receiving new items
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function checkProducerAvailabilityAction (Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            // Services
            $m_service = $this->get('messaging');

            $producer_id = $request->get('producer_id');

            return $a_service->createJsonResponse(array(
                'importing' => $m_service->isProducerLocked($producer_id)
            ))
                ;
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Saves an OppositionList File and adds it to the
     * messaging system to be processed
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function oppositionListImportAction (Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            // Services
            $m_service = $this->get('messaging');
            $f_service = $this->get('file_handler');

            $producer_id = MessagingServiceInterface::OPPOSITION_LIST_IMPORT_PRODUCER;

            // Save Opposition File
            $form = $a_service->generateForm('opposition_list_import');
            $form->handleRequest($request);
            $file = $f_service->saveFormFile($form);

            $data = $form->getData();

            // Publish Extraction to the Queue
            $m_service->publishMessage($producer_id, array(
                'filename'        => $file->getRealPath(),
                'opposition_list' => $data['type'],
                'clear_old'       => $data['clear_old']
            ))
            ;

            $m_service->lockProducer($producer_id);

            return $a_service->createJsonResponse(array(
                'response' => 'Opposition is being imported',
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
        $a_service = $this->get('app');
        $f_service = $this->get('file_handler');

        list($filename, $password) = $f_service->generateFileFromArray(StagingContact::IMPORT_TEMPLATE_FILENAME, StagingContact::IMPORT_TEMPLATE_FILE_EXTENSION, array(StagingContact::$import_template), false);

        return $a_service->createAttachmentResponse($filename);
    }

    /**
     * Saves an database File and adds it to the
     * messaging system to be processed
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function stagingContactImportAction (Request $request)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            // Services
            $m_service = $this->get('messaging');
            $f_service = $this->get('file_handler');

            $producer_id = MessagingServiceInterface::STAGING_CONTACT_IMPORT_PRODUCER;

            // Save Opposition File
            $form = $a_service->generateForm('staging_contact_import');
            $form->handleRequest($request);
            $file = $f_service->saveFormFile($form);

            $data = $form->getData();

            // Publish Extraction to the Queue
            $m_service->publishMessage($producer_id, array(
                'filename' => $file->getRealPath(),
                'owner'    => $data['owner']->getName(),
                'update'   => $data['update']
            ))
            ;

            $m_service->lockProducer($producer_id);

            return $a_service->createJsonResponse(array(
                'response' => 'Database is being imported',
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

            $a_service = $this->get('app');

            $subject = $request->get('subject');
            $body = $request->get('body');
            $emails = explode(',', $request->get('emails'));

            $response = $a_service->deliverEmail('ListBrokingAppBundle:KitEmail:operational_email.html.twig', array(
                'body' => $body
            ), $subject, $emails)
            ;

            if ( $response !== 1 )
            {
                return $a_service->createJsonResponse(array(
                    'response' => 'Emails could not be delivered'
                ), 500)
                    ;
            }

            return $a_service->createJsonResponse(array(
                'response' => 'Emails delivered'
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