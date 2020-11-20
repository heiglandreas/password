<?php

declare(strict_types=1);

/*
 * Copyright (c) Andreas Heigl<andreas@heigl.org> All rights reserved.
 *
 * Licensed under the MIT License. See LICENSE.md file in the
 * project root for full license information.
 */

namespace Org_Heigl\Password;

use function define;
use function defined;
use function password_hash;
use function password_needs_rehash;
use function version_compare;
use const PASSWORD_DEFAULT;
use const PHP_VERSION;

final class Password
{
    private $password;

    private $hash;

    private function __construct(string $password)
    {
        $this->hash = null;
        $this->password = sodium_crypto_secretbox(
            $password,
            ORG_HEIGL_PASSWORD_PASSWORD_NONCE,
            ORG_HEIGL_PASSWORD_PASSWORD_KEY
        );
    }

    public static function createFromPlainText(string $password) : self
    {
        return new self($password);
    }

    public function __toString()
    {
        return '********';
    }

    public function __debugInfo()
    {
        return [
            'password' => '********'
        ];
    }

    public function matchesHash(string $hash) : bool
    {
        $this->hash = $hash;

        return password_verify(
            $this->getPasswordInPlainText(),
            $this->hash
        );
    }

    /**
     * @deprecated Use Password::needsRehash() instead
     */
    public function shouldBeRehashed($algorithm = PASSWORD_DEFAULT, array $options = []) : bool
    {
        if (null === $this->hash) {
            return true;
        }

        return password_needs_rehash($this->hash, $algorithm, $options);
    }

    public function needsRehash(string $algorithm = PASSWORD_DEFAULT, array $options = []): bool
    {
        if (null === $this->hash) {
            return true;
        }
        if (0 < version_compare(PHP_VERSION, '7.4.0')) {
            $algorithm = (int) $algorithm;
        }
        return password_needs_rehash($this->hash, $algorithm, $options);
    }

    /**
     * @deprecated Use Password::hash() instead
     */
    public function getNewHash($algorithm = PASSWORD_DEFAULT, array $options = []) : string
    {
        return password_hash($this->getPasswordInPlainText(), $algorithm, $options);
    }

    public function hash(string $algorithm = PASSWORD_DEFAULT, array $options = []): string
    {
        if (0 < version_compare(PHP_VERSION, '7.4.0')) {
            $algorithm = (int) $algorithm;
        }
        return password_hash($this->getPasswordInPlainText(), $algorithm, $options);
    }

    public function getPlainTextPasswordAndYesIKnowWhatIAmDoingHere() : string
    {
        trigger_error(
            'Password was leaked in clear text using the ' .
            '"Password::getPlainTextPasswordAndYesIKnowWhatIAmDoingHere"-function!!',
            E_USER_WARNING
        );

        return $this->getPasswordInPlainText();
    }

    private function getPasswordInPlainText() : string
    {
        return sodium_crypto_secretbox_open(
            $this->password,
            ORG_HEIGL_PASSWORD_PASSWORD_NONCE,
            ORG_HEIGL_PASSWORD_PASSWORD_KEY
        );
    }

    /**
     * @throws \Org_Heigl\Password\PasswordException
     */
    public function __wakeup() : void
    {
        throw PasswordException::getWakeupException();
    }

    /**
     * @throws \Org_Heigl\Password\PasswordException
     */
    public function __sleep() : array
    {
        throw PasswordException::getSleepException();
    }

    /**
     * @throws \Org_Heigl\Password\PasswordException
     */
    public function __clone()
    {
        throw PasswordException::getCloneException();
    }
}

define('ORG_HEIGL_PASSWORD_PASSWORD_NONCE', random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES));
define('ORG_HEIGL_PASSWORD_PASSWORD_KEY', sodium_crypto_secretbox_keygen());
