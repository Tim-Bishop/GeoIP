<?php
class mydb extends mysqli {
  public $dberror = false;

  public function __construct() {
    $str_host = 'localhost';
    $str_user = 'root';
    $str_pswd = 'toor';
    $str_db = 'test';
    parent::__construct($str_host,$str_user,$str_pswd,$str_db);

    if (mysqli_connect_errno()) {
      $this->dberror = sprintf("fatal: connect failed: %s\n", mysqli_connect_error());
    }
  } // end-function __construct
} // end-class mydb