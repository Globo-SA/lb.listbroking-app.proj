<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

interface CoreServiceInterface {

    /**
     * Gets list of countries
     * @return mixed
     */
    public function getCountryList();

    /**
     * Adds a country
     * @return mixed
     */
    public function addCountry();

    /**
     * Removes a country
     * @return mixed
     */
    public function removeCountry();

    /**
     * Gets a country
     * @return mixed
     */
    public function getCountry();

    /**
     * Get a list of categories
     * @return mixed
     */
    public function getCategoryList();

    /**
     * Adss a category
     * @return mixed
     */
    public function addCategory();

    /**
     * Removes a category
     * @return mixed
     */
    public function removeCategory();


    /**
     * Gets a category
     * @return mixed
     */
    public function getCategory();

    /**
     * Get a list of sub categories
     * @return mixed
     */
    public function getSubCategoryList();

    /**
     * Adss a sub category
     * @return mixed
     */
    public function addSubCategory();

    /**
     * Removes a sub category
     * @return mixed
     */
    public function removeSubCategory();


    /**
     * Gets a sub category
     * @return mixed
     */
    public function getSubCategory();
}