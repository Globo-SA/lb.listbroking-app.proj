<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Helper;

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Service\Base\BaseService;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AppService extends BaseService implements AppServiceInterface
{

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Swift_FileSpool
     */
    private $mailer_spool;
    /**
     * @var \Swift_Transport_EsmtpTransport
     */
    private $mailer_transport_real;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct (\Swift_Mailer $mailer, \Swift_FileSpool $mailer_spool, \Swift_Transport_EsmtpTransport $mailer_transport_real, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->mailer_spool = $mailer_spool;
        $this->mailer_transport_real = $mailer_transport_real;
        $this->twig = $twig;
    }

    /**
     * @inheritdoc
     */
    public function createJsonResponse ($response, $code = 200)
    {
        // Handle exceptions that don't have a valid http code
        if ( ! is_int($code) || $code === '0' )
        {
            $code = 500;
        }

        return new JsonResponse(array(
            'code'     => $code,
            'response' => $response
        ), $code);
    }

    /**
     * @inheritdoc
     */
    public function createAttachmentResponse ($filename, $with_cookie = true)
    {

        // Generate response
        $response = new Response();

        // Set headers for file attachment
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-length', filesize($filename));

        // Send headers before outputting anything
        $response->sendHeaders();
        $response->setContent(readfile($filename));

        // Sends a "file was downloaded" cookie
        if ( $with_cookie )
        {
            $cookie = new Cookie('fileDownload', 'true', new \DateTime('+1 minute'), '/', null, false, false);
            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function deliverEmail ($template, $parameters, $subject, $emails, $filename = null)
    {
        $message = $this->mailer->createMessage()
                                ->setSubject($subject)
                                ->setFrom($this->findConfig('system.email'))
                                ->setTo($emails)
                                ->setBody($this->twig->render($template, $parameters))
                                ->setContentType('text/html')
        ;

        if ( $filename )
        {
            $message->attach(\Swift_Attachment::fromPath($filename));
        }

        return $this->mailer->send($message);
    }

    public function flushSpool ()
    {
        $this->mailer_spool->flushQueue($this->mailer_transport_real);
    }

    /**
     * @inheritdoc
     */
    public function generateForm ($type, $action = null, $data = null, $options = array(), $view = false)
    {
        $form = $this->form_factory->createBuilder($type, $data);
        if ( $action )
        {
            $form->setAction($action);
        }
        if ( $view )
        {
            return $form->getForm()
                        ->createView()
                ;
        }

        return $form->getForm();
    }

    /**
     * @inheritdoc
     */
    public function getEntityList ($type, $ids, $query, $bundle)
    {
        if ( empty($type) )
        {
            throw new \Exception('Type can not be empty', 400);
        }

        $qb = $this->entity_manager->getRepository("{$bundle}:{$type}")
                                   ->createQueryBuilder('l')
        ;
        if ( ! empty($ids) )
        {
            $qb->where($qb->expr()
                          ->in('l.id', $ids))
            ;
        }

        if ( ! empty($query) )
        {
            $qb->where($qb->expr()
                          ->like('l.name', $qb->expr()
                                              ->literal("%%{$query}%%")))
            ;
        }
        $qb->orderBy('l.name','ASC')
        ;

        $list = $qb->getQuery()
                   ->execute(null, Query::HYDRATE_ARRAY)
        ;

        return $list;
    }

    /**
     * @inheritdoc
     */
    public function validateAjaxRequest (Request $request)
    {
        if ( ! $request->isXmlHttpRequest() )
        {
            throw new \Exception('Only Xml Http Requests allowed', 400);
        }
    }
}