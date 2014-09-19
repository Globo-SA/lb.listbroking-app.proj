<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\CoreBundle\Entity;

use Adclick\DoctrineBehaviorBundle\Behavior\BlameableEntityBehavior,
    Adclick\DoctrineBehaviorBundle\Behavior\TimestampableEntityBehavior
    ;

use Doctrine\Common\Collections\ArrayCollection;

class Category {
    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;

    protected $is_active;

    protected $name;

    protected $sub_categories;

    function __construct()
    {
        $this->sub_categories = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * @param mixed $is_active
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param SubCategory $sub_category
     */
    public function addSubCategory(SubCategory $sub_category)
    {
        $this->sub_categories[] = $sub_category;
    }

    /**
     * @param SubCategory $sub_category
     */
    public function removeSubCategory(SubCategory $sub_category)
    {
        $this->sub_categories->removeElement($sub_category);
    }

    /**
     * @return mixed
     */
    public function getSubCategories()
    {
        return $this->sub_categories;
    }
}