<?php

declare(strict_types=1);

namespace Siteworx\Cli\Commands\OAuth;

use League\CLImate\TerminalObject\Dynamic\Input;
use Siteworx\Cli\Commands\Command;
use Siteworx\Library\Crypt;
use Siteworx\Library\OAuth\Entities\User;
use Siteworx\Library\Utilities\Helpers;

/**
 * Class CreateUser
 * @package Siteworx\Cli\Commands\OAuth
 */
final class CreateUser extends Command
{

    /**
     * @inheritDoc
     */
    public static function getHelp(): string
    {
        return 'create oauth user';
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(): int
    {
        /** @var Input $prompt */
        $prompt = $this->cli->input('User Name: ');
        $userName = $prompt->prompt();

        /** @var Input $prompt */
        $prompt = $this->cli->input('Email: ');
        $email = $prompt->prompt();

        /** @var Input $prompt */
        $prompt = $this->cli->input('Password (blank for random): ');
        $password = $prompt->prompt();

        if ($password === '') {
            $password = Helpers::generateRandString(13);
        }

        $user = new User();
        $user->username = $userName;
        $user->email = $email;
        $user->password = Crypt::encryptPassword($password);
        $user->save();

        $this->cli->info('Username: ' . $userName);
        $this->cli->info('Email: ' . $email);
        $this->cli->info('Password: ' . $password);

        return 0;
    }

    /**
     * @inheritDoc
     */
    public static function commandSignature(): string
    {
        return 'create-user';
    }
}
