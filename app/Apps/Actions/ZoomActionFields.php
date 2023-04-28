<?php

namespace App\Apps\Actions;

use App\Apps\Zoom;
use App\Http\Controllers\Api\Apps\Manager;
use App\Logic\Helpers;
use App\Models\Account;

class ZoomActionFields
{
    function __construct($accountId = 0)
    {
        $this->mainClass = new Zoom();
        if ($accountId != 0) {
            $this->account = Account::find($accountId);
            $this->mainClass = new Zoom();
            $this->userId = $this->mainClass->getUserId();
            $this->access_token = $this->mainClass->getToken($accountId);
        }
    }

    public function create_meeting($data)
    {
        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Zoom;
        $accessToken = $drive->getToken($actionAccount);

        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        $form = [];

        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "topic/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Topic",
            'labelId' => "topic",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "topic",
            'labelName' => "Topic",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "description/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Description",
            'labelId' => "description",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "description",
            'labelName' => "Description",
        ])->render();



        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "timezone/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Timezone",
            'labelId' => "timezone",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "timezone",
            'labelName' => "Timezone",
        ])->render();

        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "duration/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Duration",
            'labelId' => "duration",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "duration",
            'labelName' => "Duration",
        ])->render();







        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "start_time/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Start Time",
            'labelId' => "start_time",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "start_time",
            'labelName' => "Start Time",
        ])->render();




        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_meeting_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Zoom;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['topic'])) {
            $params = [
                'topic' => Helpers::stringorexplode($mainData['topic']),
                'timezone' => Helpers::stringorexplode($mainData['timezone']),
                'description' => Helpers::stringorexplode($mainData['description']),
                'duration' => Helpers::stringorsingle($mainData['duration']),
                'start_time' => Helpers::stringorsingle($mainData['start_time']),
            ];
            $this->mainClass->createMeeting($this->access_token, $params);

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }

    public function create_meeting_registrant($data)
    {
        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Zoom;
        $accessToken = $drive->getToken($actionAccount);

        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        $form = [];

        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "email/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Email",
            'labelId' => "email",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "email",
            'labelName' => "Email",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "first_name/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "First Name",
            'labelId' => "first_name",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "first_name",
            'labelName' => "First Name",
        ])->render();



        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "last_name/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Last Name",
            'labelId' => "last_name",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "last_name",
            'labelName' => "Last Name",
        ])->render();

        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "phone/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Phone",
            'labelId' => "phone",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "phone",
            'labelName' => "Phone",
        ])->render();







        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "meeting_id/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Meeting Id",
            'labelId' => "meeting_id",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "meeting_id",
            'labelName' => "Meeting Id",
        ])->render();




        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_meeting_registrant_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Zoom;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['email'])) {
            $params = [
                'email' => Helpers::stringorsingle($mainData['email']),
                'first_name' => Helpers::stringorsingle($mainData['first_name']),
                'last_name' => Helpers::stringorsingle($mainData['last_name']),
                'phone' => Helpers::stringorsingle($mainData['phone']),
                'meeting_id' => Helpers::stringorsingle($mainData['meeting_id']),
            ];
            $this->mainClass->createMeetingRegistant($this->access_token, $params);

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }
}
