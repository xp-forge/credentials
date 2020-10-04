<?php namespace security\credentials\unittest;

use io\File;
use io\streams\{MemoryInputStream, TextReader};
use security\credentials\FromStream;
use unittest\{Test, Values};
use util\Secret;

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

  /** @return iterable */
  private function streams() {
    yield [new TextReader(new MemoryInputStream('')), 'readers'];
    yield [new MemoryInputStream(''), 'streams'];
    yield [new File('filename'), 'files'];
    yield ['filename', 'filenames'];
  }

  #[Test, Values('streams')]
  public function can_create($arg, $from) {
    new FromStream($arg);
  }
}