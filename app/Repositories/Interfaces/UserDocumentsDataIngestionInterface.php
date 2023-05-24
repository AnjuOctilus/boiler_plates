<?php


namespace App\Repositories\Interfaces;


interface UserDocumentsDataIngestionInterface
{
  public function store($userDocumentData,$visitorParameters,$formData,$visitorData,$queryString);
}
