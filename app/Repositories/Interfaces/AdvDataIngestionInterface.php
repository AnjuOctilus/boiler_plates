<?php


namespace App\Repositories\Interfaces;


Interface AdvDataIngestionInterface
{
  public function saveADVVisitorData($arrParamData, $arrParamVisitor,$page);
  public function saveAdvClicks($arrParamData, $arrParamVisitor,$page);
  public function setAgentVisitorParam($request,$pageName);
  public static function setAdvAgentRequest($request,$arrParam, $uuid);
}
