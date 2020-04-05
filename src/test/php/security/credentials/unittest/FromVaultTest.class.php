<?php namespace security\credentials\unittest;

use io\streams\MemoryInputStream;
use lang\FormatException;
use peer\URL;
use peer\http\HttpResponse;
use security\credentials\FromVault;
use util\Secret;
use webservices\rest\Endpoint;
use webservices\rest\RestRequest;
use webservices\rest\RestResponse;

class FromVaultTest extends AbstractSecretsTest {

  // Mapping test name => array of answers
  private static $answers= [
    'credential' => [
      ['data' => ['test_db_password' => 'db']],
      ['data' => ['test_ldap_password' => 'ldap']],
      ['data' => ['prod_master_key' => 'master']]
    ],
    'non_existant_credential' => [
      null
    ],
    'credentials' => [
      ['data' => ['test_db_password' => 'db', 'test_ldap_password' => 'ldap']],
      ['data' => ['prod_master_key' => 'master']]
    ],
  ];

  /** @return security.vault.Secrets */
  protected function newFixture() {
    $answers= &self::$answers[$this->getName()];
    return new FromVault(newinstance(Endpoint::class, ['http://test'], [
      'execute' => function(RestRequest $request) use(&$answers) {
        $answer= array_shift($answers);
        if (null === $answer) {
          return new RestResponse(404, 'Not found');
        } else {
          return newinstance(RestResponse::class, [200, 'OK'], [
            'value' => function($type= null) use($answer) { return $answer; }
          ]);
        }
      }
    ]));
  }

  #[@test, @values([
  #  ['http://vault:8200'],
  #  [new URL('http://vault:8200')],
  #  [new Endpoint('http://vault:8200')]
  #])]
  public function can_create_with($arg) {
    new FromVault($arg);
  }

  #[@test, @values(['secret', new Secret('for-vault')])]
  public function can_create_with_token($token) {
    new FromVault('http://vault:8200', $token);
  }

  #[@test]
  public function can_create_with_token_and_group() {
    new FromVault('http://vault:8200', 'SECRET_VAULT_TOKEN', '/vendor/name');
  }

  #[@test]
  public function uses_environment_variable_by_default() {
    putenv('VAULT_ADDR=http://127.0.0.1:8200');
    new FromVault();
  }

  #[@test, @expect(FormatException::class)]
  public function fails_if_environment_variable_missing() {
    putenv('VAULT_ADDR=');
    new FromVault();
  }

  #[@test, @values(['map' => [
  #  '/'             => '/',
  #  '/vendor/name'  => '/vendor/name/',
  #  '/vendor/name/' => '/vendor/name/',
  #  'vendor/name'   => '/vendor/name/',
  #  'vendor/name/'  => '/vendor/name/',
  #]])]
  public function using_group($group, $path) {
    $endpoint= newinstance(Endpoint::class, ['http://test'], [
      'execute' => function(RestRequest $request) use(&$requested) {
        $requested= $request->path();
        return new RestResponse(404, 'Not found');
      }
    ]);

    (new FromVault($endpoint, 'SECRET_VAULT_TOKEN', $group))->named('credential');
    $this->assertEquals('/v1/secret'.$path, $requested);
  }
}