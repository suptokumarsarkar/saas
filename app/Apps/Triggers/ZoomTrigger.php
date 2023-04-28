<?php

namespace App\Apps\Triggers;

use App\Apps\Zoom;
use App\Http\Controllers\Api\Apps\Manager;
use App\Logic\Helpers;
use App\Models\Account;
use PHPUnit\TextUI\Help;

class ZoomTrigger
{
    private $account;
    /**
     * @var Zoom
     */
    private $mainClass;
    /**
     * @var mixed
     */
    private $access_token;

    function __construct($accountId)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Zoom;
        $this->userId = $this->mainClass->getUserId();
        $this->access_token = $this->mainClass->getToken($accountId);
    }

    public function new_recording()
    {
        return [
            'view' => Helpers::maxNote(Helpers::translate('Will track your recordings and trigger while a new record finds.')),
            'script' => '',
            'message' => Helpers::translate('Connected With Zoom'),
            'status' => 200,
        ];
    }

    public function new_recording_check($data = [])
    {
        $files = $this->mainClass->listRecordings($this->access_token, []);
        $dataString['string'] = $files[count($files) - 1];
        return $dataString;
    }


    public function new_recording_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];

        $files = $this->mainClass->listRecordings($this->access_token, []);
        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['id']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['id'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $files[] = $latest;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function new_recording_update_database($data = null)
    {
        $files = $this->mainClass->listRecordings($this->access_token, []);
        return ['Files' => $files];
    }







    public function new_meeting()
    {
        return [
            'view' => Helpers::maxNote(Helpers::translate('Will track your meetings and trigger while a new meeting find.')),
            'script' => '',
            'message' => Helpers::translate('Connected With Zoom'),
            'status' => 200,
        ];
    }

    public function new_meeting_check($data = [])
    {
        $files = $this->mainClass->getMeetings($this->access_token, []);
        $dataString['string'] = $files[count($files) - 1];
        return $dataString;
    }


    public function new_meeting_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];

        $files = $this->mainClass->getMeetings($this->access_token, []);
        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['id']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['id'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $files[] = $latest;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function new_meeting_update_database($data = null)
    {
        $files = $this->mainClass->getMeetings($this->access_token, []);
        return ['Files' => $files];
    }

    public function new_meeting_registrant()
    {

        $form = [];

        // Custom Data
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'meeting_id',
            'label' => Helpers::translate('Meeting Id'),
            'required' => false
        ])->render();

        $scripts = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelName' => Helpers::translate('Meeting Id'),
            'labelId' => 'meeting_id',
        ])->render();
        $views[] = $view;


        $view = Helpers::rap_with_form($views, [], 'triggerForm');

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }


    public function new_meeting_registrant_check($data = [])
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $files = $this->mainClass->getMeetingRegistant($this->access_token, ['meeting_id' => $value['string']['meeting_id'][0]]);
        $dataString['string'] = $files[count($files) - 1];
        return $dataString;
    }


    public function new_meeting_registrant_changes($zapDatabase = [], $zapData = [])
    {
        $zapData = json_decode($zapData, true);
        $zapDatabase = json_decode($zapDatabase, true);

        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);

        $files = $this->mainClass->getMeetingRegistant($this->access_token, ['meeting_id' => $value['string']['meeting_id'][0]]);
        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['id']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['id'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $files[] = $latest;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function new_meeting_registrant_update_database($data = null)
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $files = $this->mainClass->getMeetingRegistant($this->access_token, ['meeting_id' => $value['string']['meeting_id'][0]]);
        return ['Files' => $files];
    }


}
