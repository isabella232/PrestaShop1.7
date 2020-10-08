<?php

namespace MolliePrefix\Mollie\Api\Exceptions;

class IncompatiblePlatform extends \MolliePrefix\Mollie\Api\Exceptions\ApiException
{
    const INCOMPATIBLE_PHP_VERSION = 1000;
    const INCOMPATIBLE_CURL_EXTENSION = 2000;
    const INCOMPATIBLE_CURL_FUNCTION = 2500;
    const INCOMPATIBLE_JSON_EXTENSION = 3000;
}
