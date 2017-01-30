<?php
class myZipArchive extends ZipArchive {
  public function downloadUnzip($str_file,$str_tmpdir) {
    $status = true;
    $str_error = false;

    $str_zipin = tempnam($str_tmpdir,'zipin_');

    // download zip file
    if ($status) {
      if (($res_fp = fopen($str_file,'r')) === false) {
        $status = true;
        $str_error = "warning: \$res_fp = fopen() failed!";
      }
    }

    if ($status) {
      if (file_put_contents($str_zipin,$res_fp) === false) {
        $status = false;
        $str_error = "warning: file_put_contents() failed!";
      }
      fclose($res_fp);
    }

    // unzip file
    if ($status) {
      if ($this->open($str_zipin) === true) {
      } else {
        $status = false;
        $str_error = "warning: ZipArchive::open() failed!";
      }
    }

    if ($status) {
      if ($this->extractTo($str_tmpdir) === false) {
        $status = false;
        $str_error = "warning: ZipArchive::extractTo() failed!";
      }
    }

    // remove temp file
    @unlink($str_zipin); // use @ to suppress unlink warnings

    return $str_error;
  } // end-function unzip
} // end-class myZipArchive
