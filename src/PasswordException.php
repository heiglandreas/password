<?php

declare(strict_types=1);

/*
 * Copyright (c) Andreas Heigl<andreas@heigl.org> All rights reserved.
 *
 * Licensed under the MIT License. See LICENSE.md file in the
 * project root for full license information.
 */

namespace Org_Heigl\Password;

use Exception;

class PasswordException extends Exception
{
    public static function getWakeupException() : self
    {
        return new self('This object can not be deserialized');
    }

    public static function getSleepException() : self
    {
        return new self('This object can not be serialized');
    }

    public static function getCloneException() : self
    {
        return new self('This object can not be cloned');
    }
}
