<?php


namespace App\Repositories\Interfaces;


Interface PDFGenerationInterface
{
  public function generatePDF($userId,$milestone_status);
  // public function fnRegeneratePDF($userId, $recordStatus, $milestone_status ="live", $flEmail = true);
  public function fnRegeneratePDFTest();
  public function generateEngagementPDF($userId);
  public function generateAuthenticityPDF($userId);
  public function generateQuestionnairePDF($userId);
  public function generatePreviewPDF($userId);
  public function generateStatementPDF($userId);
}
