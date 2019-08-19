<?php

namespace TildaTools\Tilda\Mappers;

use TildaTools\Tilda\Exceptions\Map\MapperNotFoundException;
use TildaTools\Tilda\Mappers\Page\ExportedPageMapper;
use TildaTools\Tilda\Mappers\Page\PageMapper;
use TildaTools\Tilda\Mappers\Page\PagesListMapper;
use TildaTools\Tilda\Mappers\Project\ExportedProjectMapper;
use TildaTools\Tilda\Mappers\Project\ProjectMapper;
use TildaTools\Tilda\Mappers\Project\ProjectsListMapper;

class MapperFactory
{

    /**
     * @param string $apiMethod
     * @return MapperInterface $mapper
     * @throws MapperNotFoundException
     */
    public static function create(string $apiMethod)
    {
        $mappers = [
            'getprojectslist' => ProjectsListMapper::class,
            'getproject' => ProjectMapper::class,
            'getprojectexport' => ExportedProjectMapper::class,
            'getpageslist' => PagesListMapper::class,
            'getpage' => PageMapper::class,
            'getpagefull' => PageMapper::class,
            'getpageexport' => ExportedPageMapper::class,
            'getpagefullexport' => ExportedPageMapper::class,
        ];
        if (isset($mappers[$apiMethod])) {
            return new $mappers[$apiMethod];
        }
        throw new MapperNotFoundException("Mapper for $apiMethod api method not found");
    }

}