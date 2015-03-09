<?php

namespace ListBroking\ExceptionHandlerBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Form\ExtractionDeduplicationType;
use ListBroking\AppBundle\Service\Helper\AppService;
use ListBroking\ExceptionHandlerBundle\Entity\ExceptionLog;
use ListBroking\TaskControllerBundle\Entity\Queue;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExceptionLogAdminController extends CRUDController
{

    public function ExceptionAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());

        /** @var ExceptionLog $object */
        $object = $this->admin->getObject($id);

        // Render Response
        return $this->render('@ListBrokingApp/Exception/error.html.twig',
            array(
                'status_code' => $object->getCode(),
                'status_text' => $object->getMsg(),
                'stack_trace' => $object->getTrace(),
                'elements' => $this->admin->getShow(),
            )
        );
    }
}
