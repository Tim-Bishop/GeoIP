<?php

namespace Pure360;

class MyZipArchiveException extends \Exception
{
}

class MyZipArchive extends \ZipArchive
{
    private $file;
    private $tmpDir;
    private $zipIn;

    use GetAndSet; // trait to add get and set methods for properties

    public static function create()
    {
        $result = new static();
        return $result;
    }

    private function downloadFile()
    {
        // download zip file
        if (($res_fp = fopen($this->getFile(), 'r')) === false) {
            throw new MyZipArchiveException("warning: fopen() failed!");
        }

        if (file_put_contents($this->getZipIn(), $res_fp) === false) {
            throw new MyZipArchiveException("warning: file_put_contents() failed!");
        }
        fclose($res_fp);
        return $this;
    }

    public function unzipFile()
    {
        // unzip file
        if (($result = $this->open($this->getZipIn())) === true) {
        } else {
            throw new MyZipArchiveException("warning: ZipArchive::open() failed!");
        }

        if ($this->extractTo($this->getTmpDir()) === false) {
            throw new MyZipArchiveException("warning: ZipArchive::extractTo() failed!");
        }

        // remove temp file
        @unlink($this->getZipIn()); // use @ to suppress unlink warnings
        return $this;
    }

    public function downloadUnzip($str_file, $str_tmpdir)
    {
        $this->setFile($str_file)
            ->setTmpDir($str_tmpdir)
            ->setZipIn(tempnam($str_tmpdir, 'zipin_'))
            ->downloadFile()
            ->unzipFile();
    }

}
