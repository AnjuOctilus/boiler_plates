<?php

namespace App\Repositories\DataIngestion;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\DataIngestionInterface;

/**
 * Class DataIngestionRepository
 *
 * @package App\Repositories\DataIngestion
 */
class DataIngestionRepository implements DataIngestionInterface
{

    /**
     * Set agent request
     *
     * @param $request
     * @param $arrParam
     * @param $uuid
     * @return array
     */
    public static function setAgentRequest($request, $arrParam, $uuid)
    {
        $request_array['uuid'] = $uuid;
        return $request_array;
    }

}
