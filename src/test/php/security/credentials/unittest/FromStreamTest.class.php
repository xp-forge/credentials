<?php namespace security\credentials\unittest;

use security\credentials\FromStream;
use util\Secret;
use io\File;
use io\streams\MemoryInputStream;
use io\streams\TextReader;

class FromStreamTest extends AbstractSecretsTest {

  /** @return security.vault.Secrets */
  protected function newFixture() {
    return new FromStream(new MemoryInputStream(
      "test_db_password=db\n".
      "test_ldap_password=ldap\n".
      "prod_master_key=master\n".
      "xp/app/mysql=test"
    ));
  }

  #[@test, @values([
  #  [new TextReader(new MemoryInputStream("")), "readers"],
  #  [new MemoryInputStream(""), "streams"],
  #  [new File("filename"), "files"],
  #  ["filename", "filenames"]
  #])]
  public function can_create($arg, $from) {
    new FromStream($arg);
  }
}