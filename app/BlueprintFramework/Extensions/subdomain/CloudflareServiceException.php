<?php

namespace Pterodactyl\BlueprintFramework\Extensions\subdomain;

use Exception;

/**
 * Exception thrown when Cloudflare API interactions fail.
 *
 * This exception is used for network failures, rate limiting,
 * and other Cloudflare API errors that cannot be recovered from.
 */
class CloudflareServiceException extends Exception
{
}
