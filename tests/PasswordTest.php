<?php
/**
 * Copyright (c) Andreas Heigl<andreas@heigl.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Andreas Heigl<andreas@heigl.org>
 * @copyright Andreas Heigl
 * @license   http://www.opensource.org/licenses/mit-license.php MIT-License
 * @since     06.05.2018
 * @link      http://github.com/heiglandreas/password
 */

namespace Org_Heigl\PasswordTest;

use Closure;
use Org_Heigl\Password\Password;
use Org_Heigl\Password\PasswordException;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class PasswordTest extends TestCase
{

    public function testShouldBeRehashed()
    {
        $password = Password::createFromPlainText('test');

        self::assertTrue($password->shouldBeRehashed());
    }

    public function testToString()
    {
        $password = Password::createFromPlainText('test');

        self::assertSame('********', (string) $password);
    }

    public function testGetPlainTextPasswordAndYesIKnowWhatIAmDoingHere()
    {
        $password = Password::createFromPlainText('test');

        self::assertSame('test', @$password->getPlainTextPasswordAndYesIKnowWhatIAmDoingHere());
    }

    public function testGetPlainTextPasswordAndYesIKnowWhatIAmDoingHereTriggersWarning()
    {
        $password = Password::createFromPlainText('test');

        $this->expectException(Warning::class);
        $password->getPlainTextPasswordAndYesIKnowWhatIAmDoingHere();
    }

    public function testMatchesHash()
    {
        $password = Password::createFromPlainText('test');

        self::assertFalse($password->matchesHash(password_hash('test1', PASSWORD_DEFAULT)));
        self::assertTrue($password->matchesHash(password_hash('test', PASSWORD_DEFAULT)));
    }

    public function testCreateFromPlainText()
    {
        $password = Password::createFromPlainText('test');

        $encrypted = sodium_crypto_secretbox(
            'test',
            ORG_HEIGL_PASSWORD_PASSWORD_NONCE,
            ORG_HEIGL_PASSWORD_PASSWORD_KEY
        );

        self::assertAttributeSame($encrypted, 'password', $password);
        self::assertAttributeSame(null, 'hash', $password);
    }

    public function testGetNewHash()
    {
        $password = Password::createFromPlainText('test');
        self::assertTrue(password_verify('test', $password->getNewHash()));
    }

    public function testDoesNotLeakPasswordThroughPrintR()
    {
        $password = Password::createFromPlainText('testPassword');

        self::assertNotContains('testPassword', print_r($password, true));
    }

    public function testDoesNotLeakPasswordThroughVarDump()
    {
        $password = Password::createFromPlainText('testPassword');

        ob_start();
        var_dump($password);
        $output = ob_get_clean();

        self::assertNotContains('testPassword', $output);
    }

    public function testDoesNotLeakPasswordThroughArrayConversion()
    {
        $password = Password::createFromPlainText('testPassword');

        $output = (array) $password;

        self::assertNotContains('testPassword', $output);
    }

    public function testDoesNotLeakPasswordThroughClosureBinding()
    {
        $password = Password::createFromPlainText('testPassword');

        $keylogger = function (Password $password) {
            return $password->password;
        };

        $keylogger = Closure::bind($keylogger, null, $password);

        self::assertNotEquals('testPassword', $keylogger($password));
    }

    public function testDoesNotLeakPasswordThroughReflection()
    {
        $password = Password::createFromPlainText('testPassword');

        $keylogger = new ReflectionProperty(Password::class, 'password');

        $keylogger->setAccessible(true);

        self::assertNotEquals('testPassword', $keylogger->getValue($password));
    }

    public function testJsonSerializeDoesntLeak()
    {
        $password = Password::createFromPlainText('testPassword');

        self::assertEquals('{}', json_encode($password));
    }

    public function testSerializingThowsUp()
    {
        self::expectException(PasswordException::class);
        self::expectExceptionMessage('This object can not be serialized');

        $password = Password::createFromPlainText('testPassword');
        serialize($password);
    }

    public function testDeserializingThowsUp()
    {
        self::expectException(PasswordException::class);
        self::expectExceptionMessage('This object can not be deserialized');

        unserialize('O:27:"Org_Heigl\Password\Password":0:{}');
    }

    public function testCloningThrowsUp()
    {
        self::expectException(PasswordException::class);
        self::expectExceptionMessage('This object can not be cloned');

        clone Password::createFromPlainText('testPassword');
    }

    public function testShouldBeReallyRehashed()
    {
        $password = Password::createFromPlainText('test');

        $password->matchesHash($password->matchesHash(password_hash('test', PASSWORD_DEFAULT)));

        self::assertTrue($password->shouldBeRehashed(PASSWORD_DEFAULT, ['cost' => 11]));
    }
}
