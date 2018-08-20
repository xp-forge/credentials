Credentials change log
======================

## ?.?.? / ????-??-??

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