<?php namespace security\credentials;

use lang\Environment;
use util\Secret;

class FromEnvironment implements Secrets {
  const REMOVE = true;

  private $remove;
  private $unset= [];

  /**
   * Uses environment as secrets storage
   *
   * @param  bool $remove Whether to remove the secrets after usage
   */
  public function __construct($remove= false) {
    $this->remove= $remove;
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
    $name= strtoupper(strtr($name, ['/' => '__']));
    if (null === ($value= Environment::variable($name, null))) return null;

    $this->remove && $this->unset[$name]= null;
    return new Secret($value);
  }

  /**
   * Get credentials for a given pattern
   *
   * @param  string $pattern Name with * meaning any character except a dot
   * @return iterable
   */
  public function all($pattern) {
    $match= strtoupper(strtr(substr($pattern, 0, strrpos($pattern, '*')), ['/' => '__']));
    foreach ($_ENV as $name => $value) {
      if (0 === strncmp($name, $match, strlen($match))) yield strtr(strtolower($name), ['__' => '/']) => new Secret($value);
    }
  }

  /** @return void */
  public function close() {
    if ($this->unset) {
      Environment::export($this->unset);
      $this->unset= [];
    }
  }
}