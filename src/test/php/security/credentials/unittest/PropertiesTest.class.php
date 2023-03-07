<?php namespace security\credentials\unittest;

use io\streams\MemoryInputStream;
use security\credentials\{Credentials, Secrets};
use test\Assert;
use test\{Test, TestCase};
use util\{Properties, Secret};

class PropertiesTest {

  #[Test]
  public function expanding() {
    $secret= new Secret('Expanded!');
    $credentials= new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() { return $this; },
      'named' => function($name) use($secret) { return $secret; },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));

    $prop= $credentials->expanding(new Properties());
    $prop->load(new MemoryInputStream('pass=${secret.test}'));

    Assert::equals($secret->reveal(), $prop->readString(null, 'pass'));
  }
}