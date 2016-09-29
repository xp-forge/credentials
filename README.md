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

Example
-------

```php
use security\credentials\{Credentials, FromEnvironment, FromVault};

$credentials= new Credentials(new FromEnvironment());
$secret= $credentials->named('ldap_password');   // Reads $ENV{LDAP_PASSWORD} => util.Secret

$credentials= new Credentials(new FromVault('http://127.0.0.1:8200', '72698676-4988-94a4-...'));
$secret= $credentials->named('ldap_password');   // Reads ldap_password from /secret
```

Backends
--------
This API supports multiple backends:

* **Environment variables** via the `FromEnvironment` class.
* **Files**  via the `FromFile` class.
* **Hashicorp's Vault** via the `FromVault` class.

See also
--------
https://github.com/xp-framework/rfc/issues/316