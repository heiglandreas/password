# org_heigl/password

An Object for passwords - Stop leaking passwords to logs or stacktraces!

## Why?

The discussions that spun up around twitter leaking passwords to logfiles left me thinking.

It can actually happen quite easily to have passwords come up into log-files when
you put stack-traces into logs. And that brought me to thinking how to avoid that
accidentally. The answer to me is a vaule-object with a bit of logic that handles the password but
won't accidentaly leak it.

## How

The password is stored in an encrypted way using a nonce and a key that will be
shared throughout one Request. So multiple calls to `Password::createFromPlainText()`
within one request will use the same values for nonce and key respectively, different
request will have different values.

As those values are not stored within the object but as global constants they can not leak
via reflection of closure::bind or whatever nice ways there are to get private properties.

As the goal of this Object is not to store the password in a secure way (you will
use a hashing algorithm for that, won't you?) but to prohibit it from accidentally
leaking in cleartext that is a compromise I'm willing to take.

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

Verifying the password is done right within the Object like this:

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


