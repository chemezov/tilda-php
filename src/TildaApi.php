<?php

namespace TildaTools\Tilda;

use TildaTools\Tilda\Exceptions\Api\HttpClientExceptions;
use TildaTools\Tilda\Exceptions\Api\TildaApiConnectionException;
use TildaTools\Tilda\Exceptions\Api\TildaApiErrorResponseException;
use TildaTools\Tilda\Exceptions\Api\TildaApiException;
use TildaTools\Tilda\Exceptions\Api\TildaApiInvalidConfigurationException;
use TildaTools\Tilda\Exceptions\InvalidJsonException;
use TildaTools\Tilda\Mappers\MapperFactory;
use TildaTools\Tilda\Objects\Page\ExportedPage;
use TildaTools\Tilda\Objects\Page\Page;
use TildaTools\Tilda\Objects\Page\PagesListItem;
use TildaTools\Tilda\Objects\Project\ExportedProject;
use TildaTools\Tilda\Objects\Project\Project;
use TildaTools\Tilda\Objects\Project\ProjectsListItem;

class TildaApi
{
    const CONFIG_OPTION_PUBLIC_KEY = 'publicKey';
    const CONFIG_OPTION_SECRET_KEY = 'secretKey';

    /** @var string */
    protected $endpoint = 'http://api.tildacdn.info/v1';
    /** @var string */
    protected $publicKey;
    /** @var string */
    protected $secretKey;

    /**
     * TildaApi constructor.
     * @param array $config
     * @throws TildaApiInvalidConfigurationException
     */
    public function __construct(array $config)
    {
        $this->endpoint = $config['endpoint'] || $this->endpoint;
        $this->publicKey = $config[self::CONFIG_OPTION_PUBLIC_KEY];
        if (!$this->publicKey) {
            throw TildaApiInvalidConfigurationException::forOption(self::CONFIG_OPTION_PUBLIC_KEY);
        }
        if (!$this->secretKey) {
            throw TildaApiInvalidConfigurationException::forOption(self::CONFIG_OPTION_SECRET_KEY);
        }
        $this->secretKey = $config['secretKey'];
    }

    /**
     * @param bool $asJson
     * @return ProjectsListItem[]|string
     * @throws TildaApiException
     * @throws InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     */
    public function getProjectsList($asJson = false)
    {
        return $this->request('getprojectslist', [], $asJson);
    }

    /**
     * @param int $projectId
     * @param bool $asJson
     * @return Project|string
     * @throws TildaApiException
     * @throws InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     */
    public function getProject(int $projectId, $asJson = false)
    {
        return $this->request('getproject', ['projectid' => $projectId], $asJson);
    }

    /**
     * @param int $projectId
     * @param bool $asJson
     * @return ExportedProject|string
     * @throws TildaApiException
     * @throws InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     */
    public function getProjectExport(int $projectId, $asJson = false)
    {
        return $this->request('getprojectexport', ['projectid' => $projectId], $asJson);
    }

    /**
     * @param int $projectId
     * @param bool $asJson
     * @return PagesListItem[]|string
     * @throws TildaApiException
     * @throws InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     */
    public function getPagesList(int $projectId, $asJson = false)
    {
        return $this->request('getpageslist', ['projectid' => $projectId], $asJson);
    }

    /**
     * @param int $pageId
     * @param bool $asJson
     * @return Page|string
     * @throws TildaApiException
     * @throws InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     */
    public function getPage(int $pageId, $asJson = false)
    {
        return $this->request('getpage', ['pageid' => $pageId], $asJson);
    }

    /**
     * @param int $pageId
     * @param bool $asJson
     * @return Page|string
     * @throws TildaApiException
     * @throws InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     */
    public function getPageFull(int $pageId, $asJson = false)
    {
        return $this->request('getpagefull', ['pageid' => $pageId], $asJson);
    }

    /**
     * @param int $pageId
     * @param bool $asJson
     * @return ExportedPage|string
     * @throws TildaApiException
     * @throws InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     */
    public function getPageExport(int $pageId, $asJson = false)
    {
        return $this->request('getpageexport', ['pageid' => $pageId], $asJson);
    }

    /**
     * @param int $pageId
     * @param bool $asJson
     * @return ExportedPage|string
     * @throws TildaApiException
     * @throws InvalidJsonException
     * @throws Exceptions\Map\MapperNotFoundException
     */
    public function getPageFullExport(int $pageId, $asJson = false)
    {
        return $this->request('getpagefullexport', ['pageid' => $pageId], $asJson);
    }

    /**
     * @param $uri
     * @param array $params
     * @param bool $asJson
     * @return mixed|string
     * @throws Exceptions\Map\MapperNotFoundException
     * @throws HttpClientExceptions
     * @throws InvalidJsonException
     * @throws TildaApiConnectionException
     * @throws TildaApiErrorResponseException
     */
    protected function request($uri, $params = [], $asJson = false)
    {
        $url = $this->endpoint . '/' . $uri . $this->queryString($params);
        if ($curl = curl_init()) {
            $headers = [
                'Content-type: application/json',
                'Accept: application/json'
            ];
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            if (!($data = curl_exec($curl))) {
                throw new TildaApiConnectionException(curl_error($curl));
            }
            if ($asJson) {
                return $data;
            }
            if (($decoded = json_decode($data)) === null) {
                throw new InvalidJsonException;
            }
            if ($decoded->status != 'FOUND') {
                throw new TildaApiErrorResponseException($decoded->message);
            }
            curl_close($curl);
            return MapperFactory::create($uri)->map($data);
        }
        throw new HttpClientExceptions('Unable to init curl');
    }

    protected function queryString($params = [])
    {
        $accessParams = [
            'publickey' => $this->publicKey,
            'secretkey' => $this->secretKey
        ];
        return '?' . http_build_query(array_merge($params, $accessParams));
    }
}
