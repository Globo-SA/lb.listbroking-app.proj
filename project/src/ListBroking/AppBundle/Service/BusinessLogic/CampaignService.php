<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Enum\HttpStatusCodeEnum;
use ListBroking\AppBundle\Repository\CampaignRepositoryInterface;
use ListBroking\AppBundle\Repository\ClientRepositoryInterface;
use ListBroking\AppBundle\Service\Base\BaseService;
use ListBroking\AppBundle\Service\External\HurryService;

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
     * @var HurryService
     */
    protected $hurryService;

    /**
     * StagingService constructor.
     *
     * @param CampaignRepositoryInterface $campaignRepository
     * @param ClientRepositoryInterface   $clientRepository
     * @param HurryService                $hurryService
     */
    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        ClientRepositoryInterface $clientRepository,
        HurryService $hurryService
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->clientRepository   = $clientRepository;
        $this->hurryService       = $hurryService;
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

        $accountId = $this->getAccountId($client);

        return $this->campaignRepository->createCampaign($client, $accountId, $campaignData);
    }

    /**
     * @param Client $client
     *
     * @return mixed|null
     */
    private function getAccountId(Client $client)
    {
        $accountId = null;
        try {
            foreach ($this->hurryService->fetchAccounts() as $hurryAccount) {
                if (strtolower(trim($hurryAccount->client_name)) == strtolower(trim($client->getName()))) {
                    $accountId = $hurryAccount->id;
                }
            }
        }catch (\Exception $exception){
        }

        return $accountId;
    }

}
