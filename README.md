Credentials
=====

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/credentials.svg)](http://travis-ci.org/xp-forge/credentials)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.6+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_6plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Supports HHVM 3.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/hhvm-3_4plus.png)](http://hhvm.com/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/credentials/version.png)](https://packagist.org/packages/xp-forge/credentials)

Credentials storage

Backends
--------
This API supports the following backends:

* [Files](https://github.com/xp-forge/credentials#files)
* [Environment variables](https://github.com/xp-forge/credentials#environment-variables)
* [Hashicorp's Vault](https://github.com/xp-forge/credentials#hashicorps-vault) 
* [KeePass databases](https://github.com/xp-forge/credentials#keepass-databases)
* [Docker Secrets](https://github.com/xp-forge/credentials#docker-secrets)

### Files

Via the `FromFile` class. Files are expected to have the following format:

```
rest_password=abcdefg
ldap_password=qwertzu
```

### Environment variables

Via the `FromEnvironment` class. Credential names map to environment variables by uppercasing them and replacing forward slashes by two underscores:

```php
use security\credentials\{Credentials, FromEnvironment};

$credentials= new Credentials(new FromEnvironment());
$secret= $credentials->named('ldap_password');     // Reads $ENV{LDAP_PASSWORD} => util.Secret
```

### Hashicorp's Vault

Via the `FromVault` class. Credentials are read from the backend mounted at `/secret`.

```php
use security\credentials\{Credentials, FromVault};

// Set token to NULL to use VAULT_TOKEN from environment
$token= new Secret('72698676-4988-94a4-...');

$credentials= new Credentials(new FromVault('http://127.0.0.1:8200', $token));
$secret= $credentials->named('ldap_password');     // Reads ldap_password key from /secret

$credentials= new Credentials(new FromVault('http://127.0.0.1:8200', $token, 'vendor/name'));
$secret= $credentials->named('mysql');             // Reads mysql key from /secret/vendor/name
```

### KeePass databases

Via the `KeePass` class.

```php
use security\credentials\{Credentials, FromKeePass};
use util\Secret;

$secret= new Secret('key');

$credentials= new Credentials(new FromKeePass('database.kdbx', $secret));
$secret= $credentials->named('ldap_password');     // Reads top-level entry ldap_password

$credentials= new Credentials(new FromKeePass('database.kdbx', $secret, 'vendor/name'));
$secret= $credentials->named('mysql');             // Reads mysql entry in vendor/name subfolder
```

### Docker secrets

See https://docs.docker.com/engine/swarm/secrets/. Uses Docker's default locations on both Windows and Un\*x systems if constructed without argument.

```php
use security\credentials\{Credentials, FromDockerSecrets};
use util\Secret;

$credentials= new Credentials(new FromDockerSecrets());
$secret= $credentials->named('ldap_password');     // Reads top-level entry ldap_password
```

See also
--------
https://github.com/xp-framework/rfc/issues/316