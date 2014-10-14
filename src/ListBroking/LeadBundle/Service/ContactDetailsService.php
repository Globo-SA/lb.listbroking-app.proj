<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Service;
use Adclick\CacheBundle\Manager\CacheManagerInterface;
use ListBroking\CoreBundle\Service\BaseService;
use ListBroking\LeadBundle\Repository\ContactRepositoryInterface;
use ListBroking\LeadBundle\Repository\ORM\CountyRepository;
use ListBroking\LeadBundle\Repository\ORM\DistrictRepository;
use ListBroking\LeadBundle\Repository\ORM\GenderRepository;
use ListBroking\LeadBundle\Repository\ORM\OwnerRepository;
use ListBroking\LeadBundle\Repository\ORM\ParishRepository;
use ListBroking\LeadBundle\Repository\ORM\SourceRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ContactDetailsService extends BaseService implements ContactRepositoryInterface
{
    private $county_repo;
    private $district_repo;
    private $gender_repo;
    private $owner_repo;
    private $parish_repo;
    private $source_repo;
    const   COUNTY_SCOPE = 'county';
    const   DISTRICT_SCOPE = 'district';
    const   GENDER_SCOPE = 'gender';
    const   OWNER_SCOPE = 'owner';
    const   PARISH_SCOPE = 'parish';
    const   SOURCE_SCOPE = 'source';

    function __construct(
        CacheManagerInterface $cache,
        ValidatorInterface $validator,
        CountyRepository $county_repo,
        DistrictRepository $district_repo,
        GenderRepository $gender_repo,
        OwnerRepository $owner_repo,
        ParishRepository $parish_repo,
        SourceRepository $source_repo
    ) {
        parent::__construct($cache, $validator);
        $this->county_repo = $county_repo;
        $this->district_repo = $district_repo;
        $this->gender_repo = $gender_repo;
        $this->owner_repo = $owner_repo;
        $this->parish_repo = $parish_repo;
        $this->source_repo = $source_repo;
    }

    /**
     * @param bool $only_active
     * @return mixed|null
     */
    public function getCountyList($only_active = true){
        return $this->getList(self::COUNTY_SCOPE . '_list', self::COUNTY_SCOPE, $this->county_repo, $only_active);
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @return mixed|null
     */
    public function getCounty($id, $hydrate = false){
        return $this->get(self::COUNTY_SCOPE . '_list', self::COUNTY_SCOPE, $this->county_repo, $id, $hydrate);
    }

    public function getCountyByName($name, $hydrate = false){
        $entity = $this->county_repo->getCountyByName($name, $hydrate);

        return $entity;
    }

    /**
     * @param $county
     * @return $this
     */
    public function addCounty($county){
        $this->add(self::COUNTY_SCOPE . '_list', self::COUNTY_SCOPE, $this->county_repo, $county);
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function removeCounty($id){
        $this->remove(self::COUNTY_SCOPE . '_list', self::COUNTY_SCOPE, $this->county_repo, $id);
        return $this;
    }

    /**
     * @param $county
     * @return $this
     */
    public function updateCounty($county){
        $this->update(self::COUNTY_SCOPE . '_list', self::COUNTY_SCOPE, $this->county_repo, $county);
        return $this;
    }

    /**
     * @param bool $only_active
     * @return mixed|null
     */
    public function getDistrictList($only_active = true){
        return $this->getList(self::DISTRICT_SCOPE . '_list', self::DISTRICT_SCOPE, $this->district_repo, $only_active);
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @return mixed|null
     */
    public function getDistrict($id, $hydrate = false){
        return $this->get(self::DISTRICT_SCOPE . '_list', self::DISTRICT_SCOPE, $this->district_repo, $id, $hydrate);
    }

    /**
     * @param $district
     * @return $this
     */
    public function addDistrict($district){
        $this->add(self::DISTRICT_SCOPE . '_list', self::DISTRICT_SCOPE, $this->district_repo, $district);
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function removeDistrict($id){
        $this->remove(self::DISTRICT_SCOPE . '_list', self::DISTRICT_SCOPE, $this->district_repo, $id);
        return $this;
    }

    /**
     * @param $district
     * @return $this
     */
    public function updateDistrict($district){
        $this->update(self::DISTRICT_SCOPE . '_list', self::DISTRICT_SCOPE, $this->district_repo, $district);
        return $this;
    }

    /**
     * @param bool $only_active
     * @return mixed|null
     */
    public function getGenderList($only_active = true){
        return $this->getList(self::GENDER_SCOPE . '_list', self::GENDER_SCOPE, $this->gender_repo, $only_active);
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @return mixed|null
     */
    public function getGender($id, $hydrate = false){
        return $this->get(self::GENDER_SCOPE . '_list', self::GENDER_SCOPE, $this->gender_repo, $id, $hydrate);
    }

    /**
     * @param $gender
     * @return $this
     */
    public function addGender($gender){
        $this->add(self::GENDER_SCOPE . '_list', self::GENDER_SCOPE, $this->gender_repo, $gender);
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function removeGender($id){
        $this->remove(self::GENDER_SCOPE . '_list', self::GENDER_SCOPE, $this->gender_repo, $id);
        return $this;
    }

    /**
     * @param $gender
     * @return $this
     */
    public function updateGender($gender){
        $this->update(self::GENDER_SCOPE . '_list', self::GENDER_SCOPE, $this->gender_repo, $gender);
        return $this;
    }

    /**
     * @param bool $only_active
     * @return mixed|null
     */
    public function getOwnerList($only_active = true){
        return $this->getList(self::OWNER_SCOPE . '_list', self::OWNER_SCOPE, $this->owner_repo, $only_active);
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @return mixed|null
     */
    public function getOwner($id, $hydrate = false){
        return $this->get(self::OWNER_SCOPE . '_list', self::OWNER_SCOPE, $this->owner_repo, $id, $hydrate);
    }

    /**
     * @param $name
     * @param bool $hydrate
     * @return $this
     */
    public function getOwnerByName($name, $hydrate = false){
        $entity = $this->owner_repo->getOwnerByName($name, $hydrate);
        return $entity;
    }

    /**
     * @param $owner
     * @return $this
     */
    public function addOwner($owner){
        $this->add(self::OWNER_SCOPE . '_list', self::OWNER_SCOPE, $this->owner_repo, $owner);
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function removeOwner($id){
        $this->remove(self::OWNER_SCOPE . '_list', self::OWNER_SCOPE, $this->owner_repo, $id);
        return $this;
    }

    /**
     * @param $owner
     * @return $this
     */
    public function updateOwner($owner){
        $this->update(self::OWNER_SCOPE . '_list', self::OWNER_SCOPE, $this->owner_repo, $owner);
        return $this;
    }

    /**
     * @param bool $only_active
     * @return mixed|null
     */
    public function getParishList($only_active = true){
        return $this->getList(self::PARISH_SCOPE . '_list', self::PARISH_SCOPE, $this->parish_repo, $only_active);
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @return mixed|null
     */
    public function getParish($id, $hydrate = false){
        return $this->get(self::PARISH_SCOPE . '_list', self::PARISH_SCOPE, $this->parish_repo,$id, $hydrate);
    }

    /**
     * @param $parish
     * @return $this
     */
    public function addParish($parish){
        $this->add('_list' . self::PARISH_SCOPE, self::PARISH_SCOPE, $this->parish_repo, $parish);
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function removeParish($id){
        $this->remove(self::PARISH_SCOPE . '_list', self::PARISH_SCOPE, $this->parish_repo, $id);
        return $this;
    }

    /**
     * @param $parish
     * @return $this
     */
    public function updateParish($parish){
        $this->update(self::PARISH_SCOPE . '_list', self::PARISH_SCOPE, $this->parish_repo, $parish);
        return $this;
    }

    /**
     * @param bool $only_active
     * @return mixed|null
     */
    public function getSourceList($only_active = true){
        return $this->getList(self::SOURCE_SCOPE . '_list', self::SOURCE_SCOPE, $this->source_repo, $only_active);
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @return mixed|null
     */
    public function getSource($id, $hydrate = false){
        return $this->get(self::SOURCE_SCOPE . '_list', self::SOURCE_SCOPE, $this->source_repo, $id, $hydrate);
    }

    /**
     * @param $name
     * @param bool $hydrate
     * @return mixed
     */
    public function getSourceByName($name, $hydrate = false){
        $entity = $this->source_repo->getSourceByName($name, $hydrate);
        return $entity;
    }

    /**
     * @param $source
     * @return $this
     */
    public function addSource($source){
        $this->add(self::SOURCE_SCOPE . '_list', self::SOURCE_SCOPE, $this->source_repo, $source);
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function removeSource($id){
        $this->remove(self::SOURCE_SCOPE . '_list', self::SOURCE_SCOPE, $this->source_repo, $id);
        return $this;
    }

    /**
     * @param $source
     * @return $this
     */
    public function updateSource($source){
        $this->update(self::SOURCE_SCOPE . '_list', self::SOURCE_SCOPE, $this->source_repo, $source);
        return $this;
    }
}