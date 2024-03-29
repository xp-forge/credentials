<?php namespace security\credentials\unittest;

use lang\Environment;
use security\credentials\FromEnvironment;
use test\{Assert, Test};
use util\Secret;

class FromEnvironmentTest extends AbstractSecretsTest {

  /** @return security.vault.Secrets */
  protected function newFixture($name) {
    Environment::export([
      'TEST_DB_PASSWORD'   => 'db',
      'TEST_LDAP_PASSWORD' => 'ldap',
      'PROD_MASTER_KEY'    => 'master',
    ]);
    return new FromEnvironment();
  }

  #[Test]
  public function can_create() {
    new FromEnvironment();
  }

  #[Test]
  public function removed_after_use() {
    Environment::export(['TEST_DB_PASSWORD' => 'db']);
    $before= Environment::variable('TEST_DB_PASSWORD', null);

    with (new FromEnvironment(FromEnvironment::REMOVE), function($fixture) {
      $fixture->open();
      $fixture->named('test_db_password');
      $fixture->close();
    });

    $after= Environment::variable('TEST_DB_PASSWORD', null);
    Assert::equals(['db', null], [$before, $after]);
  }
}