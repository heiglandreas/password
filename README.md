# org_heigl/password

An Object for passwords - Stop leaking passwords to logs or stacktraces!

## Scope

This package contains an Object that can be used and passed just like you would
use a plaintext-password. The only difference is that the plaintext-password will not
be accidentaly leaked into log-files or stacktraces or var_Dump-output.

The scope is **not** to provide a Cryptographically Secure Password or a ValueObject
that you can just pass to your Persistence-Layer for storage. On the contrary. **You shall
never store this Object**

This is only a thin wrapper around your password-string that tries to guard you from
accidentally leaking the password string where you don't want to see it.

The object stores the password encrypted using `sodium_crypto_secretbox`. So should one
find a way to expose the private property to the public there will only be an encrypted
binary code. The nonce and the key to encrypt and decrypt are stored in constants and
will be replaced on every request. So when you create two Password-objects within one
request they will both use the same nonce and key. As those value are stored as constants
they will not leak by accident. You will have to actively address them. Preventing **that**
is outside the scope of this package!

As the goal of this Object is not to store the password in a secure way (you will
use a hashing algorithm for that, won't you?) but to prohibit it from accidentally
leaking in cleartext that is a compromise I'm willing to take.

## Why?

The discussions that spun up around twitter leaking passwords to logfiles left me thinking.

It can actually happen quite easily to have passwords come up into log-files when
you put stack-traces into logs. And that brought me to thinking how to avoid that
accidentally. The answer to me is a vaule-object with a bit of logic that handles the password but
won't accidentaly leak it.

## Installation

This is best installed using composer like this:

```bash
composer require org_heigl/password
```

## Usage

Instead of passing the password as a string create a Password-Object and pass that.

```php
$password = Password::createFromPlainText($request->getParam('password'));
```

You can additionally directly use PHPs password-hashing API:

```php
$password->matchesHash($hashFromPhpPasswordHashingApi);
```

Additionally you can get a new hash for the password like this:

```php
$hash = $password->getNewHash();
```

And to wrap up the API of PHPs password-hashing API there's also a method to check
whether the password should be rehashed

```php
$password->shouldBeRehashed();
```

If you **really** need to get the plaintext password the password-object was initialized with
(f.e. for use with ```ldap_bind```) you can do that as well:

```php
$plaintextPassword = $password->getPlainTextPasswordAndYesIKnowWhatIAmDoingHere();
```


