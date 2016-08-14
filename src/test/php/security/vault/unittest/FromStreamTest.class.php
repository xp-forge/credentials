<?php namespace security\vault\unittest;

use security\vault\FromStream;
use util\Secret;
use io\File;
use io\streams\MemoryInputStream;
use io\streams\TextReader;

class FromStreamTest extends AbstractSecretsTest {

  /** @return security.vault.Secrets */
  protected function newFixture() {
    return new FromStream(new MemoryInputStream(
      "TEST_DB_PASSWORD=db\n".
      "TEST_LDAP_PASSWORD=ldap\n".
      "PROD_MASTER_KEY=master"
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