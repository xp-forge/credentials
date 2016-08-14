<?php namespace security\vault\unittest;

use security\vault\FromEnvironment;
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
}