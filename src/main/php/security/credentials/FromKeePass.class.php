<?php namespace security\credentials;

use info\keepass\KeePassDatabase;
use info\keepass\Key;
use util\Secret;
use io\File;

class FromKeePass implements Secrets {

  /**
   * Uses KeePass database for storage
   *
   * @param  io.File|string $file
   * @param  util.Secrert $key
   */
  public function __construct($file, Secret $key) {
    $this->file= $file instanceof File ? $file : new File($file);
    $this->key= $key;
  }

  /** @return void */
  public function open() {
    $this->db= KeePassDatabase::open($this->file->in(), new Key($this->key->reveal()));
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
    
    foreach ($this->db->group($group)->passwords() as $title => $value) {
      if ($title === $match) return new Secret((string)$value);
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
    
    foreach ($this->db->group($group)->passwords() as $title => $value) {
      if (0 === strncmp($title, $match, strlen($match))) yield ltrim($group.'/'.$title, '/') => new Secret((string)$value);
    }
  }

  /** @return void */
  public function close() {
    $this->db->close();
  }
}