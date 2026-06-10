<?php

namespace Pterodactyl\Http\Requests\Admin\Extensions\Subdomain;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class SubdomainPostRequest extends AdminFormRequest
{
    /**
     * Validation rules for adding a new Cloudflare API token.
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string|size:40|regex:/^[A-Za-z0-9_\-]+$/',
            'label' => 'required|string|min:1|max:100',
        ];
    }
}
