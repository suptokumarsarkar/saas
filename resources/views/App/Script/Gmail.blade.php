<script>
    function getPersonalStatementGmail(data) {
        return "<h6>" + data.name + "</h6><i style='font-size: 12px'>" + data.email + "</i>";
    }

    let yxGmail = 0;

    function connectAccountGmail() {
        yxGmail = 0;
        regGmail();
    }

    function connectAccountActionGmail() {
        yxGmail = 1;
        regGmail();
    }

    function checkTriggerGmail(trigger, accountId, AppId = "Gmail") {
        $.ajax({
            url: '{{route('checkTrigger')}}',
            type: 'POST',
            data: 'trigger=' + trigger + "&accountId=" + accountId + "&AppId=" + AppId,
            beforeSend: function () {
                $(".moni_rh_567_summary_disbled_btn").html('{{\App\Logic\translate('Checking Trigger...')}}');
            },
            success: function (data) {
                $(".moni_rh_567_summary_disbled_btn").html('{{\App\Logic\translate('Rerun Trigger')}}');
                console.log(data);
                if (data.status === 200) {
                    displaySuccessToaster(data.message);
                    $(".triggerBlock").attr("open", true);
                    $(".triggerBlock").slideDown("slow");
                    $(".app_trigger_comment").html(data.data.view.view);
                    $(".app_trigger_comment").append(data.data.view.script);
                    scroll_to_id('.triggerBlock', 20)
                } else {
                    displayErrorToaster(data.message);
                }
            },
            error: function () {
                $(".moni_rh_567_summary_disbled_btn").html('{{\App\Logic\translate('Rerun Trigger')}}');
                displayErrorToaster('{{\App\Logic\translate('Something went wrong')}}');
            }
        });
    }


    function checkActionGmail(trigger, action) {
        let data = {
            trigger: trigger,
            action: action
        }
        $.ajax({
            url: '{{route('getActionForm')}}',
            type: 'POST',
            data: $.param(data),
            beforeSend: function () {
                $(".enable_now_action").html('{{\App\Logic\translate('Creating Action Manager...')}}');
            },
            success: function (data) {
                $(".enable_now_action").html('{{\App\Logic\translate('Change Action Manager')}}');
                if (data.status === 200) {
                    displaySuccessToaster(data.message);
                    $(".actionBlock").attr("open", true);
                    $(".actionBlock").show("slow");
                    $(".app_action_comment").html(data.view);
                    $(".app_action_comment_script").html(data.script);
                    scroll_to_id('.actionBlock', 20)
                } else {
                    displayErrorToaster(data.message);
                }
            },
            error: function () {
                $(".enable_now_action").html('{{\App\Logic\translate('Run Action Manager')}}');
                displayErrorToaster('{{\App\Logic\translate('Something went wrong')}}');
            }
        });
    }


</script>
@php
    $settings = new \App\Logic\Settings;
    $googleClientId = $settings->get('googleClientId');
@endphp
<script src="https://accounts.google.com/gsi/client">
</script>
<script>
    function regGmail() {
        const clientGmail = google.accounts.oauth2.initCodeClient({
            client_id: '{{$googleClientId}}',
            scope: 'https://www.googleapis.com/auth/userinfo.email \
          https://www.googleapis.com/auth/userinfo.profile\
          https://www.googleapis.com/auth/gmail.modify\
          https://www.googleapis.com/auth/gmail.labels\
          https://www.googleapis.com/auth/gmail.compose\
          https://www.googleapis.com/auth/gmail.send\
          https://www.googleapis.com/auth/gmail.insert\
             ',
            ux_mode: 'popup',
            callback: (response) => {
                response.type = 'Gmail';
                $.ajax({
                    url: '{{route('googleToken')}}',
                    type: 'post',
                    data: response,
                    success: function (data) {
                        data = JSON.parse(data);
                        console.log(data);
                        if (data.status === 200) {
                            displaySuccessToaster(data.message);
                            if (yxGmail) {
                                getAccountsAction('Gmail');
                            } else {
                                getAccounts('Gmail');
                            }
                        } else {
                            displayErrorToaster(data.message);
                        }
                    }
                })
            },
        });
        clientGmail.requestCode();
    }
</script>
