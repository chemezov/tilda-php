<?php

namespace TildaTools\Tilda\Mappers\Project;

use TildaTools\Tilda\Exceptions\Map\UnableToMapApiResponseException;
use TildaTools\Tilda\Exceptions\InvalidJsonException;
use TildaTools\Tilda\Mappers\MapperInterface;
use TildaTools\Tilda\Mappers\ObjectMapper;
use TildaTools\Tilda\Objects\Project\ProjectsListItem;

class ProjectsListMapper extends ObjectMapper implements MapperInterface
{

    protected $attributes = [
        'id',
        'title',
        'descr',
    ];

    /**
     * @param string $json
     * @return ProjectsListItem[] $projectsList
     * @throws InvalidJsonException
     * @throws UnableToMapApiResponseException
     */
    public function map(string $json)
    {
        if (($projects = json_decode($json, true)) === null) {
            throw new InvalidJsonException;
        }
        if (!isset($projects['result']) || !is_array($projects['result'])) {
            throw new UnableToMapApiResponseException("Invalid result field at api response");
        }
        $projectsList = [];
        foreach ($projects['result'] as $project) {
            $projectsList[] = new ProjectsListItem($this->mapAttributes($project));
        }
        return $projectsList;
    }

}