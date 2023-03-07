Credentials change log
======================

## ?.?.? / ????-??-??

* Merged PR #11: Migrate to new testing library - @thekid

## 2.1.0 / 2022-02-26

* Merged PR #10: Throw exceptions if vault is down - @thekid

## 2.0.4 / 2022-02-26

* Fixed "Creation of dynamic property" warnings in PHP 8.2 - @thekid
* Made library compatible with `xp-forge/rest-client` version 5.0.0
  (@thekid)

## 2.0.3 / 2021-12-03

* Made library compatible with `xp-forge/rest-client` version 4.0.0
  (@thekid)

## 2.0.2 / 2021-10-21

* Made library compatible with XP 11, `xp-forge/rest-client` version 3.0.0
  (@thekid)

## 2.0.1 / 2020-04-10

* Made compatible with `xp-forge/rest-client` 2.0.0 - @thekid

## 2.0.0 / 2020-04-05

* Implemented xp-framework/rfc#335: Remove deprecated key/value pair
  annotation syntax
  (@thekid)
* Implemented xp-framework/rfc#334: Drop PHP 5.6. The minimum required
  PHP version is now 7.0.0!
  (@thekid)

## 1.0.1 / 2019-11-30

* Made compatible with XP 10 - @thekid

## 1.0.0 / 2019-01-26

* **Heads up:** Dropped support for hierarchical credentials using forward
  slashes. Credential groups in KeePass databases and in Vault can be
  accessed by passing the groups' names to their respective constructors.
* Require xp-forge/rest-client `^1.0` - @thekid

## 0.8.5 / 2019-01-22

* Added compatibility with xp-forge/rest-client 0.8.0 - @thekid
* Added PHP 7.3 compatibility - @thekid

## 0.8.4 / 2018-11-05

* Added compatibility with xp-forge/rest-client 0.7.0 - @thekid

## 0.8.3 / 2018-11-04

* Added compatibility with xp-forge/rest-client 0.6.0 - @thekid

## 0.8.2 / 2018-11-02

* Added compatibility with xp-forge/rest-client 0.5.0 - @thekid

## 0.8.1 / 2018-09-17

* Fixed *Call to undefined method webservices\rest\Endpoint::with()*
  when using `FromVault` with a token
  (@thekid)

## 0.8.0 / 2018-09-07

* Merged PR #9: Migrate to use xp-forge/rest-client API - @thekid

## 0.7.1 / 2018-08-25

* Made library compatible with `xp-framework/rest` 10.0.0 - @thekid

## 0.7.0 / 2018-08-20

* Made `pattern` parameter to `Credentials::all()` optional, defaulting
  to *list all credentials*
  (@thekid)
* Fixed `all()` not opening credentials stores correctly - @thekid

## 0.6.0 / 2018-08-13

* **Heads up:** Implementations of the `security.secret.Secrets` interface
  must return *$this* from `open()` now instead of not returning anything
  (@thekid)
* Merged PR #7: Add `Credentials::expanding()` to add expansion in
  property files
  (@thekid)
* Merged PR #6: Lazily open secret stores - @thekid

## 0.5.0 / 2018-06-08

* Merged PR #5: Byte escape sequences - @thekid

## 0.4.1 / 2018-01-12

* Ignored trailing newlines in docker secret files - @thekid

## 0.4.0 / 2018-01-12

* Implemented PR #3: Implement support for Docker Secrets - @thekid

## 0.3.0 / 2017-10-14

* Added XP9 compatibility - @thekid

## 0.2.0 / 2016-09-29

* Update to KeePass v0.5.0 - restores HHVM compatibility - @thekid

## 0.1.0 / 2016-09-29

* Hello World! First release - @thekid