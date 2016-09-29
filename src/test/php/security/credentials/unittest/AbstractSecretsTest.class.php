<?php namespace security\credentials\unittest;

abstract class AbstractSecretsTest extends \unittest\TestCase {

  /** @return security.vaults.Secrets */
  protected abstract function newFixture();

  /**
   * Assertion helper
   *
   * @param  string $expected
   * @param  string $name
   * @return void
   * @throws unittest.AssertionFailedError
   */
  protected function assertCredential($expected, $name) {
    $fixture= $this->newFixture();
    $fixture->open();
    try {
      $this->assertEquals($expected, $fixture->named($name)->reveal());
    } finally {
      $fixture->close();
    }
  }

  /**
   * Assertion helper
   *
   * @param  [:string] $expected
   * @param  string $pattern
   * @return void
   * @throws unittest.AssertionFailedError
   */
  protected function assertCredentials($expected, $pattern) {
    $fixture= $this->newFixture();
    $fixture->open();
    try {
      $this->assertEquals($expected, array_map(
        function($s) { return $s->reveal(); },
        iterator_to_array($fixture->all($pattern))
      ));
    } finally {
      $fixture->close();
    }
  }


  #[@test, @values([
  #  ['test_db_password', 'db'],
  #  ['test_ldap_password', 'ldap'],
  #  ['prod_master_key', 'master']
  #])]
  public function credential($name, $result) {
    $this->assertCredential($result, $name);
  }

  #[@test, @values([
  #  ['test_*', ['test_db_password' => 'db', 'test_ldap_password' => 'ldap']],
  #  ['prod_*', ['prod_master_key' => 'master']],
  #  ['non_existant_*', []]
  #])]
  public function credentials($filter, $result) {
    $this->assertCredentials($result, $filter);
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
}