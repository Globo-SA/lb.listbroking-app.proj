<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Helper;


use Doctrine\ORM\Query;
use ListBroking\AppBundle\Service\Base\BaseService;

class AppService extends BaseService implements AppServiceInterface {

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * @param $code
     * @param bool $hydrate
     * @return mixed
     */
    public function getCountryByCode($code, $hydrate = true)
    {
       $entities = $this->getEntities('country', $hydrate);
        foreach ($entities as $entity){
            if($hydrate){
                if($entity->getName() == $code){
                    return $entity;
                }
            } else{
                if($entity['name'] == $code){
                    return $entity;
                }
            }
        }

        return null;
    }

    /**
     * Gets a list of entities using the services
     * provided in various bundles
     * @param $type
     * @param $query
     * @param string $bundle
     * @throws \Exception
     * @return mixed
     */
    public function getEntityList($type, $query, $bundle = 'ListBrokingAppBundle')
    {
        if (empty($type))
        {
            throw new \Exception("Type can not be empty", 400);
        }

        $qb = $this->em->getRepository("{$bundle}:{$type}")
            ->createQueryBuilder('l')
        ;

        if(!empty($query)){
            $qb->where($qb->expr()->like('l.name', $qb->expr()->literal("%%{$query}%%")))
            ;
        }

        $list = $qb
            ->getQuery()
            ->execute(null, Query::HYDRATE_ARRAY)
        ;

        return $list;
    }

    /**
     * Deliver emails using the system
     * @param $template
     * @param $parameters
     * @param $subject
     * @param $emails
     * @internal param $body
     * @return int
     */
    public function deliverEmail($template, $parameters, $subject, $emails){
        $message = $this->mailer->createMessage()
            ->setSubject($subject)
            ->setFrom($this->getConfig('system.email')->getValue())
            ->setTo($emails)
            ->setBody(
                $this->twig->render(
                    $template,
                    $parameters
                )
            )
            ->setContentType('text/html');

        return $this->mailer->send($message);
    }
}