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

use ListBroking\AppBundle\Behavior\BlameableEntityBehavior,
    ListBroking\AppBundle\Behavior\TimestampableEntityBehavior
    ;
use Doctrine\Common\Collections\ArrayCollection;

class Campaign
{

    const CACHE_ID = 'campaign';

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;

    protected $name;

    protected $description;

    protected $client;

    protected $extractions;

    function __construct()
    {
        $this->extractions = new ArrayCollection();
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @param Extraction $extraction
     */
    public function addExtraction(Extraction $extraction){
    	$extraction->setCampaign($this);
        $this->extractions[] = $extraction;
    }

    /**
     * @param Extraction $extraction
     */
    public function removeExtraction(Extraction $extraction){
        $this->extractions->removeElement($extraction);
    }
}