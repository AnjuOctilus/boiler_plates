<?php 

namespace App\Repositories\Interfaces;

interface LogInterface
{

    public function writeLog( $name = '-custom-log', $data );
    public function writeLogIntoS3( $name = '-custom-log', $data );
    public function logIntoS3($name,$data);

}