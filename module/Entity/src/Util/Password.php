<?php

namespace Entity\Util;

/**
 * Hashing and verifying of passwords.
 * The hashing utility is a replacement for the bcrypt functions provided in zend framework 2.
 * We've also added basic checking for password validity.
 * @package Entity\Util
 */
class Password
{
    /**
     * This is the default cost for generating hashes. Should be neither too low nor too high.
     * @see http://timoh6.github.io/2013/11/26/Aggressive-password-stretching.html
     */
    const BCRYPT_COST = 10;

    /**
     * Password reset tokens last for this many days.
     */
    const RESET_KEY_VALIDITY_DAYS = 7;

    /**
     * Universal rules for validating passwords.
     * Password must be at least 8 chars and contain one number.
     * 72 chars upper bound is set by bcrypt.
     *
     * @param $password
     *
     * @return bool
     */
    public static function isValidPassword($password)
    {
        $len = strlen($password);
        $password = trim($password);
        $trimLen = strlen($password);

        $valid = ($len > 7 && $len < 73 && $len == $trimLen);
        if ($valid) {
            # test for at least one number
            return (bool) preg_match('/[\d]/', $password);
        }

        return false;
    }

    /**
     * Hash a password.
     * If it's already a bcrypt hashed string then nothing is changed.
     *
     * @param string $password
     * @param int $cost
     *
     * @return int
     */
    public static function bcrypt($password, $cost = self::BCRYPT_COST)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);

        return $hash;
    }

    /**
     * Verify a password.
     *
     * @param $password
     * @param $hash
     *
     * @return bool
     */
    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Test a string to see if it's a bcrypt hash
     *
     * @param $value
     *
     * @return bool
     */
    public static function isBcryptHash($value)
    {
        $info = password_get_info($value);

        return (isset($info['algo']) && $info['algo'] == PASSWORD_BCRYPT);
    }

    /**
     * Create a url friendly token that can be used for a password reset.
     * Also appends a token expiry timestamp
     * @return string
     */
    public static function generateResetKey()
    {
        $endDate = new \DateTime();
        $endDate->add(new \DateInterval('P' . self::RESET_KEY_VALIDITY_DAYS . 'D'));
        $key = base64_encode(sha1(mt_rand(), true));
        $key = strtr($key, '+/=', '123') . '-' . $endDate->getTimestamp();

        return $key;
    }
}
