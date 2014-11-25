<?php
/**
 * 
 * @author: escudeiro
 * @date: 07-05-2014
 * @time: 17:13
 * @file: DoctrineTimestampableTrait.php
 * @ide: PhpStorm
 * @project: smark.io
 */

namespace ListBroking\AppBundle\Behavior;


trait TimestampableEntityBehavior
{
    protected $created_at;

    protected $updated_at;

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param \DateTime $updated_at
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * Behavior check
     *
     * @return bool
     */
    public function isTimestampable()
    {
        return true;
    }
} 
