<?php

namespace ListBroking\AppBundle\Model;

use Guzzle\Http\Message\Response;

class PostalCodeResponse extends HttpClientResponse
{

    const KEY_HTTP_CODE          = 'code';
    const KEY_STATUS             = 'status';
    const KEY_ERROR_MESSAGE      = 'error';
    const KEY_CONTENT            = 'content';
    const KEY_DISTRICT_ID        = 'district_id';
    const KEY_DISTRICT_NAME      = 'district';
    const KEY_CITY_ID            = 'city_id';
    const KEY_CITY_NAME          = 'city';
    const KEY_PARISH_ID          = 'parish_id';
    const KEY_PARISH_NAME        = 'parish';
    const KEY_STREET_ID          = 'street_id';
    const KEY_STREET_TYPE        = 'street_type';
    const KEY_STREET_NAME        = 'street_name';
    const KEY_POSTAL_CODE        = 'postalcode';
    const KEY_POSTAL_CODE_1      = 'cp1';
    const KEY_POSTAL_CODE_2      = 'cp2';
    const KEY_POSTAL_DESIGNATION = 'postal_designation';
    const KEY_COUNTRY_ID         = 'country_id';

    /**
     * PostalCodeResponse constructor.
     *
     * @param $originalResponse
     */
    public function __construct($originalResponse)
    {
        $this->setFieldsFromDecodedResponse($originalResponse);
    }

    /**
     * @return string|null
     */
    public function getDistrictId()
    {
        return $this->getContentField(static::KEY_DISTRICT_ID);
    }

    /**
     * @return string|null
     */
    public function getDistrictName()
    {
        return $this->getContentField(static::KEY_DISTRICT_NAME);
    }

    /**
     * @return string|null
     */
    public function getCityId()
    {
        return $this->getContentField(static::KEY_CITY_ID);
    }

    /**
     * @return string|null
     */
    public function getCityName()
    {
        return $this->getContentField(static::KEY_CITY_NAME);
    }

    /**
     * @return string|null
     */
    public function getParishId()
    {
        return $this->getContentField(static::KEY_PARISH_ID);
    }

    /**
     * @return string|null
     */
    public function getParishName()
    {
        return $this->getContentField(static::KEY_PARISH_NAME);
    }

    /**
     * @return string|null
     */
    public function getStreetId()
    {
        return $this->getContentField(static::KEY_STREET_ID);
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->getContentField(static::KEY_STREET_TYPE);
    }

    /**
     * @return string|null
     */
    public function getStreetName()
    {
        return $this->getContentField(static::KEY_STREET_NAME);
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        if (isset($this->getContent()[self::KEY_POSTAL_CODE])) {
            return $this->getContent()[self::KEY_POSTAL_CODE];
        }

        if (isset($this->getContent()[self::KEY_POSTAL_CODE_1])
            && isset(
                $this->getContent()[self::KEY_POSTAL_CODE_2]
            )) {
            return sprintf(
                '%s-%s',
                $this->getContent()[self::KEY_POSTAL_CODE_1],
                $this->getContent()[self::KEY_POSTAL_CODE_2]
            );
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getPostalDesignation()
    {
        return $this->getContentField(static::KEY_POSTAL_DESIGNATION);
    }

    /**
     * @return string|null
     */
    public function getCountryId()
    {
        return $this->getContentField(static::KEY_COUNTRY_ID);
    }

    /**
     * Fills class fields based on the given $originalResponse
     *
     * @param Response $originalResponse
     *
     * @return void
     */
    protected function setFieldsFromDecodedResponse(Response $originalResponse)
    {
        $decodedResponse = json_decode($originalResponse->getBody(), true);

        $this->code    = $decodedResponse[self::KEY_HTTP_CODE];
        $this->status  = $decodedResponse[self::KEY_STATUS];
        $this->content = $decodedResponse[self::KEY_CONTENT];

        if ($this->errorMessage === null) {
            $this->errorMessage = '';
        }
    }
}
