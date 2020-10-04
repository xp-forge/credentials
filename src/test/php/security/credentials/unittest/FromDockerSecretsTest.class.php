<?php namespace security\credentials\unittest;

use io\{Folder, Path};
use lang\Environment;
use security\credentials\FromDockerSecrets;
use unittest\Test;
use util\Secret;

class FromDockerSecretsTest extends AbstractSecretsTest {

  /** @return security.credentials.Secrets */
  protected function newFixture() {
    return new FromDockerSecrets($this->path);
  }

  /** @return void */
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

  /** @return void */
  public function tearDown() {
    $this->path->exists() && $this->path->unlink();
  }

  #[Test]
  public function path() {
    $path= new Path('.');
    $this->assertEquals($path, (new FromDockerSecrets($path))->path());
  }

  #[Test]
  public function string_path() {
    $this->assertEquals(new Path('.'), (new FromDockerSecrets('.'))->path());
  }

  #[Test]
  public function folder_path() {
    $folder= new Folder('.');
    $this->assertEquals(new Path($folder->getURI()), (new FromDockerSecrets($folder))->path());
  }

  #[Test]
  public function default_path() {
    $this->assertNotEquals(null, (new FromDockerSecrets())->path());
  }
}