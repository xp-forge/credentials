<?php namespace security\credentials\unittest;

use test\{Assert, Test, Values};

abstract class AbstractSecretsTest {

  /** @return security.vaults.Secrets */
  protected abstract function newFixture($name);

  /**
   * Assertion helper
   *
   * @param  security.valuts.Secrets $fixture
   * @param  string $expected
   * @param  string $name
   * @return void
   * @throws unittest.AssertionFailedError
   */
  protected function assertCredential($fixture, $expected, $name) {
    $fixture->open();
    try {
      Assert::equals($expected, $fixture->named($name)->reveal());
    } finally {
      $fixture->close();
    }
  }

  /**
   * Assertion helper
   *
   * @param  security.valuts.Secrets $fixture
   * @param  [:string] $expected
   * @param  string $pattern
   * @return void
   * @throws unittest.AssertionFailedError
   */
  protected function assertCredentials($fixture, $expected, $pattern) {
    $fixture->open();
    try {
      Assert::equals($expected, array_map(
        function($s) { return $s->reveal(); },
        iterator_to_array($fixture->all($pattern))
      ));
    } finally {
      $fixture->close();
    }
  }

  #[Test]
  public function open_and_close_can_be_called_twice() {
    $fixture= $this->newFixture(__FUNCTION__);
    $fixture->open();
    $fixture->open();

    $fixture->close();
    $fixture->close();
  }

  #[Test, Values([['test_db_password', 'db'], ['test_ldap_password', 'ldap'], ['prod_master_key', 'master']])]
  public function credential($name, $result) {
    $this->assertCredential($this->newFixture(__FUNCTION__), $result, $name);
  }

  #[Test, Values([['test_*', ['test_db_password' => 'db', 'test_ldap_password' => 'ldap']], ['prod_*', ['prod_master_key' => 'master']], ['non_existant_*', []]])]
  public function credentials($filter, $result) {
    $this->assertCredentials($this->newFixture(__FUNCTION__), $result, $filter);
  }

  #[Test]
  public function non_existant_credential() {
    $fixture= $this->newFixture(__FUNCTION__);
    $fixture->open();
    try {
      Assert::null($fixture->named('non_existant_value'));
    } finally {
      $fixture->close();
    }
  }
}