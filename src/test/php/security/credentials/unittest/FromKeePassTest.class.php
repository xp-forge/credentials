<?php namespace security\credentials\unittest;

use lang\ClassLoader;
use security\credentials\FromKeePass;
use unittest\{Test, Values};
use util\Secret;

class FromKeePassTest extends AbstractSecretsTest {

  /** @return security.vault.Secrets */
  protected function newFixture($group= '/') {
    return new FromKeePass(
      ClassLoader::getDefault()->getResourceAsStream('keepass/unittest.kdbx'),
      new Secret('test'),
      $group
    );
  }

  #[Test, Values(['xp/app', '/xp/app', '/xp/app/'])]
  public function using_group($group) {
    $this->assertCredential($this->newFixture($group), 'test', 'mysql');
  }

  #[Test, Values(['xp/app', '/xp/app', '/xp/app/'])]
  public function all_in_group($group) {
    $this->assertCredentials($this->newFixture($group), ['mysql' => 'test'], '*');
  }
}