<?php

namespace Pterodactyl\Http\Requests\Admin\Extensions\Subdomain;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class SubdomainPutRequest extends AdminFormRequest
{
    /**
     * Validation rules for creating a new subdomain DNS record.
     */
    public function rules(): array
    {
        return [
            'subdomain' => 'required|string|min:1|max:63',
            'zone_id' => 'required|string|size:32|regex:/^[0-9a-f]+$/',
            'record_type' => 'required|string|in:A,AAAA,CNAME',
            'target' => 'required|string|max:253',
            'server_id' => 'nullable|integer|exists:servers,id',
            'proxied' => 'nullable|boolean',
            'ttl' => 'nullable|integer|min:1',
        ];
    }
}
