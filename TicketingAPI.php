/*

API for a ticketing system on a Datatable developed in PHP
and MySQL, using JSON messages

addresses, account names and sensitive informations
have been censored with the word "[censored]"

*/

<?php
$request = $_REQUEST;

require_once __DIR__ . '[censored]';

global $emn_params;

$projectObj = new [censored]($emn_params);

header('Content-Type: application/json');

// get tickets, set filters and send data to Datatable
if ($request['action'] == 'getTickets') {

    $perPage = 10;

    $start = $request['start'] ? $request['start'] : 0;
    $length = $request['length'] ? $request['length'] : $perPage;
    $draw = $request['draw'] ? $request['draw'] : '1';
    $order = $request['order'] ? $request['order'] : array();
    $columns = $request['columns'];
    $customFilters = $request['customFilters'] ? $request['customFilters'] : array();
    $role = $emn_params->user_rights['role_name'];
    $username = $emn_params->user;
    $status = $request['status'];

    $dags = $projectObj->dagSwitcherData();
    $dags_id = array();

    foreach($dags as $x => $x_value) {
        if($x == $emn_params->user_rights['username']){
            for($i=0;$i<count($x_value);$i++){
                array_push($dags_id,$x_value[$i]);
            }
        }
    }

    $orderField = null;
    $orderType = null;

    if (!empty($order)) { // type of order
        $orderField = $columns[$order[0]['column']]['data'];
        $orderType = $order[0]['dir'];
    }

    if (!empty($customFilters)) { // status => open, close, etc... from customFilters to whereChunk
        $whereChunk = array();
        foreach ($customFilters as $k => $v) {
            if (!empty($v)) {
                if($v == 'Opened_InProgress' || $customFilters['status'] == null){
                    $where = ' AND (tk.status = "Opened" OR tk.status = "InProgress")';
                    if(!empty($customFilters['ticket_category'])){ // category set
                        $cat = $customFilters["ticket_category"];
                        $where = $where." AND tk.ticket_category = '$cat' ";
                    }
                }
                else{
                    $whereChunk[] = "tk.$k = '$v'";
                    $where = ' AND ' . implode(" AND ", $whereChunk);
                }
            }
        }
    }
    else $where = ' AND (tk.status = "Opened" OR tk.status = "InProgress")'; // default filter

    $orderQuery = '';
    if ($orderField) { // type of order
        $orderQuery = " ORDER BY tk.{$orderField} $orderType";
    }

    if($length != -1) $limit = "LIMIT {$start}, {$length}"; // n records per page
    else $limit = ''; // all records

    $hide_tec_record = ''; $roleRecords = '';
    if($role != '[censored]' && $role != '[censored]' && $username != '[censored]')
        $roleRecords = ' AND ro.role_name = '."'$role'";

    if(strpos($role, '[censored]') == true) $roleRecords = " AND role_name LIKE 'Lab%'";

    if($role == '[censored]' || $role != '[censored]' || $role != '[censored]' || $username != [censored])
        $hide_tec_record = 'AND tk1.ticket_type <> "Tecnical"';

    $projectid = 'AND tk.project_id = '.$emn_params->project_id;

    if($role == '[censored]'){
        $q = "SELECT tk.ticket_id, tk.status, ro.role_name as requester, inf.username as username,
                     tk.ticket_category, tk.ticket_title, tk.ui_id,tk.content, tk.filename, tk.insert_at
                FROM [censored] AS tk
                LEFT JOIN [censored] as inf ON tk.ui_id = inf.ui_id
                LEFT JOIN [censored] as ri ON ri.username = inf.username
                LEFT JOIN [censored] as ro ON ro.role_id = ri.role_id
                WHERE tk.ticket_type = 'Request'
                AND (ro.role_name = '[censored]' OR ro.role_name = '[censored]')
                OR tk.ticket_id IN (SELECT tk1.ticket_id
                                    FROM [censored] as tk1
                                    WHERE tk1.ticket_type = 'Tecnical')
                AND tk.ticket_type = 'Request'
                {$projectid}
                {$roleRecords}
                {$where}
                {$orderQuery}
                {$limit}";
    }
    else{ // all request

        $filter_by_dags = '';

        if(count($dags_id) == 1 ){ // multiDAG
            $dags_id = json_encode($dags_id);
            $filter_by_dags = "AND JSON_CONTAINS(tk.dags, '$dags_id')";
        }
        else if(count($dags_id) > 1 ){ // single DAG
            $dags = array();
            $dags = explode(",",$dags_id);
            $filter_by_dags = implode(" OR ", $dags);
        }

        $q = "SELECT tk.ticket_id, tk.status, ro.role_name as requester, inf.username as username, tk.ticket_category, tk.ticket_title as ticket_title, 
                    tk.ui_id, tk.dags, tk.content, tk.filename, tk.insert_at
                FROM [censored] AS tk
                LEFT JOIN [censored] as inf ON tk.ui_id = inf.ui_id
                LEFT JOIN [censored] as ri ON ri.username = inf.username
                LEFT JOIN [censored] as ro ON ro.role_id = ri.role_id
                WHERE tk.ticket_type = 'Request'
                {$projectid}
                {$roleRecords}
                {$where}
                {$filter_by_dags}
                {$orderQuery}
                {$limit}";
    }

    $ret = $conn->query($q)->fetch_all(MYSQLI_ASSOC);

    if(!empty($ret)){ // if at least one record exists, do "last answerer" query

        $arr = array();
        $ticket_id_numbers = '';
        for($i=0;$i<count($ret);$i++){ // get tickets ID (numbers) to compose final data for Datatable
            array_push($arr,$ret[$i]['ticket_id']);
            $ticket_id_numbers = implode(',',$arr);
        }

        if($role == '[censored]' || $role == '[censored]') $get_name = "IF(tk.ticket_number > 0, inf.username, null)";
        else $get_name = "IF(tk.ticket_number > 0, ro.role_name, null)";

        $last_answerer = "SELECT tk.ticket_id, {$get_name} as answerer, tk.content as content_r
                            FROM [censored] as tk
                            INNER JOIN (
                                SELECT tk1.ticket_id, MAX(tk1.ticket_number) as max_number
                                FROM [censored] as tk1
                                WHERE tk1.ticket_id IN ({$ticket_id_numbers})
                                {$hide_tec_record}
                                GROUP BY tk1.ticket_id
                            ) as es
                            ON tk.ticket_id = es.ticket_id
                            AND tk.ticket_number = es.max_number
                            LEFT JOIN [censored] as inf ON tk.ui_id = inf.ui_id
                            LEFT JOIN [censored] as ri ON ri.username = inf.username
                            LEFT JOIN [censored] as ro ON ro.role_id = ri.role_id
                            WHERE tk.ticket_id IN ({$ticket_id_numbers})
                            {$projectid}
                            {$orderQuery}";

        header("x-query-debug: ".str_replace("\n", "", $last_answerer));

        $ret_ls = $conn->query($last_answerer)->fetch_all(MYSQLI_ASSOC);

        for($i=0;$i<count($ret);$i++){ // compose return(ret) array
            $ret[$i]['answerer'] = $ret_ls[$i]['answerer'];
            $ret[$i]['content_r'] = htmlspecialchars_decode($ret_ls[$i]['content_r']);
            $ret[$i]['content'] = htmlspecialchars_decode($ret[$i]['content']);
            $ret[$i]['ticket_title'] = htmlspecialchars_decode($ret[$i]['ticket_title']);
        }
        
        $in = "AND tk.ticket_id IN ({$ticket_id_numbers})";

        if($role == '[censored]' || $username == [censored]) $in = ''; // filter records

        $sql = "SELECT count(*) AS tot
                FROM [censored] AS tk
                LEFT JOIN [censored] AS project ON project.project_id = tk.project_id
                LEFT JOIN [censored] AS user ON user.ui_id = tk.ui_id
                WHERE tk.ticket_type = 'Request'
                {$in}
                {$where}
                {$projectid}";
        
        $qCount = $conn->prepare($sql); // get filtered records number

        $qCount->execute();
        $retCount = $qCount->get_result()->fetch_all(MYSQLI_ASSOC);
        $qCount->close();

        $tot = $retCount[0]['tot'];

    }
    else $tot = 0;

    echo json_encode(
        array(
            "draw" => (int)$draw,
            "recordsTotal" => $tot,
            "recordsFiltered" => $tot,
            "data" => $ret,
            "customFilters" => $customFilters,
            "start" => (int)$start,
            "length" => (int)$length,
            "perPage" => (int)$perPage,
            "role" => $role,
            "username" => $username,
            "dags_id" => $dags_id,
            "where" => $where,
            "customFilters" => $customFilters,
            "status" => $status
        )
    );
}


//REQUEST - RESPONSE
if ($request['action'] == 'addTickets') {

    $pid = $emn_params->project_id;
    $role = $emn_params->user_rights['role_name'];
    $username = $emn_params->user_rights['username'];
    
    $dags = $projectObj->dagSwitcherData();
    $dags_id = array();

    foreach($dags as $x => $x_value) {
        if($x == $emn_params->user_rights['username']){
            for($i=0;$i<count($x_value);$i++){
                array_push($dags_id,$x_value[$i]);
            }
        }
    }

    $dags_id = json_encode($dags_id);

    $projectName = $emn_params->Proj->project['app_title'];
    $category = $_POST['category'];
    $category_text = $_POST['category_text'];
    $title = htmlspecialchars($_POST['title']);
    $content = htmlspecialchars($_POST['content']);
    $ticket_type = $_POST['ticket_type'];
    if($_POST['ticket_type'] == 'Response' || $_POST['ticket_type'] == 'Tecnical') $ticket_id = $_POST['ticket_id']; //null => request, !null => response
    else $ticket_id = 0; // new request

    if($_FILES['file'] != null) $file = $_FILES['file']['name'];
    else $file = null;

    $currentDateTime = date('Y-m-d H:i:s');

    $getCurrentID = $conn->prepare("SELECT ui_id from [censored] where username = ?");
    $getCurrentID->bind_param('s',$username);
    $getCurrentID->execute();
    $result_getCurrentID = $getCurrentID->get_result();
    $row_cI = $result_getCurrentID->fetch_array(MYSQLI_ASSOC);
    $result_getCurrentID->close();
    $getCurrentID->close();

    $currentID = $row_cI['ui_id']; // numeric ID of who is logged

    if($ticket_type === 'Request'){
        $getLastRequestID = $conn->prepare("SELECT MAX(ticket_id) as ticket_id FROM [censored] WHERE project_id = ? AND ticket_type = ?");

        $getLastRequestID->bind_param('is',$pid,$ticket_type);
        $getLastRequestID->execute();
        $result_getLastRequestID = $getLastRequestID->get_result();
        $row = $result_getLastRequestID->fetch_array(MYSQLI_ASSOC);
        $result_getLastRequestID->close();
        $getLastRequestID->close();
        $ticket_id = $row['ticket_id']; //first id => null
        if($ticket_id >=0){ // null == 0
            $ticket_id = ++$ticket_id;
            $ticket_number = 0;
        }
    }

    if($ticket_type === 'Response' || $ticket_type === 'Tecnical'){ //get last ticket ID
        $getLastResponseID = $conn->prepare("SELECT MAX(ticket_number) as ticket_number FROM [censored] WHERE project_id = ? AND ticket_id = ?");
        $getLastResponseID->bind_param('ii',$pid, $ticket_id);
        $getLastResponseID->execute();
        $result_getLastResponseID = $getLastResponseID->get_result();
        $row = $result_getLastResponseID->fetch_array(MYSQLI_ASSOC);
        $result_getLastResponseID->close();
        $getLastResponseID->close();
        $ticket_number = $row['ticket_number'];

        if($ticket_number >=0){
            $ticket_number = ++$ticket_number;
        }
    }

    // URL in which upload files
    $url = [censored];
    if (!file_exists($url)) { // dir create if it doesn't exists
        $relative_path = 'uploads';
        mkdir(dirname(__FILE__) . "/" . $relative_path);
    }

    if($file != null && file_exists($url.$file)){// unlucky case: already exists a file with the actual file name => add ticket_id+ticket_number at the end of file name
        $point = strrpos($file,'.'); // cut file name at "."
        $file_pre = substr($file,0,$point); // file name
        $file_post = $file_pre.$ticket_id.$ticket_number; // new file name with ticket_id+ticket_number
        $ext = substr($file,$point); // file extension with "." (ex.: .jpeg)
        $file = $file_post.$ext;
    }
    $uploaded = move_uploaded_file($_FILES['file']['tmp_name'], $url.$file);

    if ($uploaded == false || 0 < $_FILES['file']['error'] ) $err = $_FILES['file']['error'];

    try {
        if($ticket_type == 'Response'){
            $role = $emn_params->user_rights['role_name'];

            if($role == '[censored]' || $role == '[censored]')
                $status_t = 'InProgress';
            else $status_t = 'Opened';

            $progressUpdate = $conn->prepare("UPDATE [censored] SET status = ? WHERE ticket_id = ? AND project_id = ?");
            $progressUpdate->bind_param('sii',$status_t,$ticket_id,$pid);
            $progressUpdate->execute();
            $progressUpdate->close();

            list($status, $details) = $dest = $projectObj->sendMail(getMailOpt($conn, $role, $pid, $ticket_type, $projectName ,$title, $currentID, $category_text, $content, $ticket_id, null, null));

            $addTicket = $conn->prepare("INSERT INTO [censored] (project_id, ticket_id, ticket_number, ticket_type, ticket_category, ticket_title, ui_id, dags, content, insert_at, status, filename)
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $addTicket->bind_param('iiisssisssss', $pid, $ticket_id, $ticket_number, $ticket_type, $category, $title, $currentID, $dags_id, $content, $currentDateTime, $status_t, $file);
        }
        
        else if($ticket_type == 'Tecnical' || $ticket_type == 'Request'){

            list($status, $details) = $projectObj->sendMail(getMailOpt($conn, $role, $pid, $ticket_type, $projectName ,$title, $currentID, $category_text, $content, $ticket_id, null, null));

            $addTicket = $conn->prepare("INSERT INTO [censored] (project_id, ticket_id, ticket_number, ticket_type, ticket_category, ticket_title, ui_id, dags, content, insert_at, status, filename)
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Opened', ?)");
            $addTicket->bind_param('iiisssissss', $pid, $ticket_id, $ticket_number, $ticket_type, $category, $title, $currentID, $dags_id, $content, $currentDateTime, $file);
        }

        $addTicket->execute();
        $result_addTicket = $addTicket->get_result();
        $addTicket->close();

    } catch(\Exception $e) {
        $err = $e->getMessage();
    }

    echo json_encode(
        array(
            "status" => 'ok',
            "role" => $role,
            "projectName" => $projectName,
            "category" => $category,
            "title" => $title,
            "content" => $content,
            "ticket_type" => $ticket_type,
            "ticket_id" => $ticket_id,
            "ticket_number" => $ticket_number,
            "current_id" => $currentID,
            "date" => $currentDateTime,
            "file" => $file,
            "mail" => array($status, $details),
            "STATUS1" => $status_t,
            "requester" => $requester,
            "pid" => $pid,
            "dags_id" => $dags_id
        )
    );
}


function getMailOpt($conn, $role, $pid, $ticket_type, $projectName, $title, $currentID, $category_text, $content, $ticket_id, $file, $url){

    $dest = array();

    $type = 'New '.$ticket_type.' on '.$projectName.', Ticket no.: '.$ticket_id;

    if($role == '[censored]' || $role == '[censored]') $role_to = '[censored]';
    else $role_to = '[censored]';

    if($ticket_type == 'Response' || $ticket_type == 'Tecnical'){

        $not_to = ''; 
        if($ticket_type == 'Response' && ($role != '[censored]' && $role != '[censored]' && $role != '[censored]')) // centro
            $not_to = "AND ro.role_name <> '[censored]'";
        else if($ticket_type == 'Tecnical') $not_to = "AND ro.role_name <> 'Center' AND ro.role_name NOT LIKE 'Laboratory%'";

        $user_email = $conn->prepare("SELECT inf.user_email
                                    FROM [censored] as tk
                                    INNER JOIN (
                                        SELECT tk1.ui_id, MAX(tk1.ticket_number) as max_number
                                        FROM [censored] as tk1
                                        GROUP BY tk1.ui_id
                                    ) as es
                                    ON tk.ui_id = es.ui_id
                                    AND tk.ticket_number = es.max_number
                                    LEFT JOIN [censored] as inf ON tk.ui_id = inf.ui_id
                                    LEFT JOIN [censored] as ri ON ri.username = inf.username
                                    LEFT JOIN [censored] as ro ON ro.role_id = ri.role_id
                                    WHERE tk.ui_id <> ?
                                    {$not_to}
                                    AND tk.project_id = ?
                                    AND tk.ticket_id = ?");

        $user_email->bind_param('iii',$currentID,$pid,$ticket_id);
        $user_email->execute();
        $email = $user_email->get_result()->fetch_all(MYSQLI_ASSOC);
        $user_email->close();

        $email = $email[0]['user_email'];

        if($email == '' || $email == null){

            $user_email = $conn->prepare("SELECT inf.user_email
                                        FROM [censored] as inf
                                        LEFT JOIN [censored] as ri ON ri.username = inf.username
                                        LEFT JOIN [censored] as ro ON ro.role_id = ri.role_id
                                        WHERE ro.role_name = ?
                                        AND ro.project_id = ?");

            $user_email->bind_param('si',$role_to,$pid);
            $user_email->execute();
            $email = $user_email->get_result()->fetch_all(MYSQLI_ASSOC);
            $user_email->close();

            $email = $email[0]['user_email'];
        }

        array_push($dest, $email);
    }
    else{
        if($ticket_type == 'Request'){

            $getEmails = $conn->prepare("SELECT inf.user_email
                                        FROM [censored] as inf
                                        LEFT [censored] as ri ON ri.username = inf.username
                                        LEFT [censored] as ro ON ro.role_id = ri.role_id
                                        WHERE ro.role_name = ?
                                        AND ro.project_id = ?");

            $getEmails->bind_param('si',$role_to,$pid);
            $getEmails->execute();
            $receivers = $getEmails->get_result()->fetch_all(MYSQLI_ASSOC);
            $getEmails->close();

            foreach ($receivers as $key => $receivers) {
                array_push($dest, $receivers['user_email']);
            }
        }
    }

    $url1 = [censored].$pid;

    $mailOpt['to'] = $dest;

    $mailOpt['subject'] = $type; //titolo

    $mailTemplateURL = [censored];
    $message = file_get_contents($mailTemplateURL);

    $message = str_replace("{{project}}",$projectName,$message);
    $message = str_replace("{{category}}",$category_text,$message);
    $message = str_replace("{{title}}",$title,$message);
    $message = str_replace("{{message}}",$content,$message);
    $message = str_replace("{{url}}",$url1,$message);

    $mailOpt['textHtml'] = $message;

    if($file != null){
        $mailOpt['attachment']['binary'] = file_get_contents($url.$file);
        $mailOpt['attachment']['name'] = $file;
    }

    return $mailOpt;
}


if ($request['action'] == 'changeStatus') {

    $pid = $emn_params->project_id;
    $role = $emn_params->user_rights['role_name'];
    $status = $request['status'];
    $ticket_id = $request['ticket_id'];
    $ui_id = $request['ui_id'];
    $projectName = $emn_params->Proj->project['app_title'];
    $category = $request['category'];
    $title = $request['title'];
    $content = $request['content'];
    
    if($status == 'Opened' || $status == 'InProgress'){
        $status = 'Closed';

        $dest = array();
        
        // get requester email
        $requester_email_q = $conn->prepare("SELECT inf.user_email
                                            FROM [censored] as inf
                                            LEFT JOIN [censored] as tk ON inf.ui_id = tk.ui_id
                                            WHERE tk.ticket_type = 'Request'
                                            AND tk.project_id = ?
                                            AND tk.ui_id = ?");

        $requester_email_q->bind_param('ii',$pid,$ui_id);
        $requester_email_q->execute();

        $requester_email = $requester_email_q->get_result()->fetch_all(MYSQLI_ASSOC);

        $requester_email_q->close();

        array_push($dest, $requester_email[0]['user_email']);

        // check if record tec exists in a specific tickets flow
        $tec_records_q = $conn->prepare("SELECT tk.project_id,tk.ticket_id, tk.ticket_title
                                        FROM [censored] as tk
                                        WHERE tk.ticket_type = 'Tecnical'
                                        AND tk.ticket_id = ?
                                        AND tk.project_id = ?");
        $tec_records_q->bind_param('ii',$ticket_id,$pid);
        $tec_records_q->execute();
        $tec_records = $tec_records_q->get_result()->fetch_all(MYSQLI_ASSOC);
        $tec_records_q->close();

        $retRequester_q = $conn->prepare("SELECT ro.role_name as requester
                                        FROM [censored] AS tk
                                        LEFT JOIN [censored] as inf ON tk.ui_id = inf.ui_id
                                        LEFT JOIN [censored] as ri ON ri.username = inf.username
                                        LEFT JOIN [censored] as ro ON ro.role_id = ri.role_id
                                        WHERE tk.ticket_type = 'Request'
                                        AND tk.ticket_id = ?
                                        AND tk.project_id = ?");
        $retRequester_q->bind_param('ii',$ticket_id,$pid);
        $retRequester_q->execute();
        $requester = $retRequester_q->get_result()->fetch_all(MYSQLI_ASSOC);
        $retRequester_q->close();

        if(!empty($tec_records) || $requester[0]['requester'] == '[censored]' || $requester[0]['requester'] == '[censored]'){
            $tec_email_q = $conn->prepare("SELECT inf.user_email
                                            FROM [censored] as inf
                                            LEFT JOIN [censored] as ri ON ri.username = inf.username
                                            LEFT JOIN [censored] as ro ON ro.project_id = ri.project_id
                                            WHERE ro.role_name = '[censored]'
                                            AND ro.role_id = ri.role_id
                                            AND ro.project_id = ?");
            $tec_email_q->bind_param('i',$pid);
            $tec_email_q->execute();
            $tec_email = $tec_email_q->get_result()->fetch_all(MYSQLI_ASSOC);
            $tec_email_q->close();

            foreach ($tec_email as $key => $tec_email) {
                array_push($dest, $tec_email['user_email']);
            }
        }


        $url = [censored].$pid;

        $mailOpt['to'] = $dest;
    
        $mailOpt['subject'] = 'Your ticket n° '.$ticket_id.' has been closed';

        $mailTemplateURL = [censored];
        $message = file_get_contents($mailTemplateURL);
    
        $message = str_replace("{{ticket_id}}",$ticket_id,$message);
        $message = str_replace("{{project}}",$projectName,$message);
        $message = str_replace("{{category}}",$category,$message);
        $message = str_replace("{{title}}",$title,$message);
        $message = str_replace("{{message}}",$content,$message);
        $message = str_replace("{{url}}",$url,$message);
    
        $mailOpt['textHtml'] = $message;

        list($status1, $details) = $projectObj->sendMail($mailOpt);
    }

    else if($role == '[censored]' && $status == 'Closed') $status = 'InProgress';
    else $status = 'Opened';

    $chStat = $conn->prepare("UPDATE [censored] SET status = ? WHERE ticket_id = ? AND project_id = ?");
    $chStat->bind_param('sii',$status,$ticket_id,$pid);
    $chStat->execute();
    $chStat->close();

    echo json_encode(
        array(
            "status" => 'ok',
            "ticket_id" => $ticket_id,
            "role" => $role,
            "mail" => array($status1, $details),
            "error" => ''
        )
    );
}


if ($request['action'] == 'getResponse') {

    $pid = $emn_params->project_id;
    $ticket_id = $request['ticket_id'];
    $type = $request['ticket_type'];
    $role = $emn_params->user_rights['role_name'];
    $username = $emn_params->user;

    if($role == '[censored]' || $role == '[censored]' || $role == '[censored]' || $username == '[censored]') // tutti i record
        $roleRecords = 'tk.ticket_type <> "Request"';
    else $roleRecords = 'tk.ticket_type = "Response"';

    $orderby = 'ORDER BY tk.ticket_number DESC';

    $getResponse = $conn->prepare("SELECT tk.ticket_number, tk.ticket_type, tk.insert_at, ro.role_name, tk.content, tk.filename
                                    FROM [censored] AS tk
                                    LEFT JOIN [censored] as inf ON tk.ui_id = inf.ui_id
                                    LEFT JOIN [censored] as ri ON inf.username = ri.username
                                    LEFT JOIN [censored] as ro ON ri.role_id = ro.role_id
                                    WHERE tk.project_id = ? AND tk.ticket_id = ? AND {$roleRecords}
                                    {$orderby}");
    $getResponse->bind_param('ii',$pid,$ticket_id);

    $getResponse->execute();
    $arr = $getResponse->get_result()->fetch_all(MYSQLI_ASSOC);
    $getResponse->close();

    echo json_encode(
        array(
            "status" => 'ok',
            "data" => $arr,
            "error" => ''
        )
    );
}


if ($request['action'] == 'getRequestText') {
    $pid = $emn_params->project_id;
    $ticket_id = $request['ticket_id'];

    $getRequestText = $conn->prepare("SELECT ticket_title, content FROM [censored] WHERE project_id = ? AND ticket_id = ? AND ticket_type = 'Request'");
    $getRequestText->bind_param('ii',$pid,$ticket_id);
    $getRequestText->execute();
    $text = $getRequestText->get_result()->fetch_all(MYSQLI_ASSOC);
    $getRequestText->close();

    echo json_encode(
        array(
            "status" => 'ok',
            "data" => $text,
            "error" => ''
        )
    );
}
