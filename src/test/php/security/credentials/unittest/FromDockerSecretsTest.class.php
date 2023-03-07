<?php namespace security\credentials\unittest;

use io\{Folder, Path};
use lang\Environment;
use security\credentials\FromDockerSecrets;
use test\{Assert, After, Before, Test};
use util\Secret;

class FromDockerSecretsTest extends AbstractSecretsTest {
  private $path;

  /** @return security.credentials.Secrets */
  protected function newFixture($name) {
    return new FromDockerSecrets($this->path);
  }

  #[Before]
  public function setUp() {
    $this->path= new Folder(Environment::tempDir(), 'secrets');
    $this->path->exists() && $this->path->unlink();
    $this->path->create(0777);

    // Create fixtures
    file_put_contents(new Path($this->path, 'test_db_password'), 'db');
    file_put_contents(new Path($this->path, 'test_ldap_password'), 'ldap');
    file_put_contents(new Path($this->path, 'prod_master_key'), 'master');

    $subfolder= new Folder($this->path, 'xp/app');
    $subfolder->create(0777);
    file_put_contents(new Path($subfolder, 'mysql'), "test\n");
  }

  #[After]
  public function tearDown() {
    $this->path->exists() && $this->path->unlink();
  }

  #[Test]
  public function path() {
    $path= new Path('.');
    Assert::equals($path, (new FromDockerSecrets($path))->path());
  }

  #[Test]
  public function string_path() {
    Assert::equals(new Path('.'), (new FromDockerSecrets('.'))->path());
  }

  #[Test]
  public function folder_path() {
    $folder= new Folder('.');
    Assert::equals(new Path($folder->getURI()), (new FromDockerSecrets($folder))->path());
  }

  #[Test]
  public function default_path() {
    Assert::notEquals(null, (new FromDockerSecrets())->path());
  }
}