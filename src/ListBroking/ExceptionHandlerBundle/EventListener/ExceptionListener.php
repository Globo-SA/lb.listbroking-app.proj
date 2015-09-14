<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ExceptionHandlerBundle\EventListener;

use Doctrine\ORM\EntityManager;
use ListBroking\ExceptionHandlerBundle\Entity\ExceptionLog;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Router;

class ExceptionListener
{

    protected $em;

    protected $mailer;

    protected $twig;

    protected $router;

    function __construct (
        EntityManager $entityManager,
        \Swift_Mailer $mailer,
        \Twig_Environment $twig,
        Router $router)
    {
        $this->em = $entityManager;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->router = $router;
    }

    public function onKernelException (GetResponseForExceptionEvent $event)
    {
        /** @var \Exception $exception */
        $exception = $event->getException();
        $error = new ExceptionLog();
        $error->setCode($exception->getCode());
        $error->setMsg($exception->getMessage());
        $error->setTrace($exception->getTraceAsString());

        /* Persist the exception to the DB */
        $this->em->persist($error);
        $this->em->flush();

        /* Send the exception by email */
        $error_array['trace'] = var_export($error->getTrace(), true); //pretty print the array
        $error_array['msg'] = $error->getMsg();

        //TODO: Fix mail_info
        $mail_info['is_active'] = false;
        if ( $mail_info['is_active'] )
        {
            $message = \Swift_Message::newInstance($mail_info['subject'], $this->twig->render('ListBrokingExceptionHandlerBundle::exception_template.html.twig', array('error' => $error_array)), 'text/html')
                                     ->setFrom($mail_info['from'])
                                     ->setTo($mail_info['to'])
            ;
            $this->mailer->send($message);
        }

        if ( ! $event->getRequest()->isXmlHttpRequest())
        {

            $event->setResponse(new RedirectResponse($this->router->generate('admin_listbroking_exceptionhandler_exceptionlog_exception', array(
                'id' => $error->getId()
            ))));
        }
    }
}