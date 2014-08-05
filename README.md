This is an unofficial PHP wrapper for accessing Coding Avenue Timeclock data programmtically.

Example Usage:
    $api = new Clock\Timeclock();
    $is_successful = $api->authenticate($email, $password);
    if ($is_successful) {
        $punch_history = $api->getPunchHistory();
        $work_days = $punch_history->getWorkDays();
    }

Check the source code for more details :P
