<script>
    function getPersonalStatementZoom(data) {
        return "<h6>" + data.first_name + "</h6><i style='font-size: 12px'>" + data.email + "</i>";
    }

    let yxZoom = 0;

    function connectAccountZoom() {
        yxZoom = 0;
        regZoom();

    }

    function connectAccountActionZoom() {
        yxZoom = 1;
        regZoom();
    }

    function checkTriggerZoom(trigger, accountId, AppId = "Zoom") {
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


    function checkActionZoom(trigger, action) {
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
    $settings = new \App\Apps\Zoom;
    $googleClientId = $settings->client_id;
@endphp

<script>

    let authWindow;


    function createOauthWindow(width = 500, height = 600) {

        const clientId = '{{$googleClientId}}';
        const redirectUri = 'http%3A%2F%2Flocalhost%2Fsaas%2Foauth%2Fzoom';
        const responseType = 'code';
        const scope = 'user:read:admin user:read user:write group:read group:write meeting:write meeting:write:admin';

        const url = `https://zoom.us/oauth/authorize?client_id=${clientId}&redirect_uri=${redirectUri}&scope=${scope}&response_type=${responseType}`;

        const left = (screen.width / 2) - (width / 2);
        const top = (screen.height / 2) - (height / 2);
        const options = `directories=no, titlebar=no, toolbar=no, location=no, status=no, menubar=no, scrollbars=no, resizable=no,
         copyhistory=no, width=${width},height=${height},left=${left},top=${top}`;

        authWindow = window.open(url, 'popup', options);

        const checkPopup = setInterval(() => {
            if (authWindow.window.location.href
                .match(decodeURIComponent(redirectUri))) {
                let data = JSON.parse(authWindow.document.getElementById('token').value);
                authWindow.close();

                let response = {
                    'AppId': 'Zoom',
                    'token': data.data,
                };
                $.ajax({
                    url: '{{route('AddApp')}}',
                    type: 'post',
                    data: response,
                    success: function (data) {
                        data = JSON.parse(data);
                        if (data.status === 200) {
                            displaySuccessToaster(data.message);
                            if (yxZoom) {
                                getAccountsAction('Zoom');
                            } else {
                                getAccounts('Zoom');
                            }
                        } else {
                            displayErrorToaster(data.message);
                        }
                    }
                })



            }
            if (!authWindow || !authWindow.closed) return;
            clearInterval(checkPopup);
        }, 1000);


    }

    function regZoom() {
        createOauthWindow();
    }

</script>
