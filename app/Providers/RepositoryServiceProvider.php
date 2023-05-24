<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
	           'App\Repositories\Interfaces\UAInterface',
	           'App\Repositories\UARepository'
	    );
	    $this->app->bind(
	           'App\Repositories\Interfaces\BrowserDetectionInterface',
	           'App\Repositories\BrowserDetectionRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\MobileDetectInterface',
            'App\Repositories\MobileDetectRepository'
        );
        $this->app->bind(
               'App\Repositories\Interfaces\CommonSplitsInterface',
               'App\Repositories\CommonSplitsRepository'
        );

        $this->app->bind(
               'App\Repositories\Interfaces\VisitorInterface',
               'App\Repositories\VisitorRepository'
        );
        $this->app->bind(
               'App\Repositories\Interfaces\UserInterface',
               'App\Repositories\UserRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\PixelFireInterface',
            'App\Repositories\PixelFireRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\LogInterface',
            'App\Repositories\LogRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\ValidationInterface',
            'App\Repositories\ValidationRepository'
        );

        $this->app->bind(
            'App\Repositories\Interfaces\CakeInterface',
            'App\Repositories\CakeRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\CommonFunctionsInterface',
            'App\Repositories\CommonFunctionsRepository'
        );

        $this->app->bind(
            'App\Repositories\Interfaces\EmailInterface',
            'App\Repositories\EmailRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\LiveSessionInterface',
            'App\Repositories\LiveSessionRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\HistoryInterface',
            'App\Repositories\HistoryRepository'
        );

         $this->app->bind(
            'App\Repositories\Interfaces\QuestionnairesInterface',
            'App\Repositories\QuestionnairesRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\LPDataIngestionInterface',
            'App\Repositories\DataIngestion\LPDataIngestionRepository'
        );

         $this->app->bind(
            'App\Repositories\Interfaces\DataIngestionInterface',
            'App\Repositories\DataIngestion\DataIngestionRepository'
        );

        $this->app->bind(
            'App\Repositories\Interfaces\ApiClassInterface',
            'App\Repositories\ApiClassRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\BasicQuestionsInterface',
            'App\Repositories\BasicQuestionsRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\AdvDataIngestionInterface',
            'App\Repositories\DataIngestion\AdvDataIngestionRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\BuyerApiInterface',
            'App\Repositories\BuyerApiRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\VoucherDetailsInterface',
            'App\Repositories\DataIngestion\VoucherDetailsRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\PDFGenerationInterface',
            'App\Repositories\PDFGenerationRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\CRMInterface',
            'App\Repositories\CrmRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\SignatureDataIngestionInterface',
            'App\Repositories\DataIngestion\SignatureDataIngestionRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\FollowupDataIngestionInterface',
            'App\Repositories\DataIngestion\FollowupDataIngestionRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\S3SignatureDataIngestionInterface',
            'App\Repositories\DataIngestion\S3SignatureDataIngestionRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\UserDocumentsInterface',
            'App\Repositories\UserDocumentsRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\UserDocumentsDataIngestionInterface',
            'App\Repositories\DataIngestion\UserDocumentsDataIngestionRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\FollowupSmsEmailEndPointInterface',
            'App\Repositories\FollowupSmsEmailEndPointRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\LeadSubmissionApiInterface',
            'App\Repositories\LeadSubmissionRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\SignatureRestoreInterface',
            'App\Repositories\SignatureRestoreRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\UpdateAddressInterface',
            'App\Repositories\UpdateAddressDetailsRepository'
        );
        $this->app->bind(
            'App\Repositories\Interfaces\EmailSMSInterface',
            'App\Repositories\EmailSMSRepository'
        );
        
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
