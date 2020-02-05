<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Entity;

use ListBroking\AppBundle\Behavior\BlameableEntityBehavior;
use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;

class Lock
{

    use TimestampableEntityBehavior, BlameableEntityBehavior;

    const TYPE_INITIAL_LOCK = 0;

    const TYPE_NO_LOCKS     = 1;

    const TYPE_RESERVED     = 2;

    const TYPE_CLIENT       = 3;

    const TYPE_CAMPAIGN     = 4;

    const TYPE_CATEGORY     = 5;

    const TYPE_SUB_CATEGORY = 6;

    private $id;

    private $type;

    private $lead;

    private $extraction;

    private $client;

    private $campaign;

    private $category;

    private $sub_category;

    /**
     * The date when the lock was created
     * @var
     */
    protected $lock_date;

    /**
     * Saves a future timestamp for the lock expiration time
     * @var
     */
    protected $expiration_date;

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
    public function getType ()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType ($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getLockDate ()
    {
        return $this->lock_date;
    }

    /**
     * @param mixed $lock_date
     */
    public function setLockDate ($lock_date)
    {
        $this->lock_date = $lock_date;
    }

    /**
     * @return mixed
     */
    public function getExpirationDate ()
    {
        return $this->expiration_date;
    }

    /**
     * @param mixed $expiration_date
     */
    public function setExpirationDate ($expiration_date)
    {
        $this->expiration_date = $expiration_date;
    }

    /**
     * @return mixed
     */
    public function getLead ()
    {
        return $this->lead;
    }

    /**
     * @param mixed $lead
     */
    public function setLead ($lead)
    {
        $this->lead = $lead;
    }

    /**
     * Set extraction
     *
     * @param Extraction $extraction
     *
     * @return Lock
     */
    public function setExtraction (Extraction $extraction = null)
    {
        $this->extraction = $extraction;

        return $this;
    }

    /**
     * Get extraction
     * @return Extraction
     */
    public function getExtraction ()
    {
        return $this->extraction;
    }

    /**
     * @return mixed
     */
    public function getCampaign ()
    {
        return $this->campaign;
    }

    /**
     * @param mixed $campaign
     */
    public function setCampaign ($campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * @return mixed
     */
    public function getCategory ()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory ($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getClient ()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient ($client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getSubCategory ()
    {
        return $this->sub_category;
    }

    /**
     * @param mixed $sub_category
     */
    public function setSubCategory ($sub_category)
    {
        $this->sub_category = $sub_category;
    }
}
