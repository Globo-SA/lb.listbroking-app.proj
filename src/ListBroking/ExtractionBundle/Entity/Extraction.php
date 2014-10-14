<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ExtractionBundle\Entity;

use Adclick\DoctrineBehaviorBundle\Behavior\BlameableEntityBehavior,
    Adclick\DoctrineBehaviorBundle\Behavior\TimestampableEntityBehavior
    ;
use ListBroking\LeadBundle\Entity\Lead;

class Extraction {

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    const STATUS_CONFIGURATION = 0;
    const STATUS_FILTRATION = 1;
    const STATUS_CONFIRMATION = 2;
    const STATUS_FINAL = 3;

    protected $id;

    protected $is_active;

    protected $quantity;

    protected $filters;

    protected $payout;

    protected $status;

    protected $campaign;

    protected $leads;

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
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param mixed $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return mixed
     */
    public function getPayout()
    {
        return $this->payout;
    }

    /**
     * @param mixed $payout
     */
    public function setPayout($payout)
    {
        $this->payout = $payout;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param mixed $campaign
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
    }

    public function addLead(Lead $lead){
        $lead->addExtraction($this);
        $this->leads[] = $lead;
    }

    public function removeLead(Lead $lead){
        $this->leads->removeElement($lead);
    }
}