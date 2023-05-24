<?php


namespace App\Repositories\Interfaces;


interface SignatureDataIngestionInterface
{
  public function store($signatureData,$visitorParameters,$previousData,$formData,$visitorData,$queryString);
}
