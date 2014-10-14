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

use Adclick\CacheBundle\Manager\CacheManagerInterface;
use ListBroking\CoreBundle\Repository\ORM\CategoryRepository;
use ListBroking\CoreBundle\Repository\ORM\CountryRepository;
use ListBroking\CoreBundle\Repository\ORM\SubCategoryRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CoreService extends BaseService implements CoreServiceInterface
{

    private $country_repo;
    private $category_repo;
    private $sub_category_repo;
    const   COUNTRY_LIST = 'country_list';
    const   COUNTRY_SCOPE = 'country';
    const   CATEGORY_LIST = 'category_list';
    const   CATEGORY_SCOPE = 'category';
    const   SUB_CATEGORY_LIST = 'sub_category_list';
    const   SUB_CATEGORY_SCOPE = 'sub_category';

    /**
     * @param CacheManagerInterface $cache
     * @param ValidatorInterface $validator
     * @param CountryRepository $country_repo
     * @param CategoryRepository $category_repo
     * @param SubCategoryRepository $sub_category_repo
     */
    function __construct(CacheManagerInterface $cache, ValidatorInterface $validator, CountryRepository $country_repo, CategoryRepository $category_repo,  SubCategoryRepository $sub_category_repo)
    {
        parent::__construct($cache, $validator);
        $this->category_repo = $category_repo;
        $this->country_repo = $country_repo;
        $this->sub_category_repo = $sub_category_repo;
    }

    /**
     * Gets list of countries
     * @param bool $only_active
     * @return mixed
     */
    public function getCountryList($only_active = true)
    {
        return $this->getList(self::COUNTRY_LIST, self::COUNTRY_SCOPE, $this->country_repo, $only_active);
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @return null
     */
    public function getCountry($id, $hydrate = false)
    {
        return $this->get(self::COUNTRY_LIST, self::COUNTRY_SCOPE, $this->country_repo, $id, $hydrate);
    }

    /**
     * @param $code
     * @param bool $hydrate
     * @return mixed
     */
    public function getCountryByCode($code, $hydrate = false)
    {
        $entity = $this->country_repo->getCountryByCode($code, $hydrate);

        return $entity;
    }

    /**
     * Adds a country
     * @param $country
     * @return mixed
     */
    public function addCountry($country)
    {
        $this->add(self::COUNTRY_LIST, self::COUNTRY_SCOPE, $this->country_repo, $country);
        return $this;
    }

    /**
     * Removes a country
     * @param $id
     * @return mixed
     */
    public function removeCountry($id)
    {
        $this->remove(self::COUNTRY_LIST, self::COUNTRY_SCOPE, $this->country_repo, $id);
        return $this;
    }

    /**
     * Updates a single Country
     * @param $country
     * @return $this
     */
    public function updateCountry($country){
        $this->update(self::COUNTRY_LIST, self::COUNTRY_SCOPE, $this->country_repo, $country);
        return $this;
    }

    /**
     * Gets a Category list
     * @param bool $only_active
     * @return mixed|null
     */
    public function getCategoryList($only_active = true)
    {
        return $this->getList(self::CATEGORY_LIST, self::CATEGORY_SCOPE, $this->category_repo, $only_active);
    }

    /**
     * Gets a single Category
     * @param $id
     * @param bool $hydrate
     * @return null
     */
    public function getCategory($id, $hydrate = false)
    {
        return $this->get(self::CATEGORY_LIST, self::CATEGORY_SCOPE, $this->category_repo, $id, $hydrate);
    }

    /**
     * Adds a single Category
     * @param $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->add(self::CATEGORY_LIST, self::CATEGORY_SCOPE, $this->category_repo, $category);
        return $this;
    }

    /**
     * Removes a single Category
     * @param $id
     * @return $this
     */
    public function removeCategory($id)
    {
        $this->remove(self::CATEGORY_LIST, self::CATEGORY_SCOPE, $this->category_repo, $id);
        return $this;
    }

    /**
     * Updates a single Category
     * @param $category
     * @return $this
     */
    public function updateCategory($category){
        $this->update(self::CATEGORY_LIST, self::CATEGORY_SCOPE, $this->category_repo, $category);
        return $this;
    }

    /**
     * Gets list of sub_categories
     * @param bool $only_active
     * @return mixed
     */
    public function getSubCategoryList($only_active = true)
    {
        return $this->getList(self::CATEGORY_LIST, self::CATEGORY_SCOPE, $this->sub_category_repo, $only_active);
    }

    /**
     * Gets a single SubCategory
     * @param $id
     * @param bool $hydrate
     * @return null
     */
    public function getSubCategory($id, $hydrate = false)
    {
        return $this->get(self::CATEGORY_LIST, self::CATEGORY_SCOPE, $this->sub_category_repo, $id, $hydrate);
    }

    /**
     * Adds a SubCategory
     * @param $sub_category
     * @return mixed
     */
    public function addSubCategory($sub_category)
    {
        $this->add(self::CATEGORY_LIST, self::CATEGORY_SCOPE, $this->sub_category_repo, $sub_category);
        return $this;
    }

    /**
     * Removes a SubCategory
     * @param $id
     * @return mixed
     */
    public function removeSubCategory($id)
    {
        $this->remove(self::CATEGORY_LIST, self::CATEGORY_SCOPE, $this->sub_category_repo, $id);
        return $this;
    }

    /**
     * Updates a single SubCategory
     * @param $sub_category
     * @return $this
     */
    public function updateSubCategory($sub_category){
        $this->update(self::CATEGORY_LIST, self::CATEGORY_SCOPE, $this->sub_category_repo, $sub_category);
        return $this;
    }
}