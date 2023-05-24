<?php

namespace App\Console\Commands;

use App\Jobs\DeleteandRegeneratePDF;
use App\Models\LeadDoc;
use App\Repositories\UserRepository;
use DB;
class RecreateCOAFile extends \Illuminate\Console\Command{

    protected $signature = 'Api:RecreateCOAFile';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Recreate COA FIle';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    public function handle(){
        $leadDocsData = LeadDoc::where('lead_docs.id','>=',240)
            ->where('lead_docs.id','<=',250)
            ->limit(3)
            ->whereNotExists(function($query){
                $query->select(DB::raw(1))
                ->from('processed_coa_pdfs')
                ->whereRaw('processed_coa_pdfs.lead_docs_id = lead_docs.id');
            }
            )
            ->get();
    
            if(isset($leadDocsData) && !empty($leadDocsData)){
                foreach($leadDocsData as $leadDocs){
                    dispatch(new DeleteandRegeneratePDF($leadDocs->id,$leadDocs->user_id));
                }
            }
        
    }

}