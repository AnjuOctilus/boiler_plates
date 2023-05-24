<?php

namespace Database\Seeders;

use App\Models\FollowupStrategyContent;
use Illuminate\Database\Seeder;

class FollowupStrategyContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $contentData = [
            ['id'=>1,'template_id'=>168,'subject'=>NULL,'content' =>'{First Name}, great job taking the intial steps with your PPI Plevin Check!.','campaign_name' => 'SMS Trigger # 1'],
            ['id'=>2,'template_id'=>169,'subject'=>NULL,'content' =>'We have a Private Message for review - Click Here to Read.','campaign_name' => 'SMS Trigger # 2'],
            ['id'=>3,'template_id'=>170,'subject'=>NULL,'content' =>'next steps are critical in successfully processing your PPI Plevin check - click here.','campaign_name' => 'SMS Trigger # 3'],
            ['id'=>4,'template_id'=>166,'subject'=>'Attention: Your Next Steps are Critical to Successfully Process your claim against {Bank}','content' =>'"<p>Dear {First Name},</p>

            <p>Congratulations on taking the first steps in checking your eligibility to make a PPI ‘PLEVIN’ claim.</p>
            
            <p>You might be eligible for compensation if any of the below applies to you, even if you have already claimed PPI or had a PPI claim dismissed:</p>
            
            <p>High Commission Levels</p>
            
            <p>If your provider, broker or finance company were earning more than a 50% commission on your PPI policy sale.</p>
            
            <p>Undisclosed Commission</p>
            
            <p>If you were unaware of the commission being paid on your policy at the time of purchase, even if you were aware of the PPI itself.</p>
            
            [ Click Here to Proceed ] 
            
            Regards
            Adrian Madar
            Claims manager
            ClaimLion Law"','CL Email Trigger # 1​ (Immediate)','campaign_name' => 'Email Trigger # 1'],
            ['id'=>5,'template_id'=>167,'subject'=>'Private Message: Important Update on your claim against {Bank}','content' =>'Private Message: Important Update on your claim against {Bank}','"Dear {First Name},

            We have a private message regarding your claim against {Bank}.
            
            [ Click Here to Read ]
            
            Regards
            Adrian Madar
            Claims manager
            ClaimLion Law".','campaign_name' => 'Email Trigger # 2'],
        ]; 
        foreach ($contentData as $content){
            $contentObject = new FollowupStrategyContent();
            $contentObject->id = $content['id'];
            $contentObject->template_id = $content['template_id'];
            $contentObject->subject = $content['subject'];
            $contentObject->content = $content['content'];
            $contentObject->campaign_name = $content['campaign_name'];
            $contentObject->save();
        }
    }
}
