<?php namespace security\vault\unittest;

use security\vault\Vault;
use security\vault\Secrets;
use util\Secret;
use lang\IllegalArgumentException;
use lang\ElementNotFoundException;

class VaultTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new Vault(newinstance(Secrets::class, [], [
      'open'  => function() { },
      'named' => function($name) { },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_create_without_secrets() {
    new Vault();
  }

  #[@test]
  public function credential() {
    $secret= new Secret('test');
    $vault= new Vault(newinstance(Secrets::class, [], [
      'open'  => function() { },
      'named' => function($name) use($secret) { return $secret; },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));

    $this->assertEquals($secret, $vault->credential('test'));
  }

  #[@test]
  public function credentials() {
    $secret= new Secret('test');
    $vault= new Vault(newinstance(Secrets::class, [], [
      'open'  => function() { },
      'named' => function($name) { },
      'all'   => function($pattern) use($secret) { yield 'test' => $secret; },
      'close' => function() { }
    ]));

    $this->assertEquals(['test' => $secret], iterator_to_array($vault->credentials('test')));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function non_existant_credential() {
    $vault= new Vault(newinstance(Secrets::class, [], [
      'open'  => function() { },
      'named' => function($name) { return null; },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));

    $vault->credential('test');
  }
}