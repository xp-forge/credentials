<?php namespace security\credentials\unittest;

use security\credentials\FromVault;
use util\Secret;
use webservices\rest\Endpoint;
use webservices\rest\RestRequest;
use webservices\rest\RestResponse;
use peer\http\HttpResponse;
use io\streams\MemoryInputStream;
use peer\URL;
use lang\FormatException;

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
    'from_subfolder' => [
      ['data' => ['mysql' => 'test']],
    ],
    'all_in_subfolder' => [
      ['data' => ['mysql' => 'test']],
    ]
  ];

  /** @return security.vault.Secrets */
  protected function newFixture() {
    $answers= &self::$answers[$this->getName()];
    return new FromVault(newinstance(Endpoint::class, [], [
      '__construct' => function() {
        parent::__construct('http://test');
      },
      'execute' => function(RestRequest $request) use(&$answers) {
        $answer= array_shift($answers);
        return newinstance(RestResponse::class, [], [
          '__construct' => function() use($answer) {
            parent::__construct(new HttpResponse(new MemoryInputStream(null === $answer
              ? "HTTP/1.1 404 Not Found\r\r\r\n"
              : "HTTP/1.1 200 OK\r\n\r\n"
            )));
          },
          'data' => function($type= null) use($answer) {
            return $answer;
          }
        ]);
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
}