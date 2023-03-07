<?php namespace security\credentials\unittest;

use lang\ClassLoader;
use security\credentials\FromKeePass;
use test\Assert;
use test\{Test, Values};
use util\Secret;

class FromKeePassTest extends AbstractSecretsTest {

  /** @return security.vault.Secrets */
  protected function newFixture($name, $group= '/') {
    return new FromKeePass(
      ClassLoader::getDefault()->getResourceAsStream('keepass/unittest.kdbx'),
      new Secret('test'),
      $group
    );
  }

  #[Test, Values(['xp/app', '/xp/app', '/xp/app/'])]
  public function using_group($group) {
    $this->assertCredential($this->newFixture(__FUNCTION__, $group), 'test', 'mysql');
  }

  #[Test, Values(['xp/app', '/xp/app', '/xp/app/'])]
  public function all_in_group($group) {
    $this->assertCredentials($this->newFixture(__FUNCTION__, $group), ['mysql' => 'test'], '*');
  }
}