<?php namespace security\credentials\unittest;

use security\credentials\FromKeePass;
use util\Secret;
use lang\ClassLoader;

class FromKeePassTest extends AbstractSecretsTest {

  /** @return security.vault.Secrets */
  protected function newFixture() {
    return new FromKeePass(
      ClassLoader::getDefault()->getResourceAsStream('keepass/unittest.kdbx'),
      new Secret('test')
    );
  }

  #[@test]
  public function from_subfolder() {
    $fixture= $this->newFixture();
    $fixture->open();
    try {
      $this->assertEquals('test', $fixture->named('xp/app/mysql')->reveal());
    } finally {
      $fixture->close();
    }
  }

  #[@test]
  public function all_in_subfolder() {
    $fixture= $this->newFixture();
    $fixture->open();
    try {
      $this->assertEquals(['xp/app/mysql' => 'test'], array_map(
        function($s) { return $s->reveal(); },
        iterator_to_array($fixture->all('xp/app/*'))
      ));
    } finally {
      $fixture->close();
    }
  }
}