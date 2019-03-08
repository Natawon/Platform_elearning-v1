<p style="margin-bottom: 2em;">Something is wrong in the job:</p>
<p>Server : {{config('constants.PROJECT_INFO.TITLE')}} e-Learning ({{ $site }})</p>
<p>URL : {{ $url }}</p>
<p>Job Code : {{ $dataCronJob->code }}</p>
@if ($dataCronJob->status == 0)
    <p>Error Code : {{ $dataCronJob->status_code }}</p>
    <p>Error Detail : {{ $dataCronJob->status_remark }}</p>
@else
    <p>Error Detail : The job is not running.</p>
@endif
<p>Date : {{ $dataCronJob->action_datetime }}</p>

