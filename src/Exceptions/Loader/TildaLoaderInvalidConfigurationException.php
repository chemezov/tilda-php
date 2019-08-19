<?php

namespace TildaTools\Tilda\Exceptions\Loader;

class TildaLoaderInvalidConfigurationException extends TildaLoaderException
{
    /**
     * @param string $name
     * @return TildaLoaderInvalidConfigurationException
     */
    public static function forConfigOption(string $name)
    {
        return new static("Please specify '$name' configuration option.");
    }
}
