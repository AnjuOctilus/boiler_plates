<?php
namespace App\Repositories\Interfaces;
interface S3PDFPathInterface{
public function storePDFPath($pdfPath,$userId);
}