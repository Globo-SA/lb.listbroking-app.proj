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


interface ContactDetailsServiceInterface
{
    /**
     * @param bool $only_active
     * @return mixed
     */
    public function getCountyList($only_active = true);

    /**
     * @param $id
     * @return mixed
     */
    public function getCounty($id);

    /**
     * @param $county
     * @return mixed
     */
    public function addCounty($county);

    /**
     * @param $id
     * @return mixed
     */
    public function removeCounty($id);

    /**
     * @param $county
     * @return mixed
     */
    public function updateCounty($county);

    /**
     * @param bool $only_active
     * @return mixed
     */
    public function getDistrictList($only_active = true);

    /**
     * @param $id
     * @return mixed
     */
    public function getDistrict($id);

    /**
     * @param $district
     * @return mixed
     */
    public function addDistrict($district);

    /**
     * @param $id
     * @return mixed
     */
    public function removeDistrict($id);

    /**
     * @param $district
     * @return mixed
     */
    public function updateDistrict($district);

    /**
     * @param bool $only_active
     * @return mixed
     */
    public function getGenderList($only_active = true);

    /**
     * @param $id
     * @return mixed
     */
    public function getGender($id);

    /**
     * @param $gender
     * @return mixed
     */
    public function addGender($gender);

    /**
     * @param $id
     * @return mixed
     */
    public function removeGender($id);

    /**
     * @param $gender
     * @return mixed
     */
    public function updateGender($gender);

    /**
     * @param bool $only_active
     * @return mixed
     */
    public function getOwnerList($only_active = true);

    /**
     * @param $id
     * @return mixed
     */
    public function getOwner($id);

    /**
     * @param $owner
     * @return mixed
     */
    public function addOwner($owner);

    /**
     * @param $id
     * @return mixed
     */
    public function removeOwner($id);

    /**
     * @param $owner
     * @return mixed
     */
    public function updateOwner($owner);

    /**
     * @param bool $only_active
     * @return mixed
     */
    public function getParishList($only_active = true);

    /**
     * @param $id
     * @return mixed
     */
    public function getParish($id);

    /**
     * @param $parish
     * @return mixed
     */
    public function addParish($parish);

    /**
     * @param $id
     * @return mixed
     */
    public function removeParish($id);

    /**
     * @param $parish
     * @return mixed
     */
    public function updateParish($parish);

    /**
     * @param bool $only_active
     * @return mixed
     */
    public function getSourceList($only_active = true);

    /**
     * @param $id
     * @return mixed
     */
    public function getSource($id);

    /**
     * @param $source
     * @return mixed
     */
    public function addSource($source);

    /**
     * @param $id
     * @return mixed
     */
    public function removeSource($id);

    /**
     * @param $source
     * @return mixed
     */
    public function updateSource($source);
} 