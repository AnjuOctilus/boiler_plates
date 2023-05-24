<?php 

namespace App\Repositories\Interfaces;

interface UserDocumentsInterface
{
    public function sendUserDocuments( $dataArray );
    public function saveFileIntoS3( $fileName, $filePath, $folderPath );

}