<?php namespace security\credentials;

use io\streams\LinesIn;
use util\Secret;

class FromStream implements Secrets {
  protected $input;
  private $secrets= null;

  /**
   * Uses stream as secret storage
   *
   * @param  io.streams.TextReader|io.streams.InputStream|io.Channel|string $input
   */
  public function __construct($input) {
    $this->input= cast($input, 'io.streams.TextReader|io.streams.InputStream|io.Channel|string');
  }

  /** @return self */
  public function open() {
    if (null === $this->secrets) {
      $this->secrets= [];
      foreach (new LinesIn($this->input) as $line) {
        sscanf($line, '%[^=]=%s', $name, $secret);
        $this->secrets[strtolower($name)]= new Secret(preg_replace_callback(
          '/\\\\x([0-9a-f]{2})/i',
          function($matches) { return pack('H*', $matches[1]); },
          $secret
        ));
      }
    }
    return $this;
  }

  /**
   * Get a named credential
   *
   * @param  string $name
   * @return util.Secret
   */
  public function named($name) {
    return isset($this->secrets[$name]) ? $this->secrets[$name] : null;
  }

  /**
   * Get credentials for a given pattern
   *
   * @param  string $pattern Name with * meaning any character except a dot
   * @return php.Generator
   */
  public function all($pattern) {
    $match= substr($pattern, 0, strrpos($pattern, '*'));
    foreach ($this->secrets as $name => $value) {
      if (0 === strncmp($name, $match, strlen($match))) yield $name => $value;
    }
  }

  /** @return void */
  public function close() {
    $this->secrets= null;
  }
}