<?php namespace security\credentials\unittest;

use io\streams\MemoryInputStream;
use lang\{FormatException, IllegalAccessException};
use peer\URL;
use peer\http\HttpResponse;
use security\credentials\FromVault;
use test\Assert;
use test\{Expect, Test, Values};
use util\Secret;
use webservices\rest\{Endpoint, RestRequest, RestResponse};

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
  protected function newFixture($name) {
    $answers= &self::$answers[$name];
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

  /** @return iterable */
  private function endpoints() {
    yield ['http://vault:8200'];
    yield [new URL('http://vault:8200')];
    yield [new Endpoint('http://vault:8200')];
  }


  #[Test, Values(from: 'endpoints')]
  public function can_create_with($arg) {
    new FromVault($arg);
  }

  #[Test, Values(eval: '["secret", new Secret("for-vault")]')]
  public function can_create_with_token($token) {
    new FromVault('http://vault:8200', $token);
  }

  #[Test]
  public function can_create_with_token_and_group() {
    new FromVault('http://vault:8200', 'SECRET_VAULT_TOKEN', '/vendor/name');
  }

  #[Test]
  public function uses_environment_variable_by_default() {
    putenv('VAULT_ADDR=http://127.0.0.1:8200');
    new FromVault();
  }

  #[Test, Expect(FormatException::class)]
  public function fails_if_environment_variable_missing() {
    putenv('VAULT_ADDR=');
    new FromVault();
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function named_on_vault_error() {
    $endpoint= newinstance(Endpoint::class, ['http://test'], [
      'execute' => function(RestRequest $request) {
        return newinstance(RestResponse::class, [503, 'Service unavailable'], [
          'error' => function($type= null) { return 'Database error'; }
        ]);
      }
    ]);

    (new FromVault($endpoint))->named('credential');
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function all_on_vault_error() {
    $endpoint= newinstance(Endpoint::class, ['http://test'], [
      'execute' => function(RestRequest $request) {
        return newinstance(RestResponse::class, [503, 'Service unavailable'], [
          'error' => function($type= null) { return 'Database error'; }
        ]);
      }
    ]);

    iterator_count((new FromVault($endpoint))->all('group*'));
  }

  #[Test, Values([['/', '/'], ['/vendor/name', '/vendor/name/'], ['/vendor/name/', '/vendor/name/'], ['vendor/name', '/vendor/name/'], ['vendor/name/', '/vendor/name/']])]
  public function using_group($group, $path) {
    $endpoint= newinstance(Endpoint::class, ['http://test'], [
      'execute' => function(RestRequest $request) use(&$requested) {
        $requested= $request->path();
        return new RestResponse(404, 'Not found');
      }
    ]);

    (new FromVault($endpoint, 'SECRET_VAULT_TOKEN', $group))->named('credential');
    Assert::equals('/v1/secret'.$path, $requested);
  }
}