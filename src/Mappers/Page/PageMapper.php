<?php

namespace TildaTools\Tilda\Mappers\Page;

use TildaTools\Tilda\Exceptions\Map\UnableToMapApiResponseException;
use TildaTools\Tilda\Exceptions\InvalidJsonException;
use TildaTools\Tilda\Mappers\MapperInterface;
use TildaTools\Tilda\Mappers\ObjectMapper;
use TildaTools\Tilda\Objects\Page\Page;

class PageMapper extends ObjectMapper implements MapperInterface
{

    protected $attributes = [
        'id',
        'projectid',
        'title',
        'descr',
        'img',
        'featureimg',
        'alias',
        'date',
        'sort',
        'published',
        'filename',
        'html',
    ];

    /**
     * @param string $json
     * @return Page $page
     * @throws InvalidJsonException
     * @throws UnableToMapApiResponseException
     */
    public function map(string $json)
    {
        if (($page = json_decode($json, true)) === null) {
            throw new InvalidJsonException;
        }
        if (!isset($page['result']) || !is_array($page['result'])) {
            throw new UnableToMapApiResponseException("Invalid result field at api response");
        }
        return new Page($this->mapAttributes($page['result']));
    }

}