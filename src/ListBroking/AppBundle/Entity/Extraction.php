<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Entity;

use ListBroking\AppBundle\Behavior\BlameableEntityBehavior;
use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;
use Doctrine\Common\Collections\ArrayCollection;

class Extraction {

    const CACHE_ID = 'extraction';

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    const STATUS_CONFIGURATION = 0;
    const STATUS_FILTRATION = 1;
    const STATUS_CONFIRMATION = 2;
    const STATUS_FINAL = 3;

    protected $status_names = array(
        0 => 'Confirmation',
        1 => 'Filtration',
        2 => 'Confirmation',
        3 => 'Finished'
    );

    protected $id;

    protected $name;

    protected $quantity;

    protected $filters;

    protected $payout;

    protected $status;

    protected $campaign;

    protected $contacts;

    protected $extraction_deduplications;

    function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->extraction_deduplications = new ArrayCollection();
    }

    function __toString()
    {
        return $this->name;
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
    public function getName()
    {
        return $this->name;
    }/**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
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

    public function getStatusName(){
        return $this->status_names[$this->status];
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

    /**
     * @return ArrayCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact){

        // Only add if its new
        if(!$this->contacts->contains($contact)){
            $contact->addExtraction($this);
            $this->contacts[] = $contact;
        }
    }

    public function removeContact(Contact $contact){
        $this->contacts->removeElement($contact);
    }

    /**
     * @return ArrayCollection
     */
    public function getExtractionDeduplications()
    {
        return $this->extraction_deduplications;
    }


    public function addExtractionDeduplication(ExtractionDeduplication $extractionDeduplication){
        $extractionDeduplication->setExtraction($this);
        $this->extraction_deduplications[] = $extractionDeduplication;
    }

    public function removeExtractionDeduplication(ExtractionDeduplication $extractionDeduplication){
        $this->extraction_deduplications->removeElement($extractionDeduplication);
    }
}