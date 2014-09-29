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
use ListBroking\LeadBundle\Repository\ORM\ContactRepository;
use ListBroking\LeadBundle\Repository\ORM\LeadRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LeadService extends BaseService implements LeadServiceInterface {
    private $contact_repo;

    private $lead_repo;

    function __construct(
        CacheManagerInterface $cache,
        ValidatorInterface $validator,
        ContactRepository $contact_repo,
        LeadRepository $lead_repo
    ) {
        parent::__construct($cache, $validator);
        $this->contact_repo = $contact_repo;
        $this->lead_repo = $lead_repo;
    }


}