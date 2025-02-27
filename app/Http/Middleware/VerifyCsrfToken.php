<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'sipd/singkron-chrome-ext/realtime',
        'sipd/singkron-chrome-ext',
        'evi-web/sipd/singkron-chrome-ext/realtime',
        'evi-web/sipd/'
    ];
}
