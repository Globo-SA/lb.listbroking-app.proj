<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\CoreBundle\Service;

interface CoreServiceInterface {

    /**
     * Gets list of countries
     * @param bool $only_active
     * @return mixed
     */
    public function getCountryList($only_active = true);

    /**
     * Gets a single country
     * @param $id
     * @return mixed
     */
    public function getCountry($id);

    /**
     * Adds a single country
     * @param $variable
     * @return mixed
     */
    public function addCountry($variable);

    /**
     * Removes a single country
     * @param $id
     * @return mixed
     */
    public function removeCountry($id);

    /**
     * Updates a single country
     * @param $country
     * @return mixed
     */
    public function updateCountry($country);

    /**
     * Get a list of categories
     * @param bool $only_active
     * @return mixed
     */
    public function getCategoryList($only_active = true);

    /**
     * Gets a single category
     * @param $id
     * @return mixed
     */
    public function getCategory($id);

    /**
     * Adds a single category
     * @param $variable
     * @return mixed
     */
    public function addCategory($variable);


    /**
     * Removes a single category
     * @param $id
     * @return mixed
     */
    public function removeCategory($id);

    /**
     * Updates a single category
     * @param $category
     * @return mixed
     */
    public function updateCategory($category);

    /**
     * Get a list of sub categories
     * @param bool $only_active
     * @return mixed
     */
    public function getSubCategoryList($only_active = true);

    /**
     * Gets a single sub category
     * @param $id
     * @return mixed
     */
    public function getSubCategory($id);

    /**
     * Adds a single sub category
     * @param $variable
     * @return mixed
     */
    public function addSubCategory($variable);

    /**
     * Removes a single sub category
     * @param $id
     * @return mixed
     */
    public function removeSubCategory($id);

    /**
     * Updates a single sub category
     * @param $sub_category
     * @return mixed
     */
    public function updateSubCategory($sub_category);
}