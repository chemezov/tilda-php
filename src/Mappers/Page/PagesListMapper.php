<?php

namespace TildaTools\Tilda\Mappers\Page;

use TildaTools\Tilda\Exceptions\Map\UnableToMapApiResponseException;
use TildaTools\Tilda\Exceptions\InvalidJsonException;
use TildaTools\Tilda\Mappers\MapperInterface;
use TildaTools\Tilda\Mappers\ObjectMapper;
use TildaTools\Tilda\Objects\Page\PagesListItem;

class PagesListMapper extends ObjectMapper implements MapperInterface
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
    ];

    /**
     * @param string $json
     * @return PagesListItem[]
     * @throws InvalidJsonException
     * @throws UnableToMapApiResponseException
     */
    public function map(string $json)
    {
        if (($pages = json_decode($json, true)) === null) {
            throw new InvalidJsonException;
        }
        if (!isset($pages['result']) || !is_array($pages['result'])) {
            throw new UnableToMapApiResponseException("Invalid result field at api response");
        }
        $pagesList = [];
        foreach ($pages['result'] as $page) {
            $pagesList[] = new PagesListItem($this->mapAttributes($page));
        }
        return $pagesList;
    }

}
