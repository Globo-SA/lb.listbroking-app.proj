<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Controller;

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Exception\InvalidExtractionException;
use ListBroking\AppBundle\Form\ExtractionDeduplicationType;
use ListBroking\AppBundle\PHPExcel\FileHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxExtractionController extends Controller
{

    /**
     * Finds an Extraction by it's id and returns it as a json
     *
     * @param Request $request
     * @param         $extraction_id
     *
     * @return JsonResponse
     */
    public function findExtractionAction (Request $request, $extraction_id)
    {
        $a_service = $this->get('app');
        try
        {
            //            $a_service->validateAjaxRequest($request);

            // Service
            $e_service = $this->get('extraction');

            // Current Extraction
            $extraction = $e_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);

            $query = json_decode($extraction['query'], 1);
            $extraction['query'] = array();

            // Format SQL for better readability
            $extraction['query']['dql'] = \SqlFormatter::format($query['dql']);

            return $a_service->createJsonResponse(array(
                "code"     => 200,
                "response" => $extraction,
            ))
                ;
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Gets the Extraction Preview Table, can be rendered as html or JSON
     *
     * @param Request $request
     * @param         $extraction_id
     *
     * @return JsonResponse|Response
     */
    public function extractionPreviewAction (Request $request, $extraction_id)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            $format = $request->get('format', 'html');

            // Service
            $e_service = $this->get('extraction');

            // Current Extraction
            $extraction = $e_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);

            // Preview limit
            $preview_limit = $e_service->getConfig('extraction.contact.show_limit')
            ;

            // Get all contacts in one Query (Better then using $extraction->getContacts())
            $extraction_contacts_preview = $e_service->findExtractionContacts($extraction, $preview_limit);
            if ( $format == 'html' )
            {
                // Render Response
                return $this->render('@ListBrokingApp/Extraction/_partials/contacts_table.html.twig', array(
                    'preview_limit'               => '',
                    'extraction'                  => $extraction,
                    'extraction_contacts_preview' => $extraction_contacts_preview
                ))
                    ;
            }

            return $a_service->createJsonResponse(array(
                "code"                        => 200,
                'extraction_contacts_preview' => $extraction_contacts_preview
            ))
                ;
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Gets the Extraction Summary Table, can be rendered as html or JSON
     *
     * @param Request $request
     * @param         $extraction_id
     *
     * @return JsonResponse|Response
     */
    public function extractionSummaryAction (Request $request, $extraction_id)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            $format = $request->get('format', 'html');

            // Service
            $e_service = $this->get('extraction');

            // Current Extraction
            $extraction = $e_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);

            // Extraction Summary
            $extraction_summary = $e_service->findExtractionSummary($extraction);

            if ( $format == 'html' )
            {
                // Render Response
                return $this->render('@ListBrokingApp/Extraction/_partials/extraction_summary.html.twig', array(
                    'extraction_summary' => $extraction_summary,

                ))
                    ;
            }

            return $a_service->createJsonResponse(array(
                "code"     => 200,
                "response" => $extraction_summary,
            ))
                ;
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Downloads the Extraction for deduplication or finalization
     *
     * @param $extraction_id
     * @param $extraction_template_id
     *
     * @return Response
     * @throws InvalidExtractionException
     */
    public function extractionDownloadAction ($extraction_id, $extraction_template_id)
    {
        //Service
        $e_service = $this->get('extraction');

        // Current Extraction
        $extraction = $e_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);

        // Get all contacts in one Query (Better then using $extraction->getContacts())
        $contacts = $e_service->findExtractionContacts($extraction);

        /** @var Extraction $extraction */
        $filename = $e_service->exportExtraction($e_service->findEntity('ListBrokingAppBundle:ExtractionTemplate', $extraction_template_id), $contacts);

        // Generate response
        $response = new Response();

        // Set headers for file attachment
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-length', filesize($filename));

        // Sends a "file was downloaded" cookie
        $cookie = new Cookie('fileDownload', 'true', new \DateTime('+1 minute'), '/', null, false, false);
        $response->headers->setCookie($cookie);

        // Send headers before outputting anything
        $response->sendHeaders();
        $response->setContent(readfile($filename));

        return $response;
    }

    /**
     * Publishes the extraction for deduplication
     *
     * @param Request $request
     * @param         $extraction_id
     *
     * @return JsonResponse
     */
    public function extractionDeduplicationAction (Request $request, $extraction_id)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            // Services
            $m_service = $this->get('messaging');

            // Current Extraction
            /** @var Extraction $extraction */
            $extraction = $m_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);
            $extraction->setIsDeduplicating(true);

            $m_service->updateEntity($extraction);

            // Handle the form and adds file to the Queue
            $form = $m_service->generateForm(new ExtractionDeduplicationType());
            $form->handleRequest($request);
            $data = $form->getData();

            $file_handler = new FileHandler();

            /** @var UploadedFile $file */
            $file = $file_handler->saveFormFile($form);

            // Deduplicate field
            $field = isset($data['field']) ? $data['field'] : 'lead_id';
            $deduplication_type = isset($data['deduplication_type']) ? $data['deduplication_type'] : ExtractionDeduplication::EXCLUDE_TYPE;

            // Publish Extraction to the Queue
            $m_service->publishMessage('deduplicate_extraction', array(
                'object_id'          => $extraction->getId(),
                'filename'           => $file->getRealPath(),
                'deduplication_type' => $deduplication_type,
                'field'              => $field
            ))
            ;

            return $a_service->createJsonResponse(array(), 200);
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Generates the requested locks on a given extraction
     *
     * @param Request $request
     * @param         $extraction_id
     *
     * @return JsonResponse
     */
    public function extractionLocksAction (Request $request, $extraction_id)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            // Services
            $m_service = $this->get('messaging');

            $lock_types = $request->get('lock_types', array());

            // Current Extraction
            /** @var Extraction $extraction */
            $extraction = $m_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);
            $extraction->setStatus(Extraction::STATUS_FINAL);
            $extraction->setIsLocking(true);

            $m_service->updateEntity($extraction);

            // Check if there are lock_types
            if ( ! empty($lock_types) )
            {
                // Publish Extraction to the Queue
                $m_service->publishMessage('lock_extraction', array(
                    'object_id'  => $extraction_id,
                    'lock_types' => $lock_types
                ))
                ;
            }

            return $a_service->createJsonResponse(array(
                "response" => "Locks being generated",
            ))
                ;
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Request $request
     * @param         $extraction_id
     * @param         $extraction_template_id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function extractionDeliverAction (Request $request, $extraction_id, $extraction_template_id)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            // Services
            $m_service = $this->get('messaging');

            // Current Extraction
            /** @var Extraction $extraction */
            $extraction = $m_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);
            $extraction->setIsDelivering(true);

            $m_service->updateEntity($extraction);

            // Publish Extraction to the Queue
            $m_service->publishMessage('deliver_extraction', array(
                'object_id'              => $extraction->getId(),
                'extraction_template_id' => $extraction_template_id,
                'email'                  => $a_service->findUser()
                                                      ->getEmail()
            ))
            ;

            return $a_service->createJsonResponse(array(
                "response" => "Email being generated",
            ))
                ;
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    //    /**
    //     * Excludes leads of a given Extraction
    //     *
    //     * @param Request $request
    //     * @param         $extraction_id
    //     * @param         $lead_id
    //     *
    //     * @return JsonResponse
    //     */
    //    public function extractionExcludeLeadAction (Request $request, $extraction_id, $lead_id)
    //    {
    //        $a_service = $this->get('app');
    //        try
    //        {
    //            $a_service->validateAjaxRequest($request);
    //
    //            $e_service = $this->get('extraction');
    //
    //            $extraction = $e_service->getEntity('extraction', $extraction_id, true, true);
    //
    //            $e_service->excludeLead($extraction, $lead_id);
    //            $e_service->deduplicateExtraction($extraction);
    //
    //            return $a_service->createJsonResponse(array(
    //                "response"      => "success",
    //                "extraction_id" => $extraction_id,
    //                "lead_id"       => $lead_id
    //            ))
    //                ;
    //        }
    //        catch ( \Exception $e )
    //        {
    //            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
    //        }
    //    }

    //    public function extractionDeduplicationQueueAction (Request $request, $extraction_id)
    //    {
    //        $a_service = $this->get('app');
    //        try
    //        {
    //            $a_service->validateAjaxRequest($request);
    //
    //            $e_service = $this->get('extraction');
    //
    //            // Run Extraction
    //            $extraction = $e_service->getEntity('extraction', $extraction_id);
    //
    //            //Check for Queues
    //            /** @var Queue[] $queues */
    //            $queues = $e_service->getQueues(AppService::DEDUPLICATION_QUEUE_TYPE);
    //            foreach ( $queues as $queue )
    //            {
    //                if ( $queue->getValue1() == $extraction->getId() )
    //                {
    //                    return $a_service->createJsonResponse(array(
    //                        "code"     => 200,
    //                        "response" => "running",
    //                    ))
    //                        ;
    //                }
    //            }
    //
    //            return $a_service->createJsonResponse(array(
    //                "code"     => 200,
    //                "response" => 'ended',
    //            ))
    //                ;
    //        }
    //        catch ( \Exception $e )
    //        {
    //            return $a_service->createJsonResponse($e->getMessage(), $e->getCode());
    //        }
    //    }
}