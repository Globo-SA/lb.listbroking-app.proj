<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ExceptionHandlerBundle\Repository\ORM;

use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;
use ListBroking\ExceptionHandlerBundle\Repository\ExceptionLogRepositoryInterface;

class ExceptionLogRepository extends BaseEntityRepository implements ExceptionLogRepositoryInterface {}