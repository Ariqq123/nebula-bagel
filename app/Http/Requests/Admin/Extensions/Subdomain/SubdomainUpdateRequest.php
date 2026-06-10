<?php

namespace Pterodactyl\Http\Requests\Admin\Extensions\Subdomain;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class SubdomainUpdateRequest extends AdminFormRequest
{
    /**
     * Validation rules for updating extension settings.
     */
    public function rules(): array
    {
        return [
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:120',
            'cache_ttl' => 'nullable|integer|min:60|max:3600',
            'max_subdomains_per_server' => 'nullable|integer|min:1|max:50',
            'wildcard_allowed' => 'nullable|boolean',
        ];
    }
}
