<?php

namespace ListBroking\AppBundle\Controller;

use ListBroking\AppBundle\Service\BusinessLogic\ExtractionContactServiceInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

class ContactAdminController extends CRUDController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function extractionsHistoryAction(Request $request)
    {
        $contactId = $request->get($this->admin->getIdParameter());
        $contact = $this->admin->getObject($contactId);

        /** @var ExtractionContactServiceInterface $extractionContactService */
        $extractionContactService = $this->get('app.service.extraction_contact');

        return $this->render($this->admin->getTemplate('extractionsHistory'), [
            'email'       => $contact->getEmail(),
            'extractions' => $extractionContactService->findContactExtractions($contact)
        ], null);
    }
}