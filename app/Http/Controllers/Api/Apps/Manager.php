<?php

namespace App\Http\Controllers\Api\Apps;

use App\Apps\AppInfo;
use App\Http\Controllers\Controller;
use App\Logic\Helpers;
use App\Models\Account;
use App\Models\AppsData;
use App\Models\Zap;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use function App\Logic\translate;

class Manager extends Controller
{
    /**
     * @var mixed
     */
    private $funcData;
    /**
     * @var mixed
     */
    private $appClass;

    public function getExtraDataFields($id, $app, Request $request): array
    {
        $data = $request->get("vk");
        $value = Helpers::evaluteData($request->all());
        $appData = json_decode($request->get("dataAction"), true);
        if ($appData['Mode'] == 'Triggers') {
            $appClassHere = "App\\Apps\\Triggers\\" . $appData['AppId'] . 'Trigger';
            $this->appClass = new $appClassHere($appData['AccountId']);
        } else {
            $appClassHere = "App\\Apps\\Actions\\" . $appData['AppId'] . 'ActionFields';
            $this->appClass = new $appClassHere($appData['AccountId']);
        }
        $fnc = $appData['Func'];
        return $this->appClass->$fnc($id, $value, $data);


    }

    public function addApp(Request $request)
    {
        $account = new Account;
        $appId = $request['AppId'];
        $cls = "App\\Apps\\" . $appId;
        $this->appClass = new $cls();
        $account->accountId = Auth::id();
        $tokenGroup = $this->appClass->addAccount($request->all());
        $profile = $this->appClass->getProfile($request->all(), json_decode($tokenGroup, true));
        $account->token = $tokenGroup;
        $account->data = $profile;
        $account->type = $request->AppId;
        if ($account->save()) {
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Account Added Successfully.'),
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate("Something went wrong. Please try again.")
            ]);
        }
    }

    public function getApps(Request $request)
    {

        if ($request->json) {
            $appData = AppsData::get();
            foreach ($appData as $key => $app) {
                $appInfo = $app->AppInfo;
                $appInfoArray = json_decode($appInfo, true);
                if ($appInfoArray['status'] == 0) {
                    unset($appData[$key]);
                }
            }
            foreach ($appData as $app) {
                $app->AppLogo = $app->getLogo();
                $appClassHere = "App\\Apps\\" . $app->AppId;
                $appClass = new $appClassHere();
                if (isset($appClass->dataOptionAction)) {
                    $app->dataOptionAction = false;
                } else {
                    $app->dataOptionAction = true;
                }
                if (isset($appClass->dataOptionTrigger)) {
                    $app->dataOptionTrigger = false;
                } else {
                    $app->dataOptionTrigger = true;
                }
            }

            // Copy to new $var
            $rx = [];
            foreach ($appData as $app) {
                $rx[] = $app;
            }
            return json_encode($rx, true);
        }
        return AppsData::get();
    }

    public function createApp(Request $request)
    {
        $app = new AppsData;
        $app->AppId = $request->AppId;
        $app->AppName = $request->AppName;
        $app->AppDescription = $request->AppDescription;
        $app->AppLogo = $request->AppLogo;
        $app->AppInfo = json_encode([], 1);
        $app->save();
        return json_encode([
            'status' => 200,
            'message' => Helpers::translate("App Created Successfully")
        ]);
    }

    public function getApp(Request $request)
    {
        $app = new AppInfo($request->AppId);
        $app->app->getActions = $app->getActions(true);
        $app->app->getLogo = $app->getLogo();
        $app->app->getTriggers = $app->getTriggers(true);
        $app->app->script = view('App.Script.' . $request->AppId)->render();

        return json_encode($app->app, true);
    }

    public function userInfo($accountId)
    {
        $account = Account::find($accountId);
        if ($account) {
            $data = json_decode($account->data, true);
            $account->data = $data;
            $token = json_decode($account->token, true);
            $account->token = $token;
            return $account;
        }
        return false;
    }

    public function checkTrigger(Request $request)
    {
        $account = Account::find($request->accountId);
        $appClassHere = "App\\Apps\\" . $request->AppId;
        $this->appClass = new $appClassHere;
        $tokens = json_decode($account->token, 1);

        if ($this->appClass->checkAccount($request->accountId)) {
            return new Response([
                'status' => 200,
                'data' => $this->appClass->getCheckupData($request->accountId, $request->trigger),
                'message' => Helpers::translate('Account Detected Successfully.')
            ]);
        } else {
            return new Response([
                'status' => 400,
                'message' => Helpers::translate('Account Is Not Valid. Please Try Adding Account Again or Select Another Account.')
            ]);
        }

    }

    public function getActionForm(Request $request)
    {

        $appClassHere = "App\\Apps\\Actions\\" . $request->action['AppId'] . "ActionFields";
        $this->appClass = new $appClassHere;
        $actionId = $request->action['action_id'];
        return $this->appClass->$actionId($request->all());
    }

    public function publishZap(Request $request)
    {
        $value = Helpers::evaluteData($request->all());
        $data = $request->data;
        $data = json_decode($data, true);

        $zap = new Zap;
        $zap->userId = Auth::id();
        $zap->zapData = json_encode($data, true);


        $triggerAction = $data['trigger']['action_id'];
        $triggerAccount = $data['trigger']['account_id'];
        $triggerApp = $data['trigger']['AppId'];
        $triggerApp = "App\\Apps\\Triggers\\" . $triggerApp . "Trigger";
        $trigger = new $triggerApp($triggerAccount);
        $triggerActionFunc = $triggerAction . "_update_database";
        $datav1 = json_decode($request->data, true);
        if (isset($datav1['trigger']['Data'])) {
            $funcData = $trigger->$triggerActionFunc($datav1['trigger']['Data']);
        } else {
            $funcData = $trigger->$triggerActionFunc();
        }


        $zap->database = json_encode($funcData, true);
        $zap->func = json_encode($value, true);
        if ($zap->save()) {
            $secret = [$zap->id, 'Zap Published'];
            return json_encode([
                "status" => 200,
                "message" => Helpers::translate('Zap Created'),
                "url" => route('Apps.zapsDetails', $secret)
            ]);
        } else {
            return json_encode([
                "status" => 400,
                "message" => Helpers::translate('Failed to save zap'),
            ]);
        }
    }

    public function createZap(Request $request)
    {
        $value = Helpers::evaluteData($request->all());
        $data = $request->data;
        $data = json_decode($data, true);
        $funcData = [];
        $mainData = [];
        if (isset($value['api'])) {
            $triggerAction = $data['trigger']['action_id'];
            $triggerAccount = $data['trigger']['account_id'];
            $triggerApp = $data['trigger']['AppId'];
            $triggerApp = "App\\Apps\\Triggers\\" . $triggerApp . "Trigger";
            $trigger = new $triggerApp($triggerAccount);
            $triggerActionFunc = $triggerAction . "_check";
            $datav1 = json_decode($request->data, true);
            if (isset($datav1['trigger']['Data'])) {
                $funcData = $trigger->$triggerActionFunc($datav1['trigger']['Data']);
            } else {
                $funcData = $trigger->$triggerActionFunc();
            }
            // Check The Api Actions
            $funcData = $this->freshArrayApi($funcData);
            if (!$this->checkApiMatch($value['api'], $funcData)) {
                return json_encode([
                    'status' => 400,
                    'message' => Helpers::translate("API Key Mismatch. Request Not Valid")
                ]);
            }
            $mainData = $this->dataFillup($value['api'], $funcData);
        }
        Helpers::stringValueFillup($mainData, $value);
        return $this->runZap($mainData, $data, Auth::id());


    }

    public function checkApiMatch(array $value, $funcData)
    {
        $ilt = 1;
        foreach ($value as $item) {
            if (is_array($item)) {
                if (!$this->checkApiMatch($item, $funcData)) {
                    $ilt = 0;
                }
            } else {
                if (!array_key_exists($item, $funcData)) {
                    $ilt = 0;
                }
            }
        }
        return $ilt;
    }

    public function dataFillup(array $value, $funcData)
    {
        $mainData = [];
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                foreach ($item as $id => $mainKey) {
                    if (array_key_exists($mainKey, $funcData)) {
                        if (isset($funcData[$mainKey]) && isset($funcData[$mainKey]['FileHandler'])) {
                            $mainData[$key][] = $funcData[$mainKey];
                        } elseif (isset($mainData[$key]) && is_array($mainData[$key])) {
                            $mainData[$key][] = $funcData[$mainKey];
                        } elseif (isset($mainData[$key]) && is_string($mainData[$key])) {
                            $prevData = $mainData[$key];
                            $mainData[$key] = [$prevData, $funcData[$mainKey]];
                        } else {
                            $mainData[$key] = $funcData[$mainKey];
                        }
                    }
                }
            } else {
                if (array_key_exists($item, $funcData)) {
                    if (isset($funcData[$item]) && isset($funcData[$item]['FileHandler'])) {
                        $mainData[$key][] = $funcData[$item];
                    } elseif (isset($mainData[$key]) && is_array($mainData[$key])) {
                        $mainData[$key][] = $funcData[$item];
                    } elseif (isset($mainData[$key]) && is_string($mainData[$key])) {
                        $prevData = $mainData[$key];
                        $mainData[$key] = [$prevData, $funcData[$item]];
                    } else {
                        $mainData[$key] = $funcData[$item];
                    }
                }
            }
        }

        return $mainData;
    }

    public function freshArrayApi($funcData)
    {
        $array = [];
        if (is_array($funcData)) {
            foreach ($funcData as $keys => $value) {
                if (is_array($value)) {
                    if (!in_array($keys, $this->dataPureArraySetup())) {
                        foreach ($value as $key => $data) {
                            $array[$key] = $data;
                        }
                    } else {
                        $array[$keys] = $value;
                    }

                } else {
                    $array[$keys] = $value;
                }
            }
        }
        return $array;
    }


    public function runZap($mainData, $data, $userId)
    {
        $accAction = $data['action']['action_id'];
        $accAccount = $data['action']['account_id'];
        $accApp = $data['action']['AppId'];
        $accApp = "App\\Apps\\Actions\\" . $accApp . "ActionFields";
        $action = new $accApp($accAccount);
        $accActionFunc = $accAction . "_post";
        return $funcData = $action->$accActionFunc($accAccount, $mainData, $data);

    }

    public function hasZaps($userId): bool
    {
        return true;
    }

    public function getTriggerValue($data)
    {
        $triggerAction = $data['trigger']['action_id'];
        $triggerAccount = $data['trigger']['account_id'];
        $triggerApp = $data['trigger']['AppId'];
        $triggerApp = "App\\Apps\\Triggers\\" . $triggerApp . "Trigger";
        $trigger = new $triggerApp($triggerAccount);
        $triggerActionFunc = $triggerAction . "_check";
        $funcData = $trigger->$triggerActionFunc($data['trigger']['Data'] ?? null);
        $this->funcData = $funcData;
        return $this->funcData;
    }

    public function freshArray($array = null): array
    {
        if ($array === null) {
            $array = $this->funcData;
        }
        if (is_array($array)) {
            $mainData = [];
            foreach ($array as $key => $item) {
                if (is_array($item)) {
                    $data = $this->freshArray($item);
                    foreach ($data as $key01 => $value) {
                        $mainData[$key01] = $value;
                    }
                } else {
                    $mainData[$key] = $item;
                }
            }
            return $mainData;
        } else {
            return [
                $array
            ];
        }
    }

    public function freshArrayParents($array = null, $parent = ""): array
    {
        if ($array === null) {
            $array = $this->funcData;
        }
        if (is_array($array)) {
            $mainData = [];
            foreach ($array as $key => $item) {
                if (is_array($item)) {
                    if (is_numeric($key)) {
                        $love = "";
                    } else {
                        $love = $key;
                    }
                    $data = $this->freshArrayParents($item, $love);
                    foreach ($data as $key01 => $value) {
                        $mainData[$parent . " " . $key01] = $value;
                    }
                } else {
                    $mainData[$parent . " " . $key] = $item;
                }
            }
            return $mainData;
        } else {
            return [
                $array
            ];
        }
    }

    private function dataPureArraySetup(): array
    {
        return ['Attendees'];
    }
}
