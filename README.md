# org_heigl/password

An Object for passwords - Stop leaking passwords to logs or stacktraces!

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


