<?php namespace security\vault;

use lang\ElementNotFoundException;
use lang\IllegalArgumentException;

class Credentials implements \lang\Closeable {
  private $secrets;
  private $open= false;

  /**
   *
   * @param  security.vault.Secrets... $secrets
   * @throws lang.IllegalArgumentException
   */
  public function __construct(...$secrets) {
    if (empty($secrets)) {
      throw new IllegalArgumentException('Secrets cannot be empty');
    }

    $this->secrets= cast($secrets, 'security.vault.Secrets[]');
  }

  /**
   * Explicitely opens this vault (otherwise done when first credential is fetched)
   *
   * @return self
   */
  public function open() {
    if (!$this->open) {
      foreach ($this->secrets as $secrets) {
        $secrets->open();
      }
    }
    $this->open= true;
    return $this;
  }

  /**
   * Get a named credential
   *
   * @param  string $name
   * @return util.Secret
   * @throws lang.ElementNotFoundException
   */
  public function credential($name) {
    foreach ($this->open()->secrets as $secrets) {
      if (null !== ($secret= $secrets->named($name))) return $secret;
    }
    throw new ElementNotFoundException('No credential named "'.$name.'"');
  }

  /**
   * Get credentials for a given pattern
   *
   * @param  string $pattern Name with * meaning any character except a dot
   * @return php.Generator
   */
  public function credentials($pattern) {
    foreach ($this->open()->secrets as $secrets) {
      foreach ($secrets->all($pattern) as $name => $secret) {
        yield $name => $secret;
      }
    }
  }

  /**
   * Explicitely closes this vault (otherwise done during object destruction)
   *
   * @return self
   */
  public function close() {
    if ($this->open) {
      foreach ($this->secrets as $secrets) {
        $secrets->close();
      }
      $this->open= false;
    }    
  }

  /** @return void */
  public function __destruct() { $this->close(); }
}