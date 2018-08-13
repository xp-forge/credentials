<?php namespace security\credentials;

interface Secrets {

  /** @return self */
  public function open();

  /**
   * Get a named credential
   *
   * @param  string $name
   * @return util.Secret
   */
  public function named($name);

  /**
   * Get credentials for a given pattern
   *
   * @param  string $pattern Name with * meaning any character except a dot
   * @return [:util.Secret]
   */
  public function all($pattern);

  /** @return void */
  public function close();
}