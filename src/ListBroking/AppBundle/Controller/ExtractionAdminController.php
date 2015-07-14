<?php

namespace ListBroking\AppBundle\Controller;

use ListBroking\AppBundle\Entity\Extraction;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExtractionAdminController extends CRUDController
{

    /**
     * Clones a given extraction and resets it's status
     * @return RedirectResponse
     */
    public function cloneAction ()
    {
        // Services
        $e_service = $this->get('extraction');

        $id = $this->get('request')
                   ->get($this->admin->getIdParameter())
        ;

        $extraction = $this->admin->getObject($id);

        if ( ! $extraction )
        {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        /** @var Extraction $clonedObject */
        $new_extraction = $e_service->cloneExtraction($extraction);

        $this->admin->create($new_extraction);

        $this->addFlash('sonata_flash_success', 'Extraction successfully duplicated');

        return new RedirectResponse($this->admin->generateUrl('filtering', array('id' => $new_extraction->getId(), 'is_new' => true)));
    }

    /**
     * Create action and redirect to filtering
     * @return RedirectResponse
     * @throws AccessDeniedException If access is not granted
     */
    public function createAction ()
    {
        if ( $this->getRestMethod() == 'POST' )
        {
            parent::createAction();

            $form = $this->admin->getForm();

            return new RedirectResponse($this->admin->generateUrl('filtering', array(
                'id' => $form->getData()
                             ->getId(),
                'is_new' => true
            )));
        }

        return parent::createAction();
    }

    /**
     * Extraction filter interface
     * @return Response
     */
    public function filteringAction ()
    {
        if ( false === $this->admin->isGranted('EDIT') )
        {
            throw new AccessDeniedException();
        }

        $is_new = $this->get('request')
                          ->get('is_new')
        ;

        // Services
        $e_service = $this->get('extraction');
        $m_service = $this->get('messaging');

        // Current Extraction
        $extraction_id = $this->get('request')
                              ->get($this->admin->getIdParameter())
        ;
        /** @var Extraction $extraction */
        $extraction = $e_service->findEntity('ListBrokingAppBundle:Extraction', $extraction_id);

        if($this->getUser() != $extraction->getCreatedBy() && !$this->admin->isGranted('SUPER_ADMIN')){
            throw new AccessDeniedException('You can only edit extractions created by you ');
        }

        // Handle Filters and update Extraction
        if ( ! $is_new )
        {
            $is_extraction_ready = $e_service->handleFiltration($extraction);
            if ( $is_extraction_ready )
            {

                // Publish Extraction to the Queue
                $m_service->publishMessage('run_extraction', array(
                    'object_id' => $extraction->getId()
                ))
                ;
            }
        }

        // Forms
        $extraction_deduplication = $e_service->generateForm('extraction_deduplication');
        $extraction_locking = $e_service->generateForm('extraction_locking');

        $filters_form = $e_service->generateForm('filters', $this->generateUrl('admin_listbroking_app_extraction_filtering', array('id' => $extraction_id)), $extraction->getFilters());

        // Render Response
        return $this->render('@ListBrokingApp/Extraction/filtering.html.twig', array(
            'action'        => 'filtering',
            'lock_time'     => $e_service->getConfig('lock.time'),
            'preview_limit' => $e_service->getConfig('extraction.contact.show_limit'),
            'extraction'    => $extraction,
            'forms'         => array(
                'filters'                  => $filters_form->createView(),
                'extraction_deduplication' => $extraction_deduplication->createView(),
                'extraction_locking'       => $extraction_locking->createView(),
            ),
            'elements'      => $this->admin->getShow(),
        ))
            ;
    }
}
