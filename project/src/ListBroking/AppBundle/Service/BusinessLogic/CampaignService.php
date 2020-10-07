<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Enum\HttpStatusCodeEnum;
use ListBroking\AppBundle\Repository\CampaignRepositoryInterface;
use ListBroking\AppBundle\Repository\ClientRepositoryInterface;
use ListBroking\AppBundle\Service\Base\BaseService;

/**
 * ListBroking\AppBundle\Service\BusinessLogic\StagingService
 */
class CampaignService extends BaseService implements CampaignServiceInterface
{
    /**
     * @var CampaignRepositoryInterface
     */
    protected $campaignRepository;

    /**
     * @var ClientRepositoryInterface
     */
    protected $clientRepository;

    /**
     * StagingService constructor.
     *
     * @param CampaignRepositoryInterface $campaignRepository
     * @param ClientRepositoryInterface   $clientRepository
     */
    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        ClientRepositoryInterface $clientRepository
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->clientRepository   = $clientRepository;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function createCampaign(array $campaignData): Campaign
    {
        $client = $this->clientRepository->getById($campaignData[Campaign::CLIENT_ID]);
        if ($client === null) {
            throw new \Exception('An invalid client_id was provided', HttpStatusCodeEnum::HTTP_STATUS_CODE_BAD_REQUEST);
        }

        return $this->campaignRepository->createCampaign($client, $campaignData);
    }

}
