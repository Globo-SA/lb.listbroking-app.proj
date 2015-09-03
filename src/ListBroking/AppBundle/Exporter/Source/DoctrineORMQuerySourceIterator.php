<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 *
 */

namespace ListBroking\AppBundle\Exporter\Source;

use Exporter\Source\DoctrineORMQuerySourceIterator as BaseSourceIterator;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;

class DoctrineORMQuerySourceIterator extends BaseSourceIterator {

    public function current ()
    {
        $current = $this->iterator->current();

        $data = array();

        foreach ($this->propertyPaths as $name => $propertyPath) {
            try {
                $data[$name] = $this->getValue($this->propertyAccessor->getValue($current[0], $propertyPath));
            } catch (UnexpectedTypeException $e) {
                //non existent object in path will be ignored
                $data[$name] = null;
            }
        }

        $this->query->getEntityManager()->getUnitOfWork()->detach($current[0]);
        $this->query->getEntityManager()->clear();

        return $data;
    }
}