<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Engine\FilterEngine;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Form\FiltersType;
use ListBroking\AppBundle\Service\Base\BaseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ExtractionService extends BaseService implements ExtractionServiceInterface
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var FilterEngine
     */
    private $f_engine;

    function __construct (RequestStack $requestStack, FilterEngine $filterEngine)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->f_engine = $filterEngine;
    }

    public function cloneExtraction (Extraction $extraction)
    {
        /** @var Extraction $clonedObject */
        $clonedObject = clone $extraction;

        $clonedObject->setName($extraction->getName() . " (duplicate)");
        $clonedObject->setStatus(Extraction::STATUS_FILTRATION);
        $clonedObject->getExtractionContacts()
                     ->clear()
        ;
        $clonedObject->getExtractionDeduplications()
                     ->clear()
        ;
        $clonedObject->setDeduplicationType(null);
        $clonedObject->setQuery(null);
        $clonedObject->setIsAlreadyExtracted(false);

        return $clonedObject;
    }

    public function findExtractionContacts (Extraction $extraction, $limit = null)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionContact')
                        ->getExtractionContacts($extraction, $limit)
            ;
    }

    public function findExtractionSummary (Extraction $extraction)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionContact')
                        ->getExtractionSummary($extraction)
            ;
    }

    public function generateLocks (Extraction $extraction, $lock_types)
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
                 ->generateLocks($extraction, $lock_types, $this->findConfig('lock.time'))
        ;
    }

    //    /**
    //     * Used to import a file with Leads
    //     *
    //     * @param $filename
    //     *
    //     * @internal param $filename
    //     * @return mixed
    //     */
    //    public function importExtraction ($filename)
    //    {
    //        $file_handler = new FileHandler();
    //        $obj = $file_handler->import($filename);
    //
    //        return $file_handler->convertToArray($obj, false);
    //    }

    public function getExtractionContactsQuery (Extraction $extraction)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionContact')
                        ->getExtractionContactsQuery($extraction)
            ;
    }

    public function handleFiltration (Extraction $extraction)
    {
        $form = $this->form_factory->createBuilder('filters', $extraction->getFilters())
                                   ->getForm()
        ;

        // Handle the filters form
        $filters_form = $form->handleRequest($this->request);
        $filters = $filters_form->getData();

        if ( $this->request->getMethod() == Request::METHOD_POST )
        {
            // Sets the new Filters and mark the Extraction to reprocess
            // and sets the status to confirmation
            $extraction->setFilters($filters);
            $readable_filters = FiltersType::humanizeFilters($this->entity_manager, $extraction->getFilters());
            $extraction->setReadableFilters($readable_filters);

            $extraction->setIsAlreadyExtracted(false);
            $extraction->setStatus(Extraction::STATUS_CONFIRMATION);

            $this->updateEntity($extraction);

            return true;
        }

        return false;
    }

    public function runExtraction (Extraction $extraction)
    {
        // if the Extraction is closed don't run
        if ( $extraction->getStatus() == Extraction::STATUS_FINAL )
        {
            return false;
        }

        $this->executeFilterEngine($extraction);

        return true;
    }

    public function uploadDeduplicationsByFile (Extraction $extraction, $filename, $field)
    {

        $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
                 ->uploadDeduplicationsByFile($filename, $extraction, $field)
        ;
    }

    /**
     * Executes the filtering engine and adds the contacts
     * to the Extraction
     *
     * @param Extraction $extraction
     *
     * @return void
     * @throws \ListBroking\AppBundle\Exception\InvalidFilterObjectException
     */
    private function executeFilterEngine (Extraction $extraction)
    {
        // Runs the Filter compilation and generates the QueryBuilder
        $qb = $this->f_engine->compileFilters($extraction);

        $query = $qb->getQuery();

        // Add Contacts to the Extraction
        $contacts = $query->execute();

        $this->entity_manager->getRepository('ListBrokingAppBundle:Extraction')
                 ->addContacts($extraction, $contacts, false)
        ;

        $query = array(
            'dql' => $query->getDQL(),
            'sql' => $query->getSQL(),
        );

        // Update Extraction
        $extraction->setStatus(Extraction::STATUS_CONFIRMATION);
        $extraction->setIsAlreadyExtracted(true);
        $extraction->setQuery(json_encode($query));

        $this->updateEntity($extraction);
    }
}