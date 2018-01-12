<?php namespace security\credentials;

use io\File;
use io\Path;
use io\Folder;
use util\Secret;

/**
 * In Docker 1.13 and higher, you can use Docker secrets
 * 
 * @see  https://docs.docker.com/engine/swarm/secrets/
 */
class FromDockerSecrets implements Secrets {
  private $path;

  /**
   * Creates a new Docker Secrets source. Optionally, may be given a path. If
   * omitted, Docker's default locations are used.
   *
   * @param  string|io.Path|io.Folder $path
   */
  public function __construct($path= null) {
    if ($path instanceof Path) {
      $this->path= $path;
    } else if ($path instanceof Folder) {
      $this->path= new Path($path->getURI());
    } else if (null !== $path) {
      $this->path= new Path($path);
    } else if (0 === strncasecmp(PHP_OS, 'Win', 3)) {
      $this->path= new Path(getenv('ProgramData'), 'Docker/secrets');
    } else {
      $this->path= new Path('/run/secrets');
    }
  }

  /** @return io.Path */
  public function path() { return $this->path; }

  public function open() { }

  /**
   * Get a named credential
   *
   * @param  string $name
   * @return util.Secret
   */
  public function named($name) {
    $file= new File($this->path, $name);
    if (!$file->exists()) return null;

    $file->open(File::READ);
    try {
      return new Secret($file->read($file->size()));
    } finally {
      $file->close();
    }
  }

  /**
   * Get credentials for a given pattern
   *
   * @param  string $pattern Name with * meaning any character except a dot
   * @return php.Generator
   */
  public function all($pattern) {
    $base= (string)$this->path;
    foreach (glob($base.$pattern) as $name) {
      $file= new File($name);
      $file->open(File::READ);
      try {
        yield substr($name, strlen($base)) => new Secret($file->read($file->size()));
      } finally {
        $file->close();
      }
    }
  }

  public function close() { }
}