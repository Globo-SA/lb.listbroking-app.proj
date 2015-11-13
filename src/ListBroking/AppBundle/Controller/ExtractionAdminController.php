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
        if ( false === $this->admin->isGranted('EDIT') )
        {
            throw new AccessDeniedException();
        }

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
        if ( $this->getRestMethod() === 'POST' )
        {
            parent::createAction();

            $form = $this->admin->getForm();

            return new RedirectResponse($this->admin->generateUrl('filtering', array(
                'id'     => $form->getData()
                                 ->getId(),
                'is_new' => true
            )));
        }

        return parent::createAction();
    }

    /**
     * Redirect the user depend on this choice.
     *
     * @param object $extraction
     *
     * @return RedirectResponse
     */
    protected function redirectTo ($extraction)
    {
        $url = false;

        if ( null !==
             $this->get('request')
                  ->get('btn_update_and_list')
        )
        {
            $url = $this->admin->generateUrl('filtering');
        }
        if ( null !==
             $this->get('request')
                  ->get('btn_create_and_list')
        )
        {
            $url = $this->admin->generateUrl('filtering');
        }

        if ( null !==
             $this->get('request')
                  ->get('btn_create_and_create')
        )
        {
            $params = array();
            if ( $this->admin->hasActiveSubClass() )
            {
                $params['subclass'] = $this->get('request')
                                           ->get('subclass')
                ;
            }
            $url = $this->admin->generateUrl('filtering', $params);
        }

        if ( $this->getRestMethod() == 'DELETE' )
        {
            $url = $this->admin->generateUrl('list');
        }

        if ( ! $url )
        {
            $url = $this->admin->generateObjectUrl('filtering', $extraction);
        }

        return new RedirectResponse($url);
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
        $a_service = $this->get('app');
        $e_service = $this->get('extraction');
        $m_service = $this->get('messaging');

        // Current Extraction
        $extraction_id = $this->get('request')
                              ->get($this->admin->getIdParameter())
        ;
        /** @var Extraction $extraction */
        $extraction = $this->admin->getObject($extraction_id);

        if ( ! $this->admin->isGranted('SUPER_ADMIN') &&
             $this->getUser()
                  ->getId() !==
             $extraction->getCreatedBy()
                        ->getId()
        )
        {
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
                ));
            }
        }

        // Forms
        $extraction_form = $this->generateExtractionForm($extraction);
        $extraction_deduplication = $a_service->generateForm('extraction_deduplication');
        $extraction_locking = $a_service->generateForm('extraction_locking');

        $filters_form = $a_service->generateForm('filters', $this->generateUrl('admin_listbroking_app_extraction_filtering', array('id' => $extraction_id)), $extraction->getFilters());

        // Render Response
        return $this->render('@ListBrokingApp/Extraction/filtering.html.twig', array(
            'action'        => 'filtering',
            'lock_time'     => $e_service->findConfig('lock.time'),
            'preview_limit' => $e_service->findConfig('extraction.contact.show_limit'),
            'extraction'    => $extraction,
            'forms'         => array(
                'extraction'               => $extraction_form,
                'filters'                  => $filters_form->createView(),
                'extraction_deduplication' => $extraction_deduplication->createView(),
                'extraction_locking'       => $extraction_locking->createView(),
            ),
            'elements'      => $this->admin->getShow(),
        ));
    }

    /**
     * @inheritDoc
     */
    public function editAction ($id = null)
    {
        if ($this->getRestMethod() == 'POST')
        {
            // Services
            $a_service = $this->get('app');
            $e_service = $this->get('extraction');
            $m_service = $this->get('messaging');

            // Sonata doesn't not maintain the same form uniqid when changing pages
            // so old id needs to be forced
            $request = $this->container->get('request')->request->all();
            $form_data = $request[key($request)];
            $this->admin->setUniqid(key($request));

            /** @var Extraction $extraction */
            $id = $this->get('request')
                       ->get($this->admin->getIdParameter())
            ;

            $extraction = $this->admin->getObject($id);
            $old_quantity = $this->admin->getObject($id)
                                        ->getQuantity()
            ;

            // Edit the object as normal
            $response = parent::editAction($id);

            // If the quantity changed, mark the extraction to be re_run
            if ( $old_quantity != $form_data['quantity'] )
            {
                $extraction->setIsAlreadyExtracted(false);
                $a_service->updateEntity($extraction);

                $is_extraction_ready = $e_service->handleFiltration($extraction);
                if ( $is_extraction_ready )
                {

                    // Publish Extraction to the Queue
                    $m_service->publishMessage('run_extraction', array(
                        'object_id' => $extraction->getId()
                    ));
                }
            }

            return $response;
        }

        return parent::editAction($id);
    }

    private function generateExtractionForm (Extraction $extraction)
    {
        $this->admin->setSubject($extraction);

        /** @var $form \Symfony\Component\Form\Form */
        $extraction_form = $this->admin->getForm();
        $extraction_form->setData($extraction);

        $view = $extraction_form->createView();
        $this->get('twig')
             ->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $view;
    }
}
