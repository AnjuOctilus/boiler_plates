<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Defined Variables
    |--------------------------------------------------------------------------
    |
    | This is a set of variables that are made specific to this application
    | that are better placed here rather than in .env file.
    | Use config( 'your_key' ) to get the values.
    |
    */


    'DATA_KEY_TEST'         => 'I_FDB2EE55108844839FFCBE20CC5231',
    'DATA_KEY_LIVE'         => 'W_684F41E07D984539947A348AA2BC56',
    'TOKEN'                 => '9ee2a77e8ce49c20bfc020303ebacb58a1ccf26248862bc0726f6fbc361f8f28',
    //Api token
    'AUTH_API_TOKEN'        => env('AUTH_API_TOKEN','9ee2a77e8ce49c20bfc020303ebacb58a1ccf26248862bc0726f6fbc361f8f28'),
    'log_path'              => base_path().'/storage/logs',
    'live_log_path'         => base_path().'/storage/logs',
    's3_log_path'           => base_path().'/storage/app/',
    'DATA_KEY_TEST'         => 'I_FDB2EE55108844839FFCBE20CC5231',
    'DATA_KEY_LIVE'         => 'W_FDB2EE55108844839FFCBE20CC5231',
    'DATA8_USERNAME'        => 'tia123',
    'DATA8_PASSWORD'        => 'Bojangles0469',
    //For Test Leads:
    'CAKE_CAMPAIGN_ID_TEST' => '4408',
    'CAKE_CKM_KEY_TEST'     => 'T5677v1awjM',
    //For Live Leads:
    //'CAKE_CAMPAIGN_ID'      => '4408',
    //'CAKE_CKM_KEY'          => 'T5677v1awjM',
    'CAKE_CAMPAIGN_ID'      => '4409',
    'CAKE_CKM_KEY'          => 'lhUBxZuj81w',
    //For api testing:
    'TO_EMAIL_ADDRESS'      => "developers@vandalayglobal.com",
    'FROM_EMAIL_ADDRESS'    => "developers@vandalayglobal.com",
    'TO_EMAIL_API_TEST'     => "livin.cj@vandalayglobal.com",
    'TO_EMAIL_API_ERROR'    => "developers@vandalayglobal.com",
    'APP_PROCLAIM_API_USERNAME' => "LondonBridge",
    'APP_PROCLAIM_API_PASSWORD' => "L0nd0nBr1dg3",
    'VEHICLE_DATA_KEY'          => '9a0c3e8c-d2d1-4adf-ae59-d609e4702dc7',
    'VEHICLE_VALUATION_DATA_KEY_TEST' => '0bd605f3-79c0-4726-91d6-e1baf6a02105',
    'VEHICLE_VALUATION_DATA_KEY_LIVE' => '9a0c3e8c-d2d1-4adf-ae59-d609e4702dc7',
    'NEW_ADDRESS_API_KEY'   => 'KcrNNLQO20qblGmDxLimjw33618',
    'NEW_ADDRESS_API_SECRET'  => 'FDSs9YRh6EmZBeUjE6AhtA33618',
    'ELK_CLOUD_ID'          => 'Lead_Gen_LBM:ZXVyb3BlLXdlc3QyLmdjcC5lbGFzdGljLWNsb3VkLmNvbSQzNmRkMzEyZWUyMjc0NjFiODk1YWFmNDcyZGYyYzJlNSQzYWMzNjM0MmY5N2Y0ZDIyYmQ4NDNlNTQwZGFkMTlkYg==',
    'ELK_KEY_ID'            => '-KRJP3sBPH9hismv7hfi',
    'ELK_KEY'               => 'oTA947XASOG8xrinIIxcrg',
    'SMS_EMAIL_SHORT_URL'         => 'https://dev.adto.uk',
    'PLEVIN_PDF_STORAGE_BASE_PATH' => 'https://dev.doc.onlineplevincheck.co.uk',
    'PLEVIN_PDF_STORAGE_BASE_PATH_LIVE' => 'https://doc.onlineplevincheck.co.uk',
    //'PLEVIN_PDF_STORAGE_BASE_PATH' => 'https://dev.doc.react.onlineplevincheck.co.uk',
    //'PLEVIN_PDF_STORAGE_BASE_PATH_LIVE' => 'https://doc.react.onlineplevincheck.co.uk',
    'AWS_BUCKET'=>'pdf.onlineplevincheck.co.uk',
    'AWS_DEFAULT_REGION'=>'eu-west-2',
    'AWS_ACCESS_KEY_ID'=>'AKIAR7CDOEEIYGI7HKH3',
    'AWS_SECRET_ACCESS_KEY'=>'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'


];


