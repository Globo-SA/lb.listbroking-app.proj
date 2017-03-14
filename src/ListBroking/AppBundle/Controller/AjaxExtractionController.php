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
use ListBroking\AppBundle\Form\ExtractionDeduplicationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxExtractionController extends Controller
{

    /**
     * Gets the last exceptions thrown by the system
     *
     * @param Request $request
     * @param         $extraction_id
     *
     * @return JsonResponse
     */
    public function findLatestExtractionLogAction (Request $request, $extraction_id)
    {
        $a_service = $this->get('app');
        try
        {
            $a_service->validateAjaxRequest($request);

            $e_service = $this->get('extraction');

            $extraction = $e_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);

            $last = $e_service->findLastExtractionLog($extraction, 3);

            return $a_service->createJsonResponse($last);
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), 500);
        }
    }

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
            $a_service->validateAjaxRequest($request);

            // Service
            $e_service = $this->get('extraction');

            // Current Extraction
            $extraction = $e_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);

            // Serialize it as an array
            $serializer = $this->get('jms_serializer');
            $extraction = json_decode($serializer->serialize($extraction, 'json'), 1);

            $query = json_decode($extraction['query'], 1);
            $extraction['query'] = array();

            // Format SQL for better readability
            $extraction['query']['dql'] = \SqlFormatter::format($query['dql']);

            return $a_service->createJsonResponse(array(
                'code'     => 200,
                'response' => $extraction,
            ));
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), 500);
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
            $preview_limit = $e_service->findConfig('extraction.contact.show_limit');

            // Get all contacts in one Query (Better then using $extraction->getContacts())
            $extraction_contacts_preview = $e_service->findExtractionContacts($extraction, $preview_limit);
            if ( $format === 'html' )
            {
                // Render Response
                return $this->render('@ListBrokingApp/Extraction/_partials/contacts_table.html.twig', array(
                    'preview_limit'               => '',
                    'extraction'                  => $extraction,
                    'extraction_contacts_preview' => $extraction_contacts_preview
                ));
            }

            return $a_service->createJsonResponse(array(
                'code'                        => 200,
                'extraction_contacts_preview' => $extraction_contacts_preview
            ));
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), 500);
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

            if ( $format === 'html' )
            {
                // Render Response
                return $this->render('@ListBrokingApp/Extraction/_partials/extraction_summary.html.twig', array(
                    'extraction_summary' => $extraction_summary,

                ));
            }

            return $a_service->createJsonResponse(array(
                'code'     => 200,
                'response' => $extraction_summary,
            ));
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), 500);
        }
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
            $f_service = $this->get('file_handler');

            // Current Extraction
            /** @var Extraction $extraction */
            $extraction = $a_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);
            $extraction->setIsDeduplicating(true);

            $a_service->updateEntity($extraction);

            // Handle the form and adds file to the Queue
            $form = $a_service->generateForm(new ExtractionDeduplicationType());
            $form->handleRequest($request);
            $data = $form->getData();

            /** @var UploadedFile $file */
            $file = $f_service->saveFormFile($form);

            // Deduplicate field
            $field = isset($data['field']) ? $data['field'] : 'lead_id';
            $deduplication_type = isset($data['deduplication_type']) ? $data['deduplication_type'] : Extraction::EXCLUDE_DEDUPLICATION_TYPE;

            // Publish Extraction to the Queue
            $m_service->publishMessage('deduplicate_extraction', array(
                'object_id'          => $extraction->getId(),
                'filename'           => $file->getRealPath(),
                'deduplication_type' => $deduplication_type,
                'field'              => $field
            ));

            return $a_service->createJsonResponse(array(), 200);
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), 500);
        }
    }

    /**
     * Publishes the extraction for locking
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
            $extraction = $a_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);
            $extraction->setStatus(Extraction::STATUS_FINAL);
            $extraction->setIsLocking(true);

            $a_service->updateEntity($extraction);

            // Check if there are lock_types
            if ( ! empty($lock_types) )
            {
                // Publish Extraction to the Queue
                $m_service->publishMessage('lock_extraction', array(
                    'object_id'  => $extraction_id,
                    'lock_types' => $lock_types
                ));
            }

            return $a_service->createJsonResponse(array(
                'response' => 'Locks being generated',
            ));
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), 500);
        }
    }

    /**
     * Publishes the extraction for delivery
     *
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
            $extraction = $a_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);
            $extraction->setIsDelivering(true);

            $a_service->updateEntity($extraction);

            // Publish Extraction to the Queue
            $m_service->publishMessage('deliver_extraction', array(
                'object_id'              => $extraction->getId(),
                'extraction_template_id' => $extraction_template_id,
                'email'                  => $a_service->findUser()
                                                      ->getEmail()
            ));

            return $a_service->createJsonResponse(array(
                'response' => 'Email being generated',
            ));
        }
        catch ( \Exception $e )
        {
            return $a_service->createJsonResponse($e->getMessage(), 500);
        }
    }
}