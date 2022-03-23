<?php

namespace App\Services;


use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Pdf{
   
    protected $command = '%s --margin-top 0 --margin-bottom 0 --margin-left 0 --margin-right 0';

    protected $binary = '/etc/local/bin/wkhtmltopdf-amd64';

    public function render($view, $path_file){



        $process = new Process(sprintf(
            $this->command,
            escapeshellarg($this->binary),
            escapeshellarg($path = $path_file),
            escapeshellarg('data:text/html,'.rawurlencode($view))
        ));

        try {
            $process->mustRun();

            return array(
                "status" => "OK",
                "data" => File::get($path)
            );
        } catch (ProcessFailedException $exception) {
           return array(
               "status" => "NOK",
               "data" => array(
                   "errNo" => 11,
                   "errMsg" => $exception->getMessage()
               )
           );
        }
    }

}