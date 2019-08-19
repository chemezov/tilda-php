<?php

namespace TildaTools\Tilda\Mappers\Project;

use TildaTools\Tilda\Exceptions\Map\UnableToMapApiResponseException;
use TildaTools\Tilda\Exceptions\InvalidJsonException;
use TildaTools\Tilda\Mappers\MapperInterface;
use TildaTools\Tilda\Mappers\ObjectMapper;
use TildaTools\Tilda\Objects\Project\ExportedProject;

class ExportedProjectMapper extends ObjectMapper implements MapperInterface
{

    protected $attributes = [
        'id',
        'title',
        'descr',
        'customdomain',
        'export_csspath',
        'export_jspath',
        'export_imgpath',
        'indexpageid',
        'page404id',
        'htaccess',
    ];

    protected $assets = [
        'images',
        'css',
        'js',
    ];

    /**
     * @param string $json
     * @return ExportedProject $project
     * @throws InvalidJsonException
     * @throws UnableToMapApiResponseException
     */
    public function map(string $json)
    {
        if (($project = json_decode($json, true)) === null) {
            throw new InvalidJsonException;
        }
        if (!isset($project['result']) || !is_array($project['result'])) {
            throw new UnableToMapApiResponseException("Invalid result field at api response");
        }
        return new ExportedProject($this->mapAttributes($project['result']));
    }

}
