<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SmsEmailScheduleds extends Model
{
    protected $table 	=	'sms_email_scheduleds';
    protected $fillable = ['user_id','atp_url_id','status','response','sms_batch_id','email_batch_id','scheduled_date'];
}
