<?php namespace security\credentials;

use info\keepass\KeePassDatabase;
use info\keepass\Key;
use io\File;
use util\Secret;

class FromKeePass implements Secrets {
  private $file, $key;
  private $db= null;

  /**
   * Uses KeePass database for storage
   *
   * @param  io.File|string $file
   * @param  util.Secret $key
   */
  public function __construct($file, Secret $key) {
    $this->file= $file instanceof File ? $file : new File($file);
    $this->key= $key;
  }

  /** @return self */
  public function open() {
    if (null === $this->db) {
      $this->db= KeePassDatabase::open($this->file->in(), new Key($this->key->reveal()));
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
    if (false === ($p= strrpos($name, '/'))) {
      $group= '/';
      $match= $name;
    } else {
      $group= '/'.substr($name, 0, $p);
      $match= substr($name, $p + 1);
    }
    
    foreach ($this->db->group($group)->passwords() as $path => $value) {
      if (basename($path) === $match) return new Secret((string)$value);
    }
    return null;
  }

  /**
   * Get credentials for a given pattern
   *
   * @param  string $pattern Name with * meaning any character except a dot
   * @return php.Generator
   */
  public function all($pattern) {
    if (false === ($p= strrpos($pattern, '/'))) {
      $group= '/';
      $match= substr($pattern, 0, strrpos($pattern, '*'));
    } else {
      $group= '/'.substr($pattern, 0, $p);
      $match= substr($pattern, $p + 1, strrpos($pattern, '*') - $p - 1);
    }
    
    foreach ($this->db->group($group)->passwords() as $path => $value) {
      if (0 === strncmp(basename($path), $match, strlen($match))) yield substr($path, 1) => new Secret((string)$value);
    }
  }

  /** @return void */
  public function close() {
    if ($this->db) {
      $this->db->close();
      $this->db= null;
    }
  }
}