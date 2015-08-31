<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Engine\ValidatorEngine;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Service\Base\BaseService;

class StagingService extends BaseService implements StagingServiceInterface
{

    /**
     * @var ValidatorEngine
     */
    protected $v_engine;

    public function __construct (ValidatorEngine $v_engine)
    {
        $this->v_engine = $v_engine;
    }

    /**
     * @inheritdoc
     */
    public function addStagingContact ($data_array)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                                    ->addStagingContact($data_array)
            ;
    }

    /**
     * @inheritdoc
     */
    public function findAndLockContactsToValidate ($limit = 50)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                                    ->findAndLockContactsToValidate($limit)
            ;
    }

    /**
     * @inheritdoc
     */
    public function importOppositionList ($type, $file, $clear_old)
    {
        $config = $this->findConfig('opposition_list.config');

        $this->entity_manager->getRepository('ListBrokingAppBundle:OppositionList')
                             ->importOppositionListFile($type, $config[$type], $file, $clear_old)
        ;
    }

    /**
     * @inheritdoc
     */
    public function importStagingContacts ($file, array $default_info = [])
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->importStagingContactsFile($file, $default_info)
        ;
    }

    /**
     * @inheritdoc
     */
    public function loadValidatedContact (StagingContact $contact)
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->loadValidatedContact($contact)
        ;
    }

    /**
     * @inheritdoc
     */
    public function moveInvalidContactsToDQP ()
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->moveInvalidContactsToDQP()
        ;
    }

    /**
     * @inheritdoc
     */
    public function syncContactsWithOppositionLists ()
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:Lead')
                             ->syncContactsWithOppositionLists()
        ;
    }

    /**
     * @inheritdoc
     */
    public function validateStagingContact ($contact)
    {
        $this->v_engine->run($contact);
    }
}