<?php namespace security\credentials;

use lang\IllegalAccessException;
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
   * @throws lang.IllegalAccessException if vault is not accessible
   */
  public function named($name) {
    $r= $this->endpoint->resource('/v1/secret/'.$this->group)->get();
    switch ($r->status()) {
      case 200:
        $data= $r->value()['data'];
        return isset($data[$name]) ? new Secret($data[$name]) : null;

      case 404:
        return null;

      default:
        throw new IllegalAccessException('Unexpected '.$r->status().': '.$r->error());
    }
  }

  /**
   * Get credentials for a given pattern
   *
   * @param  string $pattern Name with * meaning any character except a dot
   * @return iterable
   * @throws lang.IllegalAccessException if vault is not accessible
   */
  public function all($pattern) {
    $r= $this->endpoint->resource('/v1/secret/'.$this->group)->get();
    switch ($r->status()) {
      case 200:
        $match= substr($pattern, 0, strrpos($pattern, '*'));
        foreach ($r->value()['data'] as $name => $value) {
          if (0 === strncmp($name, $match, strlen($match))) yield $name => new Secret($value);
        }
        return;

      case 404:
        return;

      default:
        throw new IllegalAccessException('Unexpected '.$r->status().': '.$r->error());
    }
  }

  /** @return void */
  public function close() {
    // NOOP
  }
}