<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Repository\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use ListBroking\DoctrineBundle\Exception\EntityClassMissingException;
use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;
use ListBroking\LockBundle\Repository\LockHistoryRepositoryInterface;

class LockHistoryRepository extends BaseEntityRepository implements LockHistoryRepositoryInterface {

    /**
     * Creates a new LockHistory using a Lock
     *
     * @param null|array $preset
     *
     * @throws EntityClassMissingException
     * @return mixed
     */
    public function createFromLock($preset = null)
    {
        if (empty($this->entity_class))
        {
            throw new EntityClassMissingException("Service declaration for {$this->getEntityName()} must have a class name to implement createNew()");
        }

        foreach($preset as $key  => $field){
            if($field instanceof \DateTime){
                $preset[$key] = $field->format('Y-m-d h:i:s');
            }
        }

        /** @var Connection $con */
        $con = $this->entityManager->getConnection();
        $con->insert('lb_lock_history', $preset);
    }
}