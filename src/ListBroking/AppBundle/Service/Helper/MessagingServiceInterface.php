<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Service\Helper;

interface MessagingServiceInterface
{

    /**
     * Publishes a new message to the Queue System
     *
     * @param $producer_id
     * @param $msg
     *
     * @return mixed
     */
    public function publishMessage ($producer_id, $msg);
} 