<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ExceptionHandlerBundle\EventListener;


use ListBroking\AdvancedConfigurationBundle\Service\ListBrokingAdvancedConfigurationInterface;
use ListBroking\ExceptionHandlerBundle\Repository\ORM\ExceptionLogRepository;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener {

    protected $exception_handler_repo;
    protected $adv_config;
    protected $mailer;
    protected $twig;

    function __construct(
        ExceptionLogRepository $exception_handler_repo,
        ListBrokingAdvancedConfigurationInterface $adv_config,
        \Swift_Mailer $mailer,
        \Twig_Environment $twig)
    {
        $this->exception_handler_repo = $exception_handler_repo;
        $this->adv_config = $adv_config;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        /** @var \Exception $exception */
        $exception =  $event->getException();
        $error = array(
            'code' => $exception->getCode(),
            'msg' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        );

        /* Persist the exception to the DB */
        $this->exception_handler_repo->createNewEntity($error);
        $this->exception_handler_repo->flush();

        /* Send the exception by email */
        $error['trace'] = var_export($error['trace'], true); //pretty print the array
        $mail_info = $this->adv_config->get('exception_handler.email');
        if($mail_info['is_active']){
            $message = \Swift_Message::newInstance()
                ->setSubject($mail_info['subject'])
                ->setFrom($mail_info['from'])
                ->setTo($mail_info['to'])
                ->setBody($this->twig->render('ListBrokingExceptionHandlerBundle::exception_template.html.twig', array('error' => $error)))
                ->setContentType('text/html');
            $this->mailer->send($message);
        }
    }

} 