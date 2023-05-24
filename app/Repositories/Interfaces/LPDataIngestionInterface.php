<?php

namespace App\Repositories\Interfaces;
use Illuminate\Http\Request;


interface LPDataIngestionInterface
{
    public static function setLPParam( $request );

    public function commonSplits( $data ,$visitorParam, $currentTime,$pageName,$queryString);

    public function store( $data,$data_query, $params,$currentTime,$pageName,$visitorData);

    public function updateAdvId( $data ,$visitorParam);

    public static function setAgentVisitorParam( $request,$pageName );
    
}
