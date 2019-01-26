<?php namespace security\credentials\unittest;

use lang\ClassLoader;
use security\credentials\FromKeePass;
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

  #[@test, @values(['xp/app', '/xp/app', '/xp/app/'])]
  public function using_group($group) {
    $this->assertCredential($this->newFixture($group), 'test', 'mysql');
  }

  #[@test, @values(['xp/app', '/xp/app', '/xp/app/'])]
  public function all_in_group($group) {
    $this->assertCredentials($this->newFixture($group), ['mysql' => 'test'], '*');
  }
}