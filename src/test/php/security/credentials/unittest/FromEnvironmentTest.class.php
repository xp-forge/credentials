<?php namespace security\credentials\unittest;

use security\credentials\FromEnvironment;
use util\Secret;
use lang\Environment;

class FromEnvironmentTest extends AbstractSecretsTest {

  /** @return security.vault.Secrets */
  protected function newFixture() { return new FromEnvironment(); }

  /** @return void */
  public function setUp() {
    Environment::export(['TEST_DB_PASSWORD' => 'db', 'TEST_LDAP_PASSWORD' => 'ldap', 'PROD_MASTER_KEY' => 'master']);
  }

  #[@test]
  public function can_create() {
    new FromEnvironment();
  }

  #[@test]
  public function removed_after_use() {
    $before= Environment::variable('TEST_DB_PASSWORD', null);

    with (new FromEnvironment(FromEnvironment::REMOVE), function($fixture) {
      $fixture->open();
      $fixture->named('test_db_password');
      $fixture->close();
    });

    $after= Environment::variable('TEST_DB_PASSWORD', null);
    $this->assertEquals(['db', null], [$before, $after]);
  }

  #[@test]
  public function forward_slashes_are_replaced_by_double_underscores_in_named() {
    Environment::export(['XP__APP__MYSQL' => 'test']);
    try {
      $this->assertCredential('test', 'xp/app/mysql');
    } finally {
      Environment::export(['XP__APP__MYSQL' => null]);
    }
  }

  #[@test]
  public function forward_slashes_are_replaced_by_double_underscores_in_all() {
    Environment::export(['XP__APP__MYSQL' => 'test']);
    try {
      $this->assertEquals('test', iterator_to_array((new FromEnvironment())->all('xp/app/*'))['xp/app/mysql']->reveal());
    } finally {
      Environment::export(['XP__APP__MYSQL' => null]);
    }
  }
}