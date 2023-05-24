<?php 

namespace App\Repositories\Interfaces;

interface AdvVisitorInterface
{
	public static function saveADVVisitor($arrParam);
	public static function updateLastAdvVisit($intAdvVisitorId, $strFileName);
	public static function checkAdvPixelStatus($intAdvVisitorId, $page_type = 'LP');
	public static function updatePixelStatus($intAdvVisitorId, $page_type);
	public static function findTracker($request);

}