<?php namespace security\credentials\unittest;

use lang\ElementNotFoundException;
use lang\IllegalArgumentException;
use security\credentials\Credentials;
use security\credentials\Secrets;
use util\Secret;

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
      'open'  => function() { return $this; },
      'named' => function($name) use($secret) { return $secret; },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));

    $this->assertEquals($secret, $credentials->named('test'));
  }

  #[@test, @values(['test', '*'])]
  public function credentials($pattern) {
    $secret= new Secret('test');
    $credentials= new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() { return $this; },
      'named' => function($name) { },
      'all'   => function($pattern) use($secret) { yield 'test' => $secret; },
      'close' => function() { }
    ]));

    $this->assertEquals(['test' => $secret], iterator_to_array($credentials->all($pattern)));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function non_existant_credential() {
    $credentials= new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() { return $this; },
      'named' => function($name) { return null; },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));

    $credentials->named('test');
  }

  #[@test]
  public function explicit_open() {
    $opened= false;
    $credentials= new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() use(&$opened) { $opened= true; return $this; },
      'named' => function($name) { return null; },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));

    $credentials->open();
    $this->assertEquals(true, $opened);
  }

  #[@test]
  public function secrets_not_opened_until_actually_needed() {
    $secret= new Secret('test');
    $credentials= new Credentials(
      newinstance(Secrets::class, [], [
        'open'  => function() { return $this; },
        'named' => function($name) use($secret) { return $secret; },
        'all'   => function($pattern) { },
        'close' => function() { }
      ]),
      newinstance(Secrets::class, [], [
        'open'  => function() { throw new IllegalArgumentException('Cannot access'); },
        'named' => function($name) { },
        'all'   => function($pattern) { },
        'close' => function() { }
      ])
    );

    $this->assertEquals($secret, $credentials->named('test'));
  }

  #[@test]
  public function secrets_opened_during_all() {
    $credentials= new Credentials(
      newinstance(Secrets::class, [], [
        'open'  => function() use(&$opened) { $opened[]= 'a'; return $this; },
        'named' => function($name) { },
        'all'   => function($pattern) { yield 'a' => new Secret('a'); },
        'close' => function() { }
      ]),
      newinstance(Secrets::class, [], [
        'open'  => function() use(&$opened) { $opened[]= 'b'; return $this; },
        'named' => function($name) { },
        'all'   => function($pattern) { yield 'b' => new Secret('b'); },
        'close' => function() { }
      ])
    );

    $opened= [];
    iterator_count($credentials->all('*'));
    $this->assertEquals(['a', 'b'], $opened);
  }
}