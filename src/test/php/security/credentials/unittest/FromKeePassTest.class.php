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
}