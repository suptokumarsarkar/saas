<?php

namespace App\Apps;

use App\Apps\Triggers\ZoomTrigger;
use App\Logic\Helpers;
use App\Models\Account;
use App\Models\AppsData;

class Zoom
{

//    private $client_id = '68667692288-4bkagbc71fb2k0c9dr481ip9ib22iffv.apps.googleusercontent.com';
//    private $client_secret = "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";

    /**
     * @var mixed
     */
    public $client_id;
    /**
     * @var mixed
     */
    public $client_secret;

    public function __construct()
    {
        $gmail = AppsData::where("AppId", "Zoom")->first();
        if ($gmail) {
            $appInfo = json_decode($gmail->AppInfo, true);
            $this->client_id = $appInfo['client_id'];
            $this->client_secret = $appInfo['client_secret'];
        }
    }

    public function addAccount($data = [])
    {
        return json_encode($this->getAccessToken($data['token']['code']), true);
    }

    public function getProfile($data = [], $tokenGroup = [])
    {
        $access_token = $tokenGroup['access_token'];

        return json_encode($this->getMember($access_token), true);

    }


    public function getTrigger(): array
    {
        return array(
            [
                'id' => 'new_meeting',
                'name' => Helpers::translate('New Meeting'),
                'description' => Helpers::translate('Triggers when a new Meeting or Webinar is created.')
            ],
            [
                'id' => 'new_meeting_registrant',
                'name' => Helpers::translate('New Meeting Registrant'),
                'description' => Helpers::translate('Triggers when a new registrant is added to a meeting.')
            ],
            [
                'id' => 'new_recording',
                'name' => Helpers::translate('New Recording'),
                'description' => Helpers::translate('Triggers when a new Recording is completed for a Meeting or Webinar.')
            ]
        );
    }

    public function getActions(): array
    {
        return array(
            [
                'id' => 'create_meeting',
                'name' => Helpers::translate('Create Meeting'),
                'description' => Helpers::translate('Creates a new meeting.')
            ],
            [
                'id' => 'create_meeting_registrant',
                'name' => Helpers::translate('Create Meeting Registrant'),
                'description' => Helpers::translate('Creates a new meeting registrant.')
            ],
        );
    }


    public function getToken($id)
    {
        $getProfile = Account::find($id);
        $token = json_decode($getProfile->token, true);
        return $token['access_token'];
    }

    public function getUserId($id = null)
    {
        if ($id == null) return 'me';
        $getProfile = Account::find($id);
        $token = json_decode($getProfile->data, true);
        return $token['id'];
    }

    public function getCheckupData($accountId, $labelId)
    {
        $getProfile = Account::find($accountId);
        $data = json_decode($getProfile->data, true);
        $access_token = $this->getToken($accountId);
        $trigger = new ZoomTrigger($accountId);
        return [
            'access_token' => $access_token,
            'view' => $trigger->$labelId(),
        ];
    }

    public function checkAccount($id)
    {
        if ($access_token = $this->getToken($id)) {
            $url = "https://www.googleapis.com/oauth2/v3/userinfo?access_token=" . $access_token;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $profile = json_decode($response, 1);

            if (isset($profile) && count($profile) != 0) {
                return true;
            }
        }
        return false;
    }

    public function fileManagerInstance($accountId)
    {
        return [$this->getToken($accountId)];
    }


    // Default API
    public function getMember($access_token)
    {
        $accessToken = $access_token;
        $apiUrl = 'https://api.zoom.us/v2/users';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $accessToken,
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true)['users'][0];

    }

    public function getAccessToken($code)
    {

        $redirectUri = 'http://localhost/saas/oauth/zoom';

        $url = 'https://zoom.us/oauth/token';

        $data = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri
        );

        $basicAuth = base64_encode($this->client_id . ':' . $this->client_secret);

        $headers = array(
            'Authorization: Basic ' . $basicAuth
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        // Step 4: Use the access token to make Zoom API requests
        return json_decode($response, true);
    }

    public function getMeetings($access_token, $param)
    {


        $url = 'https://api.zoom.us/v2/users/me/meetings';

        $headers = array(
            "Authorization: Bearer {$access_token}",
            "Content-Type: application/json"
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return json_decode($response, true)['meetings'];
    }


    // Boards Api
    public function createMeeting($access_token, $param)
    {
        $url = 'https://api.zoom.us/v2/users/me/meetings';

        $data = array(
            'topic' => $param['topic'],
            'type' => 2,
            'start_time' => $param['start_time'],
            'duration' => $param['duration'],
            'timezone' => $param['timezone'],
            'agenda' => $param['description']
        );

        $headers = array(
            "Authorization: Bearer {$access_token}",
            "Content-Type: application/json"
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        return json_decode($response, true);
    }

    public function getMeetingRegistant($access_token, $param)
    {
        $meetingId = 'YOUR_MEETING_ID';

        $url = "https://api.zoom.us/v2/meetings/{$param['meeting_id']}/registrants";

        $headers = array(
            "Authorization: Bearer {$access_token}",
            "Content-Type: application/json"
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);

    }


    public function createMeetingRegistant($accessToken, $param)
    {
        $meetingId = $param['meeting_id'];

        $data = array(
            'email' => $param['email'],
            'first_name' => $param['first_name'],
            'last_name' => $param['last_name'],
            'phone' => $param['phone']
        );

        $url = "https://api.zoom.us/v2/meetings/{$meetingId}/registrants";

        $headers = array(
            "Authorization: Bearer {$accessToken}",
            "Content-Type: application/json"
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        return json_decode($response, true);

    }

    public function listRecordings($accessToken)
    {
        $url = "https://api.zoom.us/v2/users/me/recordings";

        $headers = array(
            "Authorization: Bearer {$accessToken}",
            "Content-Type: application/json"
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        return $this->extractRecordingFiles(json_decode($response, true));

    }

    function extractRecordingFiles($responseArray)
    {
        $recordingFiles = array();
        if (!isset($responseArray["recording_files"])) {
            $recordingFile = array();
            $recordingFile["id"] = rand();
            $recordingFile["file_type"] = rand();
            $recordingFile["download_url"] = rand();
            $recordingFile["play_url"] = rand();
            $recordingFile["join_url"] = rand();
            $recordingFile["duration"] = rand();
            $recordingFiles[] = $recordingFile;
        } else {


            foreach ($responseArray["recording_files"] as $file) {
                $recordingFile = array();
                $recordingFile["id"] = $file["id"];
                $recordingFile["file_type"] = $file["file_type"];
                $recordingFile["download_url"] = $file["download_url"];
                $recordingFile["play_url"] = $file["play_url"];
                $recordingFile["join_url"] = $file["recording_url"];
                $recordingFile["duration"] = $file["duration"];
                $recordingFiles[] = $recordingFile;
            }
        }
        return $recordingFiles;
    }
}
