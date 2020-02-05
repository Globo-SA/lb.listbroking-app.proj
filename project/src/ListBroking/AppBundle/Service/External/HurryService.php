<?php
/**
 *
 * @author     Diogo Basto <diogo.basto@smark.io>
 * @copyright  2017 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\External;

use GuzzleHttp\Client;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Serializer\Encoder\JsonDecode;

class HurryService {

    const PARAMETER_TOKEN = 'token';

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $apiToken;

    /**
     * @var string
     */
    protected $baseUrl;

    public function __construct($baseUrl, $apiToken, $logger){
        $this->baseUrl = $baseUrl;
        $this->apiToken = $apiToken;
        $this->logger = $logger;
    }

    /**
     * @return mixed
     */
    public function fetchAccounts()
    {
        return $this->_get('account/list');
    }

    /**
     * @param $path
     * @param array $params
     * @return mixed
     */
    protected function _get($path, $params = [])
    {
        $guzzle = new Client([
            'base_uri' => $this->baseUrl
        ]);
        $params[self::PARAMETER_TOKEN] = $this->apiToken;
        $response = $guzzle->get($path, [
            'query' => $params
        ]);
        return json_decode($response->getBody()->getContents());
    }
}
