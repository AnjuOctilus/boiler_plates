<?php


namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Jobs\PostLeadsToCake;
use App\Models\User;
use App\Repositories\Interfaces\CommonFunctionsInterface;

/**
 * Class PostCakeController
 *
 * @package App\Http\Controllers\V1
 */
class PostCakeController extends Controller
{
    /**
     * PostCakeController constructor.
     *
     * @param CommonFunctionsInterface $commonFunctionsInterface \
     */
    public function __construct(CommonFunctionsInterface $commonFunctionsInterface)
    {
        $this->commonFunctionsInterface = $commonFunctionsInterface;
    }

    /**
     * Process cake
     *
     * @param $user_id
     * @return false|string
     */
    public function processCake($user_id)
    {
        $user = User::find($user_id);
        $recordStatus = $this->commonFunctionsInterface->isTestLiveEmail($user->email);
        dispatch(new PostLeadsToCake($user_id, $recordStatus));
        return json_encode(['status' => 'Success']);
    }
}
