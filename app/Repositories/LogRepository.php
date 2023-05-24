<?php

namespace App\Repositories;

use App\Repositories\Interfaces\LogInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Storage;
use Illuminate\Support\Facades\File as File;

/**
 * Class LogRepository
 *
 * @package App\Repositories
 */
class LogRepository implements LogInterface
{
    /**
     * Write log
     *
     * @param string $name
     * @param $data
     * @return int
     */
    public function writeLog($name = '-custom-log', $data)
    {
        $date = Carbon::now();
        // create a log channel
        $myval = env('APP_ENV', null);
        if ($myval != 'local') {
            $logPath = config('constants.live_log_path');
        } else {
            $logPath = config('constants.log_path');
        }
        $logPath = config('constants.log_path');

        $log = new Logger($name);
        $log->pushHandler(new StreamHandler($logPath . '/' . $date->toDateString() . $name . '.log', Logger::INFO));
        $log_data = json_decode(json_encode($data), 1);
        // add records to the log
        $log->info(json_encode($log_data));
        return 1;
    }
    /**
     * Write log into S#
     *
     * @param string $name
     * @param $data
     * @return int
     */
    public function writeLogIntoS3($name = '-custom-log', $data)
    {
        return $this->writeLog($name, $data);
    }
    /**
     * Login into  S3
     *
     * @param $name
     * @param $data
     * @return string
     */
    public function logIntoS3($name, $data)
    {
        $date = Carbon::now();
        $APP_ENV = env('APP_ENV');

        if ($APP_ENV == 'pre' || $APP_ENV == 'live') {
            $logPath = config('constants.s3_log_path');
        } else {
            $logPath = config('constants.s3_log_path');
        }
        $filename = $date->toDateString() . $name . "_" . $APP_ENV . '.log';
        $filepath = $logPath . $filename;
        $s3_basic_path = "logs/";
        $s3Location = $s3_basic_path . $filename;
        $s3 = Storage::disk('s3');
        $exists = $s3->exists($s3Location);
        $localDisk = Storage::disk('local');
        if ($exists != 'true') {
            // add records to the log
            $log = new Logger($name);
            $log->pushHandler(new StreamHandler($filepath, Logger::INFO));
            $log_data = json_decode(json_encode($data), 1);
            $log->info(json_encode($log_data));
            // add file to s3
            $filecontent = $localDisk->get($filename);
            $s3->put($s3Location, $filecontent);
            $url = $s3->url($s3Location);
        } else {
            $retrievedFile = $s3->read($s3Location, $filename);
            $fileSave = $localDisk->put($filename, $retrievedFile);
            $log = new Logger($name);
            $log->pushHandler(new StreamHandler($filepath, Logger::INFO));
            $log_data = json_decode(json_encode($data), 1);
            $log->info(json_encode($log_data));
            $filecontent = $localDisk->get($filename);
            $s3->put($s3Location, $filecontent);
            $url = $s3->url($s3Location);
        }
        echo $url;
        return $filepath;
    }
}
