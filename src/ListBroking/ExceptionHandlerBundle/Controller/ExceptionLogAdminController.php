<?php

namespace ListBroking\ExceptionHandlerBundle\Controller;

use ListBroking\ExceptionHandlerBundle\Entity\ExceptionLog;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

class ExceptionLogAdminController extends CRUDController
{

    public function ExceptionAction ()
    {
        $id = $this->get('request')
                   ->get($this->admin->getIdParameter())
        ;

        /** @var ExceptionLog $object */
        $object = $this->admin->getObject($id);

        // Render Response
        return $this->render('@ListBrokingApp/Exception/error.html.twig', array(
                'status_code' => $object->getCode(),
                'status_text' => $object->getMsg(),
                'stack_trace' => $object->getTrace(),
                'elements'    => $this->admin->getShow(),
            ))
            ;
    }
}
