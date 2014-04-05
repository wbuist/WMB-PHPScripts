<?php
// This script connects an infusionsoft application to redbooth and creates a task
//
require_once("isdk.php");
$app = new isdk;
$app->cfgcon("YOURAPPNAME");

// you need to have two date variables as user defined fields - this job records that an action has taken place on the record plus sets a next review date - both in infusionsoft.
$LastCon = '_LastActionDate';
$nextCon = '_ContactAfter'; 

// set the contacts contact ID so data can be read to add to the redbooth task
$contactId = (int)$_POST['Id'];
// set the redbooth title
$title = $_POST['Title'];
// set the redbooth description
$description = $_POST['Description'];
// set the assignment - You need to know the persons redbooth id for the specific project, and its ID and the specific task list too;
$redbooth_assignedid = $_POST['Assigned'];
$comment = 'Sent from Infusionsoft';
$redbooth_projectid = $_POST['Project'];
$redbooth_tasklistid = $_POST['Tasklist'];

// get today's date
$currentDate = date("Ymd\TH:i:s");
// set the task to be due (in this case) on the next day
$redbooth_due_on =Date('Y-m-d', strtotime("+1 day"));
// add a week for the next contact date in infusionsoft (or some other variable time
$currentDate3 =Date('y:m:d', strtotime("+1 week"));

// now here's a function to create the task

function createTask($title, $description)
    {
        global $redbooth_projectid, $redbooth_tasklistid, $redbooth_assignedid, $redbooth_due_on;
        
        $comment= array(
                        'body' => $description
        );
    
        $args = array(
                        'name' => $title,
                        'project_id' => $redbooth_projectid,
                        'task_list_id' => $redbooth_tasklistid,
                        'assigned_id' => $redbooth_assignedid,
                        'due_on' => $redbooth_due_on,
                        'comments_attributes' => array($comment)
        );
    
        $projectid=$redbooth_projectid;
        $tasklistid=$redbooth_tasklistid;
    
        $data = connectionHelper('tasks', $args, 'POST');
    
        return $data;
    }

// and one to connect to redbooth - You need to add your account and password in here.
    
function connectionHelper($urlpart, $args, $requestType)
    {
        $redbooth_username = 'YOUR ACCOUNT';
        $redbooth_password = 'YOUR PASSWORD';
        $baseurl = 'https://redbooth.com/api/2/';
        $args = json_encode($args);
        $url = $baseurl . $urlpart;
        $ch = curl_init();

        switch($requestType)
        {
            case 'POST':
                echo "POSTED to : ";
                echo $url;
                echo "<br/>";
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
            default:
                echo "not supported";
        }

        $username= $redbooth_username;
        $password= $redbooth_password;

        curl_setopt($ch, CURLOPT_POSTFIELDS, $args );
        echo $args;

        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $mydata = curl_exec ($ch);
        echo $mydata;

        if (curl_errno($ch))
        {
            $err_str = 'Failed to retrieve url [' . curl_error($ch) . ']' . "\n";

            echo $err_str;

            return false;
        }
        return $mydata;
    }
    
// now run the functions

createtask($title,$description);    

// Update the record in Infusionsoft
$returnFields = array($LastCon,$nextCon);
// get the data first
$contacts = $app->dsLoad("Contact", $contactId, $returnFields);
// add out dates to the array
$conDat = array($LastCon => $currentDate, $nextCon => $currentDate3);
// commit the update
$conID = $app->updateCon($contactId,$conDat);

// all done.
    
?>