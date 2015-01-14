<?php

namespace ListBroking\TaskControllerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Queue
 */
class Queue
{
    const CACHE_ID = 'queue';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $value1;

    /**
     * @var string
     */
    private $value2;

    /**
     * @var string
     */
    private $value3;

    /**
     * @var string
     */
    private $value4;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Queue
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value1
     *
     * @param string $value1
     * @return Queue
     */
    public function setValue1($value1)
    {
        $this->value1 = $value1;

        return $this;
    }

    /**
     * Get value1
     *
     * @return string 
     */
    public function getValue1()
    {
        return $this->value1;
    }

    /**
     * Set value2
     *
     * @param string $value2
     * @return Queue
     */
    public function setValue2($value2)
    {
        $this->value2 = $value2;

        return $this;
    }

    /**
     * Get value2
     *
     * @return string 
     */
    public function getValue2()
    {
        return $this->value2;
    }

    /**
     * Set value3
     *
     * @param string $value3
     * @return Queue
     */
    public function setValue3($value3)
    {
        $this->value3 = $value3;

        return $this;
    }

    /**
     * Get value3
     *
     * @return string 
     */
    public function getValue3()
    {
        return $this->value3;
    }

    /**
     * Set value4
     *
     * @param string $value4
     * @return Queue
     */
    public function setValue4($value4)
    {
        $this->value4 = $value4;

        return $this;
    }

    /**
     * Get value4
     *
     * @return string 
     */
    public function getValue4()
    {
        return $this->value4;
    }
}
