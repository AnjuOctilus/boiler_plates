<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Followupstages extends Model
{
    protected $table 	=	'followup_stages';
    protected $fillable = ['user_id','stage'];
}
