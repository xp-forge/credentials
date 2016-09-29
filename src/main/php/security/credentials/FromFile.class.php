<?php namespace security\credentials;

use io\File;

class FromFile extends FromStream {
  const REMOVE = true;

  private $remote= false;

  /**
   * Uses file as secret storage
   *
   * @param  io.File|string $input
   * @param  bool $remove
   */
  public function __construct($input, $remove= false) {
    parent::__construct($input instanceof File ? $input : new File($input));
    $this->remove= $remove;
  }

  /** @return void */
  public function close() {
    $this->remove && $this->input->unlink();
    parent::close();
  }
}