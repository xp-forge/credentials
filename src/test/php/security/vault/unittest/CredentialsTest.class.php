<?php namespace security\vault\unittest;

use security\vault\Credentials;
use security\vault\Secrets;
use util\Secret;
use lang\IllegalArgumentException;
use lang\ElementNotFoundException;

class CredentialsTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() { },
      'named' => function($name) { },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_create_without_secrets() {
    new Credentials();
  }

  #[@test]
  public function credential() {
    $secret= new Secret('test');
    $credentials= new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() { },
      'named' => function($name) use($secret) { return $secret; },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));

    $this->assertEquals($secret, $credentials->credential('test'));
  }

  #[@test]
  public function credentials() {
    $secret= new Secret('test');
    $credentials= new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() { },
      'named' => function($name) { },
      'all'   => function($pattern) use($secret) { yield 'test' => $secret; },
      'close' => function() { }
    ]));

    $this->assertEquals(['test' => $secret], iterator_to_array($credentials->credentials('test')));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function non_existant_credential() {
    $credentials= new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() { },
      'named' => function($name) { return null; },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));

    $credentials->credential('test');
  }
}