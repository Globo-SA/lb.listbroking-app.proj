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
    public function loadValidatedContact (StagingContact $staging_contact)
    {
        $this->startStopWatch('validator_engine');

        $dimensions = $this->loadStagingContactDimensions($staging_contact);
        $this->logInfo(sprintf('Stopwatch: loadStagingContactDimensions, ran in %s milliseconds', $this->lapStopWatch('validator_engine')));

        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->loadValidatedContact($staging_contact, $dimensions)
        ;
        $this->logInfo(sprintf('Stopwatch: loadValidatedContact, ran in %s milliseconds', $this->lapStopWatch('validator_engine')));
    }

    /**
     * @inheritdoc
     */
    public function moveInvalidContactsToDQP ($limit)
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->moveInvalidContactsToDQP($limit)
        ;
    }

    /**
     * @inheritdoc
     */
    public function loadUpdatedContact (StagingContact $staging_contact)
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->loadUpdatedContact($staging_contact)
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
    public function validateStagingContact (StagingContact $staging_contact)
    {
        $this->v_engine->run($staging_contact);
    }

    private function loadStagingContactDimensions (StagingContact $staging_contact)
    {
        //Dimension Tables
        return array(
            'source'       => $this->findDimension('ListBrokingAppBundle:Source', $staging_contact->getSourceName()),
            'owner'        => $this->findDimension('ListBrokingAppBundle:Owner', $staging_contact->getOwner()),
            'sub_category' => $this->findDimension('ListBrokingAppBundle:SubCategory', $staging_contact->getSubCategory()),
            'gender'       => $this->findDimension('ListBrokingAppBundle:Gender', $staging_contact->getGender()),
            'district'     => $this->findDimension('ListBrokingAppBundle:District', $staging_contact->getDistrict()),
            'county'       => $this->findDimension('ListBrokingAppBundle:County', $staging_contact->getCounty()),
            'parish'       => $this->findDimension('ListBrokingAppBundle:Parish', $staging_contact->getParish()),
            'country'      => $this->findDimension('ListBrokingAppBundle:Country', $staging_contact->getCountry())
        );
    }

    /**
     * Finds the facts table Dimensions by name
     *
     * @param       $repo_name
     * @param       $name
     *
     * @return null|object
     */
    private function findDimension ($repo_name, $name)
    {
        $cache_id = md5($repo_name . $name);
        if ( ! $this->doctrine_cache->contains($cache_id) )
        {

            $dimension = $this->entity_manager->getRepository($repo_name)
                                              ->findOneBy(array(
                                                  'name' => $name
                                              ))
            ;

            $this->doctrine_cache->save($cache_id, $dimension, BaseService::CACHE_TTL);
        }

        return $this->entity_manager->merge($this->doctrine_cache->fetch($cache_id));
    }
}