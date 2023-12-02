<?php namespace security\credentials\unittest;

use io\streams\{MemoryInputStream, Streams};
use io\{File, TempFile};
use security\credentials\FromFile;
use test\{Assert, Test, Values};

class FromFileTest extends AbstractSecretsTest {

  /** @return security.vault.Secrets */
  protected function newFixture($name) {
    return new FromFile(Streams::readableFd(new MemoryInputStream(
      "TEST_DB_PASSWORD=db\n".
      "TEST_LDAP_PASSWORD=ldap\n".
      "PROD_MASTER_KEY=master\n".
      "CLOUD_SECRET=S\\xa7T"
    )));
  }

  /** @return iterable */
  private function files() {
    yield [new File('filename'), 'files'];
    yield ['filename', 'filenames'];
  }

  #[Test, Values(from: 'files')]
  public function can_create($arg, $from) {
    new FromFile($arg);
  }

  #[Test]
  public function file_kept_by_default() {
    $file= new TempFile('secrets');
    $fixture= new FromFile($file);
    $fixture->open();
    $fixture->close();
    Assert::true($file->exists());
  }

  #[Test]
  public function can_optionally_be_removed_after_close() {
    $file= new TempFile('secrets');
    $fixture= new FromFile($file, FromFile::REMOVE);
    $fixture->open();
    $fixture->close();
    Assert::false($file->exists());
  }

  #[Test]
  public function byte_escape_sequence() {
    $this->assertCredential($this->newFixture(__FUNCTION__), "S\xa7T", 'cloud_secret');
  }
}