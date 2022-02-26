<?php namespace security\credentials;

use info\keepass\KeePassDatabase;
use info\keepass\Key;
use io\File;
use util\Secret;

class FromKeePass implements Secrets {
  private $file, $key, $group;
  private $db= null;

  /**
   * Uses KeePass database for storage
   *
   * @param  io.File|string $file
   * @param  util.Secret $key
   * @param  string $group The secret group, e.g. "/vendor/name"
   */
  public function __construct($file, Secret $key, $group= '/') {
    $this->file= $file instanceof File ? $file : new File($file);
    $this->key= $key;
    $this->group= trim($group, '/');
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
    foreach ($this->db->group('/'.$this->group)->passwords() as $path => $value) {
      if (basename($path) === $name) return new Secret((string)$value);
    }
    return null;
  }

  /**
   * Get credentials for a given pattern
   *
   * @param  string $pattern Name with * meaning any character except a dot
   * @return iterable
   */
  public function all($pattern) {
    $match= substr($pattern, 0, strrpos($pattern, '*'));
    foreach ($this->db->group('/'.$this->group)->passwords() as $path => $value) {
      $base= basename($path);
      if (0 === strncmp($base, $match, strlen($match))) yield $base => new Secret((string)$value);
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