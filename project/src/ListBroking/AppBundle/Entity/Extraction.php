<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use ListBroking\AppBundle\Behavior\BlameableEntityBehavior;
use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\ExclusionPolicy;
/**
 * @ExclusionPolicy("none")
 * @package ListBroking\AppBundle\Entity
 */
class Extraction
{
    use TimestampableEntityBehavior, BlameableEntityBehavior;

    const CAMPAIGN_ID = 'campaign_id';
    const NAME        = 'name';
    const QUANTITY    = 'quantity';
    const PAYOUT      = 'payout';

    const CACHE_ID = 'extraction';

    const STATUS_FILTRATION          = 1;
    const STATUS_CONFIRMATION        = 2;
    const STATUS_FINAL               = 3;

    const EXCLUDE_DEDUPLICATION_TYPE = 'exclude';
    const INCLUDE_DEDUPLICATION_TYPE = 'include';

    public static $status_names = array(
        self::STATUS_FILTRATION => 'Filtration',
        self::STATUS_CONFIRMATION => 'Confirmation',
        self::STATUS_FINAL => 'Finished'
    );

    public static $deduplication_names = array(
        self::EXCLUDE_DEDUPLICATION_TYPE => 'File with the leads to remove',
        self::INCLUDE_DEDUPLICATION_TYPE => 'File with the leads to keep'
    );

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $status = Extraction::STATUS_FILTRATION;

    // GENERATED STUFF

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @Exclude
     * @var array
     */
    private $filters;

    /**
     * @Exclude
     * @var array
     */
    private $readable_filters;

    /**
     * @var float
     */
    private $payout;

    /**
     * @var boolean
     */
    private $is_already_extracted = false;

    /**
     * @var boolean
     */
    private $is_deduplicating = false;

    /**
     * @var boolean
     */
    private $is_locking = false;

    /**
     * @var boolean
     */
    private $is_delivering = false;

    /**
     * @var string
     */
    private $deduplication_type;

    /**
     * @var string
     */
    private $query;

    /**
     * @var \DateTime
     */
    private $sold_at;

    /**
     * @Exclude
     * @var ArrayCollection
     */
    private $extraction_deduplications;

    /**
     * @Exclude
     * @var ArrayCollection
     */
    private $extraction_contacts;

    /**
     * @Exclude
     * @var Campaign
     */
    private $campaign;

    /**
     * @var ArrayCollection
     */
    private $extraction_log;

    function __construct ()
    {
        $this->extraction_contacts = new ArrayCollection();
        $this->extraction_deduplications = new ArrayCollection();
        $this->extraction_log = new ArrayCollection();
    }

    function __toString ()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getStatusName ()
    {
        return Extraction::$status_names[$this->status];
    }

    /**
     * Get id
     * @return integer
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * Get name
     * @return string
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Extraction
     */
    public function setName ($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get status
     * @return integer
     */
    public function getStatus ()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Extraction
     */
    public function setStatus ($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get quantity
     * @return integer
     */
    public function getQuantity ()
    {
        return $this->quantity;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Extraction
     */
    public function setQuantity ($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get filters
     * @return array
     */
    public function getFilters ()
    {
        return $this->filters;
    }

    /**
     * Set filters
     *
     * @param array $filters
     *
     * @return Extraction
     */
    public function setFilters ($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Get readable_filters
     *
     * @return array
     */
    public function getReadableFilters()
    {
        return $this->readable_filters;
    }

    /**
     * Set readable_filters
     *
     * @param array $readableFilters
     * @return Extraction
     */
    public function setReadableFilters($readableFilters)
    {
        $this->readable_filters = $readableFilters;

        return $this;
    }

    /**
     * Get payout
     * @return float
     */
    public function getPayout ()
    {
        return $this->payout;
    }

    /**
     * Set payout
     *
     * @param float $payout
     *
     * @return Extraction
     */
    public function setPayout ($payout)
    {
        $this->payout = $payout;

        return $this;
    }

    /**
     * Get isAlreadyExtracted
     * @return boolean
     */
    public function getIsAlreadyExtracted ()
    {
        return $this->is_already_extracted;
    }

    /**
     * Set isAlreadyExtracted
     *
     * @param boolean $isAlreadyExtracted
     *
     * @return Extraction
     */
    public function setIsAlreadyExtracted ($isAlreadyExtracted)
    {
        $this->is_already_extracted = $isAlreadyExtracted;

        return $this;
    }

    /**
     * Get isDeduplicating
     * @return boolean
     */
    public function getIsDeduplicating ()
    {
        return $this->is_deduplicating;
    }

    /**
     * Set isDeduplicating
     *
     * @param boolean $isDeduplicating
     *
     * @return Extraction
     */
    public function setIsDeduplicating ($isDeduplicating)
    {
        $this->is_deduplicating = $isDeduplicating;

        return $this;
    }

    /**
     * Get isLocking
     * @return boolean
     */
    public function getIsLocking ()
    {
        return $this->is_locking;
    }

    /**
     * Set isLocking
     *
     * @param boolean $isLocking
     *
     * @return Extraction
     */
    public function setIsLocking ($isLocking)
    {
        $this->is_locking = $isLocking;

        return $this;
    }

    /**
     * Get deduplicationType
     * @return string
     */
    public function getDeduplicationType ()
    {
        return $this->deduplication_type;
    }

    /**
     * Set deduplicationType
     *
     * @param string $deduplicationType
     *
     * @return Extraction
     */
    public function setDeduplicationType ($deduplicationType)
    {
        $this->deduplication_type = $deduplicationType;

        return $this;
    }

    /**
     * Get query
     * @return string
     */
    public function getQuery ()
    {
        return $this->query;
    }

    /**
     * Set query
     *
     * @param string $query
     *
     * @return Extraction
     */
    public function setQuery ($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSoldAt()
    {
        return $this->sold_at;
    }

    /**
     * @param \DateTime $sold_at
     * @return Extraction
     */
    public function setSoldAt($sold_at)
    {
        $this->sold_at = $sold_at;
        return $this;
    }

    /**
     * Add extractionDeduplication
     *
     * @param ExtractionDeduplication $extractionDeduplication
     *
     * @return Extraction
     */
    public function addExtractionDeduplication (ExtractionDeduplication $extractionDeduplication)
    {
        $extractionDeduplication->setExtraction($this);
        $this->extraction_deduplications[] = $extractionDeduplication;

        return $this;
    }

    /**
     * Remove extractionDeduplication
     *
     * @param ExtractionDeduplication $extractionDeduplication
     */
    public function removeExtractionDeduplication (ExtractionDeduplication $extractionDeduplication)
    {
        $this->extraction_deduplications->removeElement($extractionDeduplication);
    }

    /**
     * Get extractionDeduplications
     * @return ArrayCollection
     */
    public function getExtractionDeduplications ()
    {
        return $this->extraction_deduplications;
    }

    /**
     * Add extractionContact
     *
     * @param ExtractionContact $extractionContact
     *
     * @return Extraction
     */
    public function addExtractionContact (ExtractionContact $extractionContact)
    {
        $extractionContact->setExtraction($this);
        $this->extraction_contacts[] = $extractionContact;

        return $this;
    }

    /**
     * Remove extractionContact
     *
     * @param ExtractionContact $extractionContact
     */
    public function removeExtractionContact (ExtractionContact $extractionContact)
    {
        $this->extraction_contacts->removeElement($extractionContact);
    }

    /**
     * Get extractionContacts
     * @return ArrayCollection
     */
    public function getExtractionContacts ()
    {
        return $this->extraction_contacts;
    }

    /**
     * Get campaign
     * @return Campaign
     */
    public function getCampaign ()
    {
        return $this->campaign;
    }

    /**
     * Set campaign
     *
     * @param Campaign $campaign
     *
     * @return Extraction
     */
    public function setCampaign (Campaign $campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get isDelivering
     * @return boolean
     */
    public function getIsDelivering ()
    {
        return $this->is_delivering;
    }

    /**
     * Set isDelivering
     *
     * @param boolean $isDelivering
     *
     * @return Extraction
     */
    public function setIsDelivering ($isDelivering)
    {
        $this->is_delivering = $isDelivering;

        return $this;
    }

    /**
     * Add extraction_log
     *
     * @param \ListBroking\AppBundle\Entity\ExtractionLog $extractionLog
     * @return Extraction
     */
    public function addExtractionLog(\ListBroking\AppBundle\Entity\ExtractionLog $extractionLog)
    {
        $extractionLog->setExtraction($this);
        $this->extraction_log[] = $extractionLog;

        return $this;
    }

    /**
     * Remove extraction_log
     *
     * @param \ListBroking\AppBundle\Entity\ExtractionLog $extractionLog
     */
    public function removeExtractionLog(\ListBroking\AppBundle\Entity\ExtractionLog $extractionLog)
    {
        $this->extraction_log->removeElement($extractionLog);
    }

    /**
     * Get extraction_log
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExtractionLog()
    {
        return $this->extraction_log;
    }

    /**
     * Returns true if extraction is finished, false if it isn't
     *
     * @return bool
     */
    public function isFinished() : bool
    {
        return $this->status === Extraction::STATUS_FINAL;
    }
}
