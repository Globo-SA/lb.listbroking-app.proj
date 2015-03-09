<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [JOBENGINE_URL_LICENSE_HERE]
 *
 * [JOBENGINE_DISCLAIMER]
 */

namespace ListBroking\AppBundle\AdminBlock;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class OperationalEmailService extends BaseBlockService {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var AuthorizationChecker
     */
    private $check;

    public function __construct($name, EngineInterface $templating, EntityManagerInterface $entity_manager, AuthorizationChecker $check)
    {
        parent::__construct($name, $templating);
        $this->em = $entity_manager;
        $this->check = $check;
    }

    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'title' => 'Operational Email Sender',
            'template' => 'ListBrokingAppBundle:AdminBlock:operational_email_block.html.twig',
        ));
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $blockContext->getSettings();

        if(!$this->check->isGranted('ROLE_SUPER_ADMIN')){
            return $this->renderResponse('ListBrokingAppBundle:AdminBlock:empty.html.twig', array(), $response);
        }

        return $this->renderResponse($blockContext->getTemplate(), array(
            'block'    => $blockContext->getBlock(),
            'settings' => $settings,
        ), $response);
    }
} 