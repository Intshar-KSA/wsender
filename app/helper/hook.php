<?php
include "index.php";

echo "this is web hook";
include "env.php";
include "get_last_msg.php";

/*
$servername = "localhost";
$username = "root";
$password = "";
$database = "gstm";

*/

//echo getUserById( $_SESSION['current_user_id'] )['name'];

$data = file_get_contents("php://input");

$event = json_decode($data, true);
if(isset($event)){
    //Here, you now have event and can process them how you like e.g Add to the database or generate a response
    $file = 'log.txt';
    $data =json_encode($event)."\n";
    //file_put_contents($file, $data, FILE_APPEND | LOCK_EX);

    //print_r($data);

    $chat_id = $event['messages'][0]['chatId'];
    $message = $event['messages'][0]['body'];
    $profile_id = $event['messages'][0]['profile_id'];
    $is_me = $event['messages'][0]['is_me'];
    $from_user = $event['messages'][0]['from'];
    $to_user = $event['messages'][0]['to'];

    if($message=="chat_id"){
        $result = send_with_wapi('40703bb7812b727ec01c24f2da518c407342559c', 'aedd0dc2-8453',$chat_id, $chat_id);
    }

    $userInfo= getUserByUserProfile($profile_id);
    if ($userInfo !== null) {
        $user_name= $userInfo['name'];
        $token= $userInfo['token'];
        $sheet_url= $userInfo['sheet_url'];
        $sheet_id=getSheetIdFromUrl($sheet_url);

        if(!$is_me) {
            get_sheet_msgs($sheet_id,$user_name,$profile_id,$token,$sheet_url,$message,$chat_id );
        }

        if($is_me && $from_user==$to_user) {
            get_sheet_msgs($sheet_id,$user_name,$profile_id,$token,$sheet_url,$message,$chat_id );
        }

    } else {
        echo "User not found.";
    }
}

function send_with_wapi($auth, $profileId, $phone, $message) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://ads.2moh.net/wapi2024/',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => 'auth='.$auth.'&profile_id='.$profileId.'&phone='.$phone.'&msg='.$message.'',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: 40703bb7812b727ec01c24f2da518c407342559c'
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
}

function getUserByUserProfile($profile_id) {
    // Database connection details
    global $servername;
    global $username ;
    global $password;
    global $database ;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the SQL query
    $stmt = $conn->prepare("SELECT * FROM users WHERE profile_id = ?");
    $stmt->bind_param("i", $profile_id);

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there is a result
    if ($result->num_rows > 0) {
        // Fetch the user data
        $user = $result->fetch_assoc();
        return $user;
    } else {
        return null; // Return null if no user is found
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

function getSheetIdFromUrl($url) {
    $parts = parse_url($url);
    $path = explode('/', $parts['path']);
    $id = $path[3];
    return $id;
}

function get_sheet_msgs($sheet_id,$user_name,$profile_id,$token,$sheet_url,$message,$chat_id )
{
    //$result = send_with_wapi($token, $profile_id, $chat_id ,'form sheet');

    $url = 'https://opensheet.elk.sh/'.$sheet_id.'/Sheet1';

    try {
        $response = file_get_contents($url);

        if ($response !== false) {
            // Parse the response as JSON

            $data = json_decode($response, true);

            //$first_object = reset($data);

            $first_replay="";
            foreach ($data as $row) {

                $b = str_replace("\n", "\\n", $row['b']);
                //$b = str_replace(' ', '', $b);
                $first_replay = $b;

                break;
            }

            $restart_bot=get_last_message_date_if_more_than_one_hour($profile_id,$token,$chat_id);

            //&& strpos($chat_id,"@c.us") !== false
            if($restart_bot && strpos($chat_id,"@c.us") !== false ){
                //$result = send_with_wapi($token, $profile_id, $chat_id , 'started');
                //$result = send_with_wapi($token, $profile_id, $chat_id , 'started');
                //$result = send_with_wapi($token, $profile_id, $chat_id , 'started');
                try{
                    $result = send_with_wapi($token, $profile_id, $chat_id , $first_replay."" );
                }catch(Exception $e){
                    $result = send_with_wapi($token, $profile_id, $chat_id ,$e->getMessage()."eception" );
                }
                //$result = send_with_wapi($token, $profile_id, $chat_id , 'started 5');
            } else {
                //$result = send_with_wapi($token, $profile_id, $chat_id ,"not started".$restart_bot);
            }

            if ($data !== null) {
                // Loop through the rows and display key-value pairs
                foreach ($data as $row) {
                    $a = $row['a'];
                    $b = $row['b'];

                    $a = str_replace("\n", "\\n", $a);
                    $b = str_replace("\n", "\\n", $b);

                    echo $a."<br>";
                    echo $b."<br>";

                    $a = str_replace(' ', '', $a);
                    $message = str_replace(' ', '', $message);

                    if($message==$a){
                        //$result = send_with_wapi('40703bb7812b727ec01c24f2da518c407342559c', 'aedd0dc2-8453', '966568430828@c.us',$b);
                        $result = send_with_wapi($token, $profile_id, $chat_id ,$b);
                        //echo print_r($result) ;
                    }
                }
            } else {
                // Handle JSON parsing error
                throw new Exception('Failed to parse the JSON response.');
            }
        } else {
            // Handle error
            throw new Exception('Failed to retrieve the response.');
        }
    } catch (Exception $e) {
        // Handle exceptions
        //echo 'Error: ' . $e->getMessage();
    }
}
?>
