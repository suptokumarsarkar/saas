<?php

namespace App\Http\Controllers\Api\Apps;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AppsData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectorLightNit extends Controller
{
    public function getAccounts(Request $request)
    {
        $accounts = Account::where("accountId", Auth::id())->where("type", $request->AppId)->get();
        foreach ($accounts as $key => $account)
        {
            $app_data = AppsData::where('AppId', $account->type)->first();
            $logo = $app_data->getLogo();
            $accounts[$key]->logo = $logo;
        }
        return json_encode($accounts);
    }
}
