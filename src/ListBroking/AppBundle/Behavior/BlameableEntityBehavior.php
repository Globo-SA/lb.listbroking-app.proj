<?php
/**
 * 
 * @author: escudeiro
 * @date: 07-05-2014
 * @time: 17:19
 * @file: BlameableEntityTrait.php
 * @ide: PhpStorm
 * @project: smark.io
 */

namespace ListBroking\AppBundle\Behavior;


trait BlameableEntityBehavior
{
    protected $created_by;

    protected $updated_by;

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param mixed $created_by
     *
     * @return $this
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updated_by;
    }

    /**
     * @param mixed $updated_by
     *
     * @return $this
     */
    public function setUpdatedBy($updated_by)
    {
        $this->updated_by = $updated_by;

        return $this;
    }

    /**
     * Behavior check
     *
     * @return bool
     */
    public function isBlameable()
    {
        return true;
    }
}
