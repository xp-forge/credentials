<?php namespace security\credentials\unittest;

use lang\{ElementNotFoundException, IllegalArgumentException};
use security\credentials\{Credentials, Secrets};
use unittest\{Expect, Test, Values};
use util\Secret;

class CredentialsTest extends \unittest\TestCase {

  #[Test]
  public function can_create() {
    new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() { },
      'named' => function($name) { },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_create_without_secrets() {
    new Credentials();
  }

  #[Test]
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

  #[Test, Values(['test', '*'])]
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

  #[Test, Expect(ElementNotFoundException::class)]
  public function non_existant_credential() {
    $credentials= new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() { return $this; },
      'named' => function($name) { return null; },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));

    $credentials->named('test');
  }

  #[Test]
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

  #[Test]
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

  #[Test]
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