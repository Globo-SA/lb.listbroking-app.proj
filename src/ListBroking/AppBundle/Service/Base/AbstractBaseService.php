<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 *
 */

namespace ListBroking\AppBundle\Service\Base;


use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AbstractBaseService {

    /**
     * @var EntityManager
     */
    public $em;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var Cache
     */
    protected $dcache;

    /**
     * @var FormFactory
     */
    protected $form_factory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TokenStorageInterface
     */
    protected $token_storage;

    /**
     * @param EntityManager $entityManager
     *
     * @return mixed|void
     */
    public function setEntityManager (EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param FormFactory $formFactory
     *
     * @return mixed|void
     */
    public function setFormFactory (FormFactory $formFactory)
    {
        $this->form_factory = $formFactory;
    }

    /**
     * @param Kernel $kernel
     */
    public function setKernel ($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger ($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param TokenStorageInterface $token_storage
     */
    public function setTokenStorage ($token_storage)
    {
        $this->token_storage = $token_storage;
    }

    /**
     * @param Cache $dcache
     */
    public function setDcache ($dcache)
    {
        $this->dcache = $dcache;
    }


}