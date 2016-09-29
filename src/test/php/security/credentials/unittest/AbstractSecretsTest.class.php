<?php namespace security\credentials\unittest;

abstract class AbstractSecretsTest extends \unittest\TestCase {

  /** @return security.vaults.Secrets */
  protected abstract function newFixture();

  #[@test]
  public function credential() {
    $fixture= $this->newFixture();
    $fixture->open();
    try {
      $this->assertEquals('db', $fixture->named('test_db_password')->reveal());
    } finally {
      $fixture->close();
    }
  }

  #[@test]
  public function non_existant_credential() {
    $fixture= $this->newFixture();
    $fixture->open();
    try {
      $this->assertNull($fixture->named('non_existant_value'));
    } finally {
      $fixture->close();
    }
  }

  #[@test, @values([
  #  ['test_*', ['test_db_password' => 'db', 'test_ldap_password' => 'ldap']],
  #  ['prod_*', ['prod_master_key' => 'master']],
  #  ['non_existant_*', []]
  #])]
  public function credentials($filter, $result) {
    $fixture= $this->newFixture();
    $fixture->open();
    try {
      $this->assertEquals($result, array_map(
        function($s) { return $s->reveal(); },
        iterator_to_array($fixture->all($filter))
      ));
    } finally {
      $fixture->close();
    }
  }
}