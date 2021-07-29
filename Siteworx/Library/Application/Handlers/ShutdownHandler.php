<?php

declare(strict_types=1);

namespace Siteworx\Library\Application\Handlers;

use Siteworx\Library\Application\Core;

final class ShutdownHandler
{

    public function __invoke()
    {
        $error = error_get_last();

        if ($error) {
            $errorFile = $error['file'];
            $errorLine = $error['line'];
            $errorMessage = $error['message'];
            $errorType = $error['type'];

            $message = " {$errorMessage} on line {$errorLine} in file {$errorFile}.";

            switch ($errorType) {
                case E_USER_WARNING:
                case E_WARNING:
                    Core::di()->log->warning($message);

                    break;
                case E_USER_NOTICE:
                case E_NOTICE:
                case E_USER_DEPRECATED:
                    Core::di()->log->notice($message);

                    break;
                default:
                    if (Core::di()->config->get('dev_mode', false) === false) {
                        Core::di()->bugsnag->notifyError('ERROR', $message);
                    }

                    Core::di()->log->error($message);

                    break;
            }
        }
    }
}
