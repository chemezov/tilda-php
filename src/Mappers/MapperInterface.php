<?php

namespace TildaTools\Tilda\Mappers;

interface MapperInterface
{
    /**
     * @param string $json
     * @return mixed
     */
    public function map(string $json);
}
