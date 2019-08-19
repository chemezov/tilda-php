<?php

namespace TildaTools\Tilda\Exceptions\Api;

class TildaApiInvalidConfigurationException extends TildaApiException
{
    /**
     * @param string $name
     * @return TildaApiInvalidConfigurationException
     */
    public static function forConfigOption(string $name)
    {
        return new static("Please specify '$name' configuration option.");
    }
}
