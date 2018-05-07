<?php

declare(strict_types=1);

/*
 * Copyright (c) Andreas Heigl<andreas@heigl.org> All rights reserved.
 *
 * Licensed under the MIT License. See LICENSE.md file in the
 * project root for full license information.
 */

namespace Org_Heigl\Password;

class Password
{
    private $password;

    private $hash;

    private function __construct(string $password)
    {
        $this->password = $password;
        $this->hash = null;
    }

    public static function createFromPlainText(string $password) : self
    {
        return new self($password);
    }

    public function __toString()
    {
        return '********';
    }

    public function matchesHash(string $hash) : bool
    {
        $this->hash = $hash;

        return password_verify($this->password, $this->hash);
    }

    public function shouldBeRehashed(int $algorithm = PASSWORD_DEFAULT, array $options = []) : bool
    {
        if (null === $this->hash) {
            return true;
        }

        return password_needs_rehash($this->hash, $algorithm, $options);
    }

    public function getNewHash(int $algorithm = PASSWORD_DEFAULT, array $options = []) : string
    {
        return password_hash($this->password, $algorithm, $options);
    }

    public function getPlainTextPasswordAndYesIKnowWhatIAmDoingHere() : string
    {
        trigger_error(
            'Password was leaked in clear text using the "Password::getPlainTextPasswordAndYesIKnowWhatIAmDoingHere"-function!!',
            E_USER_WARNING
        );

        return $this->password;
    }
}
