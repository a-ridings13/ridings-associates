<?php

namespace Siteworx\Library\Utilities;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;

/**
 * Class Helpers
 *
 * @package App\Library\Utilities
 */
class Helpers
{

    /**
     * Generates a random string. Function is not for crypto use
     *
     * @param int $length
     *
     * @return string
     * @throws \Exception
     */
    public static function generateRandString(int $length = 25): string
    {
        $allowedChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxwy0123456789';
        $maxChars = \strlen($allowedChars);
        $i = 0;
        $return = '';

        while ($i < $length) {
            $i++;
            $rand = random_int(0, $maxChars);

            if (!isset($allowedChars[$rand])) {
                continue;
            }

            $return .= $allowedChars[$rand];
        }

        return $return;
    }


    /**
     * @param $array
     *
     * @return array|mixed
     */
    public static function replaceRecursiveArray($array)
    {
        if (!\is_array($array)) {
            return filter_var($array, FILTER_SANITIZE_STRING);
        }

        foreach ($array as $key => $value) {
            if (\is_array($value)) {
                $array[$key] = self::replaceRecursiveArray($value);
            } else {
                $array[$key] = filter_var($value, FILTER_SANITIZE_STRING);
            }
        }

        return $array;
    }

    /**
     * @param int $length
     * @return int
     * @throws \Exception
     */
    public static function generateRandNumber(int $length = 5): int
    {
        $allowedChars = '0123456789';
        $maxChars = \strlen($allowedChars) - 1;
        $return = '';

        while (strlen($return) < $length) {
            $rand = random_int(0, $maxChars);
            $string = (string) $return;
            $string .= $allowedChars[$rand];
            $return = (int) $string;
        }

        return $return;
    }

    /**
     * Returns a GUIDv4 string
     *
     * Fun Fact: Since there are about 7×10^22 stars in the universe,
     * and just under 2^128 possible GUIDs, then there are approximately
     * 4.86×10^15 (almost five quadrillion) GUIDs for every single star in
     * the known universe. That's allot.
     *
     * @return string
     * @throws \Exception
     */
    public static function GUIDv4(): string
    {
        $uuidFactory = new UuidFactory();
        $uuidFactory->setRandomGenerator(new SodiumRandomGenerator());
        Uuid::setFactory($uuidFactory);

        return Uuid::uuid4()->toString();
    }
}
