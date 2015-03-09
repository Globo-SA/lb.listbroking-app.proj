<?php

namespace ListBroking\AppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Form\ExtractionDeduplicationType;
use ListBroking\AppBundle\Service\Helper\AppService;
use ListBroking\TaskControllerBundle\Entity\Queue;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExtractionAdminController extends CRUDController
{

    public function cloneAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        /** @var Extraction $clonedObject */
        $clonedObject = clone $object;

        $clonedObject->setName($object->getName() . " (duplicate)");
        $clonedObject->setStatus(Extraction::STATUS_FILTRATION);
        $clonedObject->setContacts(new ArrayCollection());
        $clonedObject->setExtractionDeduplications(new ArrayCollection());

        $this->admin->create($clonedObject);

        $this->addFlash('sonata_flash_success', 'Extraction successfully duplicated');

        return new RedirectResponse($this->admin->generateUrl('edit', array('id'=> $clonedObject->getId())));
    }

    public function filteringAction()
    {
        if (false === $this->admin->isGranted('FILTERING')) {
            throw new AccessDeniedException();
        }

        // Services
        $e_service = $this->get('extraction');
        $t_service = $this->get('task');

        // Current Extraction and step
        $extraction_id = $this->get('request')->get($this->admin->getIdParameter());

        // Run Extraction
        $extraction = $e_service->getEntity('extraction', $extraction_id, true, true);
        $query = $e_service->runExtraction($extraction);

        // Get all contacts in one Query (Better then using $extraction->getContacts())
        $contacts = $e_service->getExtractionContacts($extraction);

        // Forms
        $adv_exclusion = $e_service->generateForm(new ExtractionDeduplicationType());
        $adv_external_exclusion = $e_service->generateForm(new ExtractionDeduplicationType());
        $filters_form = $e_service->generateForm(
            'filters',
            $this->generateUrl(
                'admin_listbroking_app_extraction_filtering', array('id' => $extraction_id)),
            $extraction->getFilters()
        );

        //Check for Queues
        $running_queue = false;
        /** @var Queue[] $queues */
        $queues = $t_service->findQueuesByType(AppService::DEDUPLICATION_QUEUE_TYPE);
        foreach($queues as $queue){
            if($queue->getValue1() == $extraction->getId()){
                $running_queue = true;
                break;
            }
        }

        // Render Response
        return $this->render('@ListBrokingApp/Extraction/filtering.html.twig',
            array(
                'action' => 'filtering',
                'lock_time' => $e_service->getConfig('lock.time')->getValue(),
                'extraction' => $extraction,
                'contacts' => $contacts,
                'has_queues' => $running_queue,
                'query' => $query ? \SqlFormatter::format($query->getSQL()): '',
                'forms' =>  array(
                    'filters' => $filters_form->createView(),
                    'adv_exclusion' => $adv_exclusion->createView(),
                    'adv_external_exclusion' => $adv_external_exclusion->createView()
                ),
                'elements' => $this->admin->getShow(),
            )
        );
    }
}
