<?php

namespace IncOre\Tilda\Mappers;

use IncOre\Tilda\Exceptions\Map\UnableToMapApiResponseException;
use IncOre\Tilda\Objects\Asset;

class ObjectMapper
{

    protected $attributes = [];
    protected $assets = [];

    /**
     * @param array $array
     * @return array $attributes
     * @throws UnableToMapApiResponseException
     */
    protected function mapAttributes(array $array)
    {
        $attributes = [];
        foreach ($this->attributes as $attribute) {
            if (!array_key_exists($attribute, $array)) {
                throw new UnableToMapApiResponseException("No attribute $attribute specified");
            }
            $attributes[$attribute] = $array[$attribute];
        }
        $assets = [];
        foreach ($this->assets as $assetType) {
            foreach ($array[$assetType] as $asset) {
                if (!array_key_exists($array, $assetType)) {
                    throw new UnableToMapApiResponseException("No $assetType assets specified");
                }
                $assets[$assetType][] = new Asset($asset);
            }
        }
        return array_merge($attributes, $assets);
    }

}