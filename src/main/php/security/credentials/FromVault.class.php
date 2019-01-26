<?php namespace security\credentials;

use util\Secret;
use webservices\rest\Endpoint;

class FromVault implements Secrets {
  private $endpoint, $group;

  /**
   * Creates a secrets source which reads credentials from a running vault service
   *
   * @param  string|peer.URL|webservices.rest.Endpoint $endpoint If omitted, defaults to `VAULT_ADDR` environment variable
   * @param  string|util.Secret $token If omitted, defaults to `VAULT_TOKEN` environment variable
   * @param  string $group The secret group, e.g. "/vendor/name"
   */
  public function __construct($endpoint= null, $token= null, $group= '/') {
    if ($endpoint instanceof Endpoint) {
      $this->endpoint= $endpoint;
    } else {
      $this->endpoint= new Endpoint($endpoint ?: getenv('VAULT_ADDR'));
    }

    if ($token instanceof Secret) {
      $this->endpoint->with('X-Vault-Token', $token->reveal());
    } else if ($header= $token ?: getenv('VAULT_TOKEN')) {
      $this->endpoint->with('X-Vault-Token', $header);
    }

    $this->group= '/' === $group ? '' : trim($group, '/').'/';
  }

  /** @return self */
  public function open() { return $this; }

  /**
   * Get a named credential
   *
   * @param  string $name
   * @return util.Secret
   */
  public function named($name) {
    $response= $this->endpoint->resource('/v1/secret/'.$this->group)->get();
    if ($response->status() < 400) {
      $data= $response->value()['data'];
      return isset($data[$name]) ? new Secret($data[$name]) : null;
    } else {
      return null;
    }
  }

  /**
   * Get credentials for a given pattern
   *
   * @param  string $pattern Name with * meaning any character except a dot
   * @return iterable
   */
  public function all($pattern) {
    $response= $this->endpoint->resource('/v1/secret/'.$this->group)->get();
    if ($response->status() < 400) {
      $match= substr($pattern, 0, strrpos($pattern, '*'));
      foreach ($response->value()['data'] as $name => $value) {
        if (0 === strncmp($name, $match, strlen($match))) yield $name => new Secret($value);
      }
    }
  }

  /** @return void */
  public function close() {
    // NOOP
  }
}