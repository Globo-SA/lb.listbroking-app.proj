<?php
/**
 *
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\CoreBundle\Repository;


interface CategoryRepositoryInterface
{
    /**
     * @return mixed
     */
    public function findAll();
} 