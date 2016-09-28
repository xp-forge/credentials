<?php namespace security\vault;

use webservices\rest\Endpoint;
use util\Secret;

class FromVaultService implements Secrets {
  private $endpoint;

  /**
   * Creates a secrets source which reads credentials from a running vault service
   *
   * @param  string|peer.URL|webservices.rest.Endpoint $endpoint If omitted, defaults to `VAULT_ADDR` environment variable
   * @param  string $token If omitted, defaults to `VAULT_TOKEN` environment variable
   */
  public function __construct($endpoint= null, $token= null) {
    if ($endpoint instanceof Endpoint) {
      $this->endpoint= $endpoint;
    } else {
      $this->endpoint= new Endpoint($endpoint ?: getenv('VAULT_ADDR'));
    }

    if ($header= $token ?: getenv('VAULT_TOKEN')) {
      $this->endpoint->with(['X-Vault-Token' => $header]);
    }
  }

  /** @return void */
  public function open() {
    // NOOP
  }

  /**
   * Get a named credential
   *
   * @param  string $name
   * @return util.Secret
   */
  public function named($name) {
    $p= strrpos($name, '/');
    $response= $this->endpoint->resource('/v1/secret/'.substr($name, 0, $p))->get();
    if ($response->isError()) {
      return null;
    } else {
      $data= $response->data()['data'];
      $key= ltrim(substr($name, $p), '/');
      return isset($data[$key]) ? new Secret($data[$key]) : null;
    }
  }

  /**
   * Get credentials for a given pattern
   *
   * @param  string $pattern Name with * meaning any character except a dot
   * @return php.Generator
   */
  public function all($pattern) {
    $p= strrpos($pattern, '/');
    $response= $this->endpoint->resource('/v1/secret/'.substr($pattern, 0, $p))->get();
    if ($response->isError()) {
      return null;
    } else {
      $key= ltrim(substr($pattern, $p), '/');
      $match= substr($key, 0, strrpos($key, '*'));
      foreach ($response->data()['data'] as $name => $value) {
        if (0 === strncmp($name, $match, strlen($match))) yield $name => new Secret($value);
      }
    }
  }

  /** @return void */
  public function close() {
    // NOOP
  }
}