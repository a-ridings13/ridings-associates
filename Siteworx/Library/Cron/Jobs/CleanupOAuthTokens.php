<?php

declare(strict_types=1);

namespace Siteworx\Library\Cron\Jobs;

use Carbon\Carbon;
use Siteworx\Library\Cron\Job;
use Siteworx\Library\OAuth\Entities\{AccessToken, AuthCode, RefreshToken};

final class CleanupOAuthTokens extends Job
{

//    protected string $cronExpression = '*/5 * * * *';

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $accessTokenWindow = Carbon::now()->subHour();
        $refreshTokenWindow = Carbon::now()->subMonth();
        $authCodeWindows = Carbon::now()->subMinutes(5);

        AccessToken::where('created_at', '<=', $accessTokenWindow)
            ->orWhere('is_revoked', '=', true)
            ->delete();
        RefreshToken::where('created_at', '<=', $refreshTokenWindow)
            ->orWhere('is_revoked', '=', true)
            ->delete();
        AuthCode::where('created_at', '<=', $authCodeWindows)
            ->orWhere('is_revoked', '=', true)
            ->delete();
    }
}
