<?php

namespace ListBroking\AppBundle\Model;

use Guzzle\Http\Message\Response;

/**
 * HttpClientResponse
 */
abstract class HttpClientResponse
{
    const HTTP_CODE     = 'code';
    const STATUS        = 'status';
    const ERROR_MESSAGE = 'error';
    const CONTENT       = 'content';
    const STATUS_ERROR  = 'error';

    /**
     * @var int
     */
    protected $code;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @var array|null
     */
    protected $content;

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Checks if response was successful
     * @return bool
     */
    public function wasSuccessful()
    {
        return (empty($this->errorMessage) === true && $this->status != static::STATUS_ERROR);
    }

    /**
     * Gets content $fieldName value
     *
     * @param string $fieldName
     *
     * @return mixed|null returns $fieldName value if set, null otherwise
     */
    protected function getContentField($fieldName)
    {
        if (isset($this->content[$fieldName]) === false) {
            return null;
        }

        return $this->content[$fieldName];
    }

    /**
     * Fills class fields based on the given $originalResponse
     *
     * @param Response $originalResponse
     *
     * @return void
     */
    protected abstract function setFieldsFromDecodedResponse(Response $originalResponse);
}
