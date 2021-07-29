<?php

declare(strict_types=1);

namespace Siteworx\Cli\Commands\OAuth;

use League\CLImate\TerminalObject\Dynamic\Input;
use Siteworx\Cli\Commands\Command;
use Siteworx\Library\Application\Core;

/**
 * Class GenerateKey
 * @package Siteworx\Cli\Commands\OAuth
 */
class GenerateKey extends Command
{

    /**
     * @return string
     */
    public static function getHelp(): string
    {
        return 'Generates new oAuth Key' .
            " \n\t\t" .
            ' [--write] save to file <red>WARNING! Destructive</red>' . " \n\t\t" .
            ' [-s|-sign] self sign key. This will add a signature to your jwt';
    }

    /**
     * @return string
     */
    public static function commandSignature(): string
    {
        return 'generate-key';
    }

    /**
     * @return int Return exit code
     * @throws \Exception
     */
    public function execute(): int
    {
        $this->cli->arguments->add([
            'writeFile' => [
                'longPrefix' => 'write',
                'castTo' => 'bool',
                'default' => false,
                'noValue' => true
            ],
            'sign' => [
                'prefix' => 's',
                'longPrefix' => 'sign',
                'castTo' => 'bool',
                'default' => false,
                'noValue' => true
            ]
        ]);

        $this->cli->arguments->parse();

        $config = [
            'digest_alg' => 'sha512',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $cert = null;

        $res = openssl_pkey_new($config);

        openssl_pkey_export($res, $privateKey);

        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey['key'];

        if ($this->cli->arguments->get('sign')) {

            /** @var Input $prompt */
            $prompt = $this->cli->input('Country Name [US]: ');
            $countryName = $prompt->prompt();
            $countryName = $countryName !== '' ? $countryName : 'US';

            /** @var Input $prompt */
            $prompt = $this->cli->input('State or Province Name [VA]: ');
            $stateOrProvinceName = $prompt->prompt();
            $stateOrProvinceName = $stateOrProvinceName !== '' ? $stateOrProvinceName : 'VA';

            /** @var Input $prompt */
            $prompt = $this->cli->input('Locality Name [Someplace]: ');
            $localityName = $prompt->prompt();
            $localityName = $localityName !== '' ? $localityName : 'Someplace';

            /** @var Input $prompt */
            $prompt = $this->cli->input('Organization Name [Awesome PHP LLC]: ');
            $organizationName = $prompt->prompt();
            $organizationName = $organizationName !== '' ? $organizationName : 'Awesome PHP LLC';

            /** @var Input $prompt */
            $prompt = $this->cli->input('Organization Name [G Unit]: ');
            $organizationalUnitName = $prompt->prompt();
            $organizationalUnitName = $organizationalUnitName !== '' ? $organizationalUnitName : 'G Unit';

            /** @var Input $prompt */
            $prompt = $this->cli->input('Common Name []: ');
            $commonName = '';

            while ($commonName === '') {
                $commonName = $prompt->prompt();
            }

            /** @var Input $prompt */
            $prompt = $this->cli->input('Email Address []: ');
            $emailAddress = '';

            while ($emailAddress === '') {
                $emailAddress = $prompt->prompt();
            }

            $csr = openssl_csr_new([
                'countryName' => $countryName,
                'stateOrProvinceName' => $stateOrProvinceName,
                'localityName' => $localityName,
                'organizationName' => $organizationName,
                'organizationalUnitName' => $organizationalUnitName,
                'commonName' => $commonName,
                'emailAddress' => $emailAddress
            ], $privateKey, ['digest_alg' => 'sha256']);

            $x509 = openssl_csr_sign($csr, null, $privateKey, 365, ['digest_alg' => 'sha256']);
            openssl_x509_export($x509, $cert);
        }

        $encryptionKey = base64_encode(random_bytes(32));

        if ($this->cli->arguments->get('writeFile')) {
            $file = fopen(Core::di()->config->get('run_dir') . '/authorization.key', 'wb');

            fwrite($file, $pubKey);
            fwrite($file, $privateKey);

            if ($cert !== null) {
                fwrite($file, $cert);
            }

            fclose($file);

            $configFile = Core::di()->config->get('run_dir') . '/var/config/config.php';
            file_put_contents(
                $configFile,
                str_replace('__encryption_key__', $encryptionKey, file_get_contents($configFile))
            );

            $this->cli->info('Your key has been written. Keep this key set in a safe place');
            $this->cli->info('Your config has also been updated with your new encryption key');
        } else {
            $this->cli->yellow($privateKey);
            $this->cli->green($pubKey);

            if ($cert !== null) {
                $this->cli->green($cert);
            }

            $this->cli->blue($encryptionKey);
        }

        return 0;
    }
}
