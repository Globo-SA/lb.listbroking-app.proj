<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Service;
use Adclick\CacheBundle\Manager\CacheManagerInterface;
use ListBroking\CoreBundle\Service\BaseService;
use ListBroking\LeadBundle\Repository\ContactRepositoryInterface;
use ListBroking\LeadBundle\Repository\ORM\CountyRepository;
use ListBroking\LeadBundle\Repository\ORM\DistrictRepository;
use ListBroking\LeadBundle\Repository\ORM\GenderRepository;
use ListBroking\LeadBundle\Repository\ORM\OwnerRepository;
use ListBroking\LeadBundle\Repository\ORM\ParishRepository;
use ListBroking\LeadBundle\Repository\ORM\SourceRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ContactDetailsService extends BaseService implements ContactRepositoryInterface{
    private $county_repo;

    private $district_repo;

    private $gender_repo;

    private $owner_repo;

    private $parish_repo;

    private $source_repo;

    function __construct(
        CacheManagerInterface $cache,
        ValidatorInterface $validator,
        CountyRepository $county_repo,
        DistrictRepository $district_repo,
        GenderRepository $gender_repo,
        OwnerRepository $owner_repo,
        ParishRepository $parish_repo,
        SourceRepository $source_repo
    ) {
        parent::__construct($cache, $validator);
        $this->county_repo = $county_repo;
        $this->district_repo = $district_repo;
        $this->gender_repo = $gender_repo;
        $this->owner_repo = $owner_repo;
        $this->parish_repo = $parish_repo;
        $this->source_repo = $source_repo;
    }


}