<?php
/**
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;

class Category
{

    const CACHE_ID = 'category';

    use TimestampableEntityBehavior;

    protected $id;

    protected $name;

    protected $sub_categories;

    function __construct ()
    {
        $this->sub_categories = new ArrayCollection();
    }

    function __toString ()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName ($name)
    {
        $this->name = $name;
    }

    public function addSubCategory (SubCategory $sub_category)
    {
        $sub_category->setCategory($this);
        $this->sub_categories[] = $sub_category;
    }

    public function removeSubCategory (SubCategory $sub_category)
    {
        $this->sub_categories->removeElement($sub_category);
    }

    /**
     * @return mixed
     */
    public function getSubCategories ()
    {
        return $this->sub_categories;
    }
}