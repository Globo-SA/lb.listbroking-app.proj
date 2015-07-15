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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AppService extends BaseService implements AppServiceInterface
{

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    function __construct (\Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function createJsonResponse ($response, $code = 200)
    {
        // Handle exceptions that don't have a valid http code
        if ( ! is_int($code) || $code == '0' )
        {
            $code = 500;
        }

        return new JsonResponse(array(
            "code"     => $code,
            "response" => $response
        ), $code);
    }

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

    public function generateForm ($type, $action = null, $data = null, $view = false)
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

    public function getEntityList ($type, $ids, $query, $bundle)
    {
        if ( empty($type) )
        {
            throw new \Exception("Type can not be empty", 400);
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

        $list = $qb->getQuery()
                   ->execute(null, Query::HYDRATE_ARRAY)
        ;

        return $list;
    }

    public function validateAjaxRequest (Request $request)
    {
        if ( ! $request->isXmlHttpRequest() )
        {
            throw new \Exception("Only Xml Http Requests allowed", 400);
        }
    }
}