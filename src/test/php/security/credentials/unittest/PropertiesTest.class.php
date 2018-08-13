<?php namespace security\credentials\unittest;

use io\streams\MemoryInputStream;
use security\credentials\Credentials;
use security\credentials\Secrets;
use unittest\TestCase;
use util\Properties;
use util\Secret;

class PropertiesTest extends TestCase {

  #[@test]
  public function expanding() {
    $secret= new Secret('Expanded!');
    $credentials= new Credentials(newinstance(Secrets::class, [], [
      'open'  => function() { },
      'named' => function($name) use($secret) { return $secret; },
      'all'   => function($pattern) { },
      'close' => function() { }
    ]));

    $prop= $credentials->expanding(new Properties());
    $prop->load(new MemoryInputStream('pass=${secret.test}'));

    $this->assertEquals($secret->reveal(), $prop->readString(null, 'pass'));
  }
}