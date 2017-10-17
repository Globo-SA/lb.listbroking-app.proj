<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Engine\ValidatorEngine;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Service\Base\BaseService;

/**
 * ListBroking\AppBundle\Service\BusinessLogic\StagingService
 */
class StagingService extends BaseService implements StagingServiceInterface
{
    /**
     * @var ValidatorEngine
     */
    protected $validatorEngine;

    /**
     * StagingService constructor.
     *
     * @param ValidatorEngine $validatorEngine
     */
    public function __construct(ValidatorEngine $validatorEngine)
    {
        $this->validatorEngine = $validatorEngine;
    }

    /**
     * {@inheritdoc}
     */
    public function addStagingContact(array $dataArray)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                                    ->addStagingContact($dataArray);
    }

    /**
     * {@inheritdoc}
     */
    public function findAndLockContactsToValidate($limit = 50)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                                    ->findAndLockContactsToValidate($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function importOppositionList($type, \PHPExcel $file, $clearOld)
    {
        $config = $this->findConfig('opposition_list.config');

        $this->entity_manager->getRepository('ListBrokingAppBundle:OppositionList')
                             ->importOppositionListFile($type, $config[$type], $file, $clearOld);
    }

    /**
     * {@inheritdoc}
     */
    public function importStagingContacts(\PHPExcel $file, array $extraFields = [], $batchSize)
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->importStagingContactsFile($file, $extraFields, $batchSize);
    }

    /**
     * {@inheritdoc}
     */
    public function loadValidatedContact(StagingContact $staging_contact)
    {
        $dimensions = $this->loadStagingContactDimensions($staging_contact);

        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->loadValidatedContact($staging_contact, $dimensions);
    }

    /**
     * {@inheritdoc}
     */
    public function moveInvalidContactsToDQP($limit)
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->moveInvalidContactsToDQP($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUpdatedContact(StagingContact $staging_contact)
    {
        $dimensions = $this->loadStagingContactDimensions($staging_contact);
        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->loadUpdatedContact($staging_contact, $dimensions);
    }

    /**
     * {@inheritdoc}
     */
    public function syncContactsWithOppositionLists()
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:Lead')
                             ->syncLeadsWithOppositionLists();
    }

    /**
     * {@inheritdoc}
     */
    public function validateStagingContact(StagingContact $staging_contact)
    {
        $this->validatorEngine->run($staging_contact);
    }

    /**
     * {@inheritdoc}
     */
    public function findLeadsWithExpiredInitialLock($limit)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:Lead')
                                    ->findLeadsWithExpiredInitialLock($limit);
    }

    /**
     * Get staging contact dimensions as an array
     *
     * @param StagingContact $stagingContact
     *
     * @return array
     */
    private function loadStagingContactDimensions(StagingContact $stagingContact)
    {
        //Dimension Tables
        return [
            'source'       => $this->findDimension('ListBrokingAppBundle:Source', $stagingContact->getSourceName()),
            'owner'        => $this->findDimension('ListBrokingAppBundle:Owner', $stagingContact->getOwner()),
            'sub_category' => $this->findDimension(
                'ListBrokingAppBundle:SubCategory',
                $stagingContact->getSubCategory()
            ),
            'gender'       => $this->findDimension('ListBrokingAppBundle:Gender', $stagingContact->getGender()),
            'district'     => $this->findDimension('ListBrokingAppBundle:District', $stagingContact->getDistrict()),
            'county'       => $this->findDimension('ListBrokingAppBundle:County', $stagingContact->getCounty()),
            'parish'       => $this->findDimension('ListBrokingAppBundle:Parish', $stagingContact->getParish()),
            'country'      => $this->findDimension('ListBrokingAppBundle:Country', $stagingContact->getCountry())
        ];
    }

    /**
     * Finds the facts table Dimensions by name
     *
     * @param string $repoName
     * @param string $name
     *
     * @return null|object
     */
    private function findDimension($repoName, $name)
    {
        return $this->entity_manager->getRepository($repoName)
                                    ->findOneBy(['name' => $name]);
    }
}
