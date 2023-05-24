<?php

namespace App\Repositories;

use App\Repositories\Interfaces\UpdateAddressInterface;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\UserExtraDetail;
use App\Models\SignatureDetails;
use App\Models\UserAddressDetails;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Class LogRepository
 *
 * @package App\Repositories
 */
class UpdateAddressDetailsRepository implements UpdateAddressInterface
{
    /**
     * Write log
     *
     * @param string $name
     * @param $data
     * @return int
     */
    public function updateDetails($limit,$start,$end)
    {
        $userExtra = DB::table('user_extra_details')
        ->leftJoin('user_address_details as UAD', 'UAD.user_id', '=', 'user_extra_details.user_id')
        ->where('UAD.user_id', '=', NULL)
        ->where('UAD.address_type', '=', 0)
        ->where('user_extra_details.user_id', '>',$start)
        ->where('user_extra_details.user_id', '<=',$end)
        ->select('user_extra_details.user_id')->get()->toArray();
        $extra_details = array_unique(array_column($userExtra,'user_id'));
        $addressTypes = SignatureDetails::select('signature_id')->get()->toArray();
        $addressType = array_unique(array_column($addressTypes,'signature_id'));
        $userSig = SignatureDetails::select('user_id')->whereIn('signature_id',$addressType)->get()->toArray();
        $sig_details =array_unique(array_column($userSig,'user_id'));
        $extra_details = array_diff($extra_details, $sig_details);

        $sig_details = array_merge ($sig_details, $extra_details);
       /* $users = DB::table('users')
        ->leftJoin('user_address_details as UAD', 'UAD.user_id', '=', 'users.id')
        ->where('UAD.user_id', '=', NULL)
        ->orWhere('UAD.')
        ->where('user.id', '>', $user_id)
        ->where('user.id', '<=', 500)
        /*->whereHas('UserExtraDetail',function($query)  use ($user_id) {
            $query->where('user_id',$user_id);})
            ->whereHas('SignatureDetails',function($query)  use ($user_id) {
                $query->where('user_id',$user_id);
                $query->whereIn('signature_id',[1,2]);
            })*/
      /*  ->select('users.id')
        ->orderBy('users.id', 'asc')->limit(5)->get();*/
        $limit = 5;
        $num = 0;
        foreach($sig_details as $user)
        {
            $userExist = User::find($user);
            if(!empty($userExist))
            {
                $this->updateAddress($user);
                $this->updatePreviousAddress($user);
                DB::table('user_crone_processed')->insert([
                    'user_id' => $user,               
                ]);
                $num +=1;
                if($num == $limit)
                {
                    break;
                }
            }
            
        }
    }
    public function updateAddress($userId)
    {
        $userExtra = UserExtraDetail::where(['user_id' => $userId])->orderBy('id','desc')->first();
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        if(!empty($userExtra))
        {
            if($this->checkUserAddressExist($userId,0))
                {
                        $user_address_details = array(
                            'user_id' => $userId,
                            'address_type' => '0',
                            'postcode' => isset($userExtra->postcode)?$userExtra->postcode:'',
                            'address_line1' => isset($userExtra->address_line1)?$userExtra->address:'',
                            'address_line2' => isset($userExtra->address_line2)?$userExtra->address3:'',
                            'address_line3' => isset($userExtra->address_line3)?$userExtra->housenumber:'',
                            'town' => isset($userExtra->town)?$userExtra->town:'',
                            'locality' => isset($userExtra->street)?$userExtra->street:'',
                            'county' => isset($userExtra->county)?$userExtra->county:'',
                            'district' => '',
                            'country' => isset($userExtra->country)?$userExtra->country:'',
                            'address_id' => isset($userExtra->addressid)?$userExtra->addressid:'',
                            'created_at' => $currentTime,
                        );
                        $IntUserAddressDetails = UserAddressDetails::insertGetId($user_address_details);
                }
        }
        
    }

    public function updatePreviousAddress($userId)
    {
        $userExtraDetails = SignatureDetails::where(['user_id' => $userId])->get();
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        if(!empty($userExtraDetails))
        {
            foreach($userExtraDetails as $userExtra)
            {
                if($this->checkUserAddressExist($userId,$userExtra->signature_id))
                {
                    $user_address_details = array(
                        'user_id' => $userId,
                        'address_type' => $userExtra->signature_id,
                        'postcode' => isset($userExtra->previous_postcode)?$userExtra->previous_postcode:'',
                        'address_line1' => isset($userExtra->previous_address_line1)?$userExtra->previous_address_line1:'',
                        'address_line2' => isset($userExtra->previous_address_line2)?$userExtra->previous_address_line2:'',
                        'address_line3' => isset($userExtra->previous_address_line3)?$userExtra->previous_address_line3:'',
                        'town' => isset($userExtra->previous_address_city)?$userExtra->previous_address_city:'',
                        'locality' => isset($userExtra->previous_address_province)?$userExtra->previous_address_province:'',
                        'county' => isset($userExtra->county)?$userExtra->county:'',
                        'district' => '',
                        'country' => isset($userExtra->previous_address_country)?$userExtra->previous_address_country:'',
                        'address_id' => isset($userExtra->previous_address_id)?$userExtra->previous_address_id:'',
                        'created_at' => isset($userExtra->created_at)?$userExtra->created_at:'',
                    );            
                    $IntUserAddressDetails = UserAddressDetails::insertGetId($user_address_details);
                }
            }
        }
    }
    public function checkUserAddressExist($userId, $type)
    {
        $userAddress = UserAddressDetails::where(['user_id'=>$userId,'address_type'=>$type])->get(); 
        return (!empty($userAddress)?1:0);
    }
  
}
