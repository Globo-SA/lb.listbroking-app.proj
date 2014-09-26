<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ExtractionBundle\Service;


use Adclick\CacheBundle\Manager\CacheManagerInterface;
use ListBroking\CoreBundle\Service\BaseService;
use ListBroking\ExtractionBundle\Repository\ORM\ExtractionRepository;
use ListBroking\ExtractionBundle\Repository\ORM\ExtractionTemplateRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExtractionService extends BaseService implements ExtractionServiceInterface {

    private $extraction_repo;
    private $extraction_template_repo;

    const EXTRACTION_LIST = 'extraction_list';
    const EXTRACTION_SCOPE = 'extraction';

    const EXTRACTION_TEMPLATE_LIST = 'extraction_template_list';
    const EXTRACTION_TEMPLATE_SCOPE = 'extraction_template';

    function __construct(CacheManagerInterface $cache, ValidatorInterface $validator, ExtractionRepository$extraction_repo, ExtractionTemplateRepository $extraction_template_repo)
    {
        parent::__construct($cache, $validator);
        $this->extraction_repo = $extraction_repo;
        $this->extraction_template_repo = $extraction_template_repo;
    }

    /**
     * Gets list of extractions
     * @param bool $only_active
     * @return mixed
     */
    public function getExtractionList($only_active = true){
        return $this->getList(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $only_active);

    }

    /**
     * Gets a single extraction
     * @param $id
     * @return mixed
     */
    public function getExtraction($id){
        return $this->get(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $id);

    }

    /**
     * Adds a single extraction
     * @param $extraction
     * @return mixed
     */
    public function addExtraction($extraction){
        $this->add(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $extraction);
        return $this;
    }

    /**
     * Removes a single extraction
     * @param $id
     * @return mixed
     */
    public function removeExtraction($id){
        $this->remove(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $id);
        return $this;
    }

    /**
     * Updates a single country
     * @param $extraction
     * @return mixed
     */
    public function updateExtraction($extraction){
        $this->update(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $extraction);
        return $this;
    }

    /**
     * Gets list of extraction_templates
     * @param bool $only_active
     * @return mixed
     */
    public function getExtractionTemplateList($only_active = true){
        return $this->getList(self::EXTRACTION_TEMPLATE_LIST, self::EXTRACTION_TEMPLATE_SCOPE, $this->extraction_template_repo, $only_active);

    }

    /**
     * Gets a single extraction_template
     * @param $id
     * @return mixed
     */
    public function getExtractionTemplate($id){
        return $this->get(self::EXTRACTION_TEMPLATE_LIST, self::EXTRACTION_TEMPLATE_SCOPE, $this->extraction_template_repo, $id);

    }

    /**
     * Adds a single extraction_template
     * @param $extraction_template
     * @return mixed
     */
    public function addExtractionTemplate($extraction_template){
        $this->add(self::EXTRACTION_TEMPLATE_LIST, self::EXTRACTION_TEMPLATE_SCOPE, $this->extraction_template_repo, $extraction_template);
        return $this;
    }

    /**
     * Removes a single extraction_template
     * @param $id
     * @return mixed
     */
    public function removeExtractionTemplate($id){
        $this->remove(self::EXTRACTION_TEMPLATE_LIST, self::EXTRACTION_TEMPLATE_SCOPE, $this->extraction_template_repo, $id);
        return $this;
    }

    /**
     * Updates a single extraction_template
     * @param $extraction_template
     * @return mixed
     */
    public function updateExtractionTemplate($extraction_template){
        $this->update(self::EXTRACTION_TEMPLATE_LIST, self::EXTRACTION_TEMPLATE_SCOPE, $this->extraction_template_repo, $extraction_template);
        return $this;
    }
} 