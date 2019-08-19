<?php

namespace TildaTools\Tilda\Exceptions\Api;


class TildaApiInvalidConfigurationException extends TildaApiException
{
    public static function forOption(string $name) {
        return new TildaApiInvalidConfigurationException("Please specify '$name' option.");
    }
}
