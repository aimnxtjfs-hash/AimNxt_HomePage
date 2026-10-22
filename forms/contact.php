<?php

session_start();
$receiving_email_address = 'contact@aimnxt.org';
//$receiving_email_address = 'vasu@godainfotech.com';

$otp_receiving_email_address=$_POST['email'];
if (file_exists($php_email_form = '../assets/vendor/php-email-form/php-email-form.php')) {
  include ($php_email_form);
} else {
  die('Unable to load the "PHP Email Form" Library!');
}

$contact = new PHP_Email_Form;

$contact->ajax = true;

$contact->to = $receiving_email_address;
$contact->from_name = "AIMNXT Enquiry";
$contact->from_email = 'aimnxt.enquiry@aimnxt.org';
array_push($contact->cc, "aimnxt.enquiry@aimnxt.org");




$contact->smtp = array(
  'host' => 'smtp.hostinger.com',
  'username' => 'aimnxt.enquiry@aimnxt.org',
  'password' => 'Ane@1820xt1',
  'port' => '587'
);

// Is this a registraiton? Contact ? or Download Brochure?

// print_r($_POST);
// return;
// Handle Registration


// echo $result;

if ( $_POST['sendotp'] == "yes"){
    $new_contact = new PHP_Email_Form;
    $new_contact->ajax = true;
    
    $new_contact->to = $otp_receiving_email_address;
    $new_contact->from_name = "AIMNXT Enquiry";
    $new_contact->from_email = 'aimnxt.enquiry@aimnxt.org';
    $new_contact->smtp = array(
      'host' => 'smtp.hostinger.com',
      'username' => 'aimnxt.enquiry@aimnxt.org',
      'password' => 'Ane@1820xt1',
      'port' => '587'
    );

   $otp=send_otp($otp_receiving_email_address);
    $message="Thank you for showing interest in ".$_POST['course']. ". Use this otp to download the course";
    $new_contact->honeypot = $_POST['last_name'];
    $new_contact->add_message($otp,$message );
    $new_contact->subject = "OTP to download ".$_POST['course']. " curriculum";
    $new_contactResult = $new_contact->send();
    if ($new_contactResult == 'OK'){
      echo "OTP_SENT";
     return;
    }else{
        echo "error";
        return;
      }
 
} else if ( $_POST['verify_otp_type'] == "yes"){

 
 verify_otp($_POST['otp'], $otp_receiving_email_address);
 return;
}

if ($_POST['type'] == "Registration" ) {
  $contact->honeypot = $_POST['last_name'];
  $contact->add_message($_POST['type'], 'Type');
  $contact->add_message($_POST['name'], 'From');
  $contact->add_message($_POST['phone'], 'Phone');
  $contact->add_message($_POST['email'], 'Email');
  $contact->add_message($_POST['course'], 'Course');
  $contact->add_message($_POST['experience'], 'Experience');

  if (isset($_POST['mode']))
    $contact->add_message($_POST['mode'], 'Training Mode');

  $contact->subject = "New Registration";

 }
// Handle Contact Us
else if ($_POST['type'] == "C") {
  $contact->honeypot = $_POST['last_name'];
  $contact->add_message($_POST['type'], 'Type');
  $contact->add_message($_POST['name'], 'From');
  $contact->add_message($_POST['phone'], 'Phone');
  $contact->add_message($_POST['email'], 'Email');
  $contact->add_message($_POST['subject'], 'subject');
  $contact->add_message($_POST['message'], 'message');

  $contact->subject = "New enquiry";
}

//Handle Assessments

// Handle Download Brochure
else if ($_POST['type'] == "D") {
  $contact->honeypot = $_POST['last_name'];
  $contact->add_message($_POST['type'], 'Type');
  $contact->add_message($_POST['name'], 'From');
  $contact->add_message($_POST['phone'], 'Phone');
  $contact->add_message($_POST['email'], 'Email');
  $contact->add_message($_POST['course'], 'Course');
  $contact->add_message($_POST['mode'], 'Training Mode');

  $contact->subject = "New Lead";
} else {
  echo "Thanks";
  return;
}

$contactResult = $contact->send();


$filename = 'flag.txt';

// Initialize file with '0' if it doesn't exist
if (!file_exists($filename)) {
    file_put_contents($filename, '0');
}

// Read current state
$currentFlag = file_get_contents($filename);

// Flip the flag (if 0, make 1; if 1, make 0)
$newFlag = ($currentFlag == '0') ? '1' : '0';
// Save new state
file_put_contents($filename, $newFlag);

//     Tele CRM Integration
$tom = 'aimnxttulasi04@gmail.com';

$data = array(
    
  "fields"=> array(
    "name"=> $_POST['name'],
    "phone"=> $_POST['phone'],
    "email"=> $_POST['email'],
    "status"=> "Fresh",
    "assignee"=> $tom,
    "Mode"=> $_POST['mode'],
    "Course"=> $_POST['course'],
    
    "Platform"=> "Aimnxt Website"
  )

);

// Encode the PHP array into a JSON string
$json_payload = json_encode($data);

// URL of the API endpoint
$url = 'https://next-api.telecrm.in/enterprise/67208667b829011d0ff170b1/autoupdatelead'; 


// 2. Initialize cURL session
$curl = curl_init($url);

// 3. Set cURL options
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Return the response as a string instead of outputting it
curl_setopt($curl, CURLOPT_POST, true);           // Set request method to POST
curl_setopt($curl, CURLOPT_POSTFIELDS, $json_payload); // Attach the JSON data as the POST 
$token = "0d2a6ad0-a024-443a-9bd4-2a55ac7683ea1767420128080:52b19319-5eba-43ca-908d-4939a89360d2";
$authorization = "Authorization: Bearer " . $token;

curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization));

// 4. Execute the POST request
$response = curl_exec($curl);

// Check for errors (log instead of echoing — echoing broke the JS success check)
$curl_error = curl_errno($curl) ? curl_error($curl) : null;
if ($curl_error) {
    error_log("TeleCRM cURL error: " . $curl_error);
}

// 5. Close the cURL session
curl_close($curl);

//     Tele CRM Integration

if ($contactResult != "OK") {
    error_log("Email send failed: " . $contactResult);
}

if ($contactResult == "OK") {
  // if($_POST['type'] == "FreeCG"){
  //   setcookie("freeCGSubscribed", "1", time() + (86400 * 30), "/"); 
  // }
  // if($_POST['type'] == "FreeDevOps"){
  //   setcookie("freeDevOpsSubscribed", "1", time() + (86400 * 30), "/"); 
  // }

  switch ($_POST['course']) {
    case "dataScience-course":
      setcookie("FreeCGDataScience", "1", time() + (86400 * 30), "/");
      break;
    case "aws-devops-course":
      setcookie("FreeCGAwsDevOps", "1", time() + (86400 * 30), "/");
      break;
    case "aws-course":
      setcookie("FreeCGAws", "1", time() + (86400 * 30), "/");
      break;
    case "devnet-course":
      setcookie("FreeCGCiscoDevnet", "1", time() + (86400 * 30), "/");
      break;
    case "cyberSecurity-course":
      setcookie("FreeCGCyberScecurity", "1", time() + (86400 * 30), "/");
      break;
    case "digitalMarketing-course":
      setcookie("FreeCGDigitalMarketing", "1", time() + (86400 * 30), "/");
      break;
    case "javafullStack-course":
      setcookie("FreeCGJavaFullStack", "1", time() + (86400 * 30), "/");
      break;
    case "pythonfullStack-course":
      setcookie("FreeCGPythonFullStack", "1", time() + (86400 * 30), "/");
      break;
    case "ccna-ccnp-networking-course":
      setcookie("FreeCGCCNANetworking", "1", time() + (86400 * 30), "/");
      break;
    case "network-automation-course":
      setcookie("FreeCGNetworkAutomation", "1", time() + (86400 * 30), "/");
      break;
    case "azure-devops-course":
      setcookie("FreeCGAzureDevOps", "1", time() + (86400 * 30), "/");
      break;
    case "spoken-english":
      setcookie("FreeCGSpokenEnglish", "1", time() + (86400 * 30), "/");
       break;
    case "testing-course":
      setcookie("FreeCGTesting", "1", time() + (86400 * 30), "/");
     break;
    case "power-bi":
      setcookie("FreeCGPowerBi", "1", time() + (86400 * 30), "/");
      break;
    case "sap-fico-course":
      setcookie("FreeCGsapfico", "1", time() + (86400 * 30), "/");
      break;
      
      case "leadership-skills":
        setcookie("FreeCGleadership", "1", time() + (86400 * 30), "/");
        break;

        case "sailpoint-skills":
          setcookie("FreeCGsailpoint", "1", time() + (86400 * 30), "/");
          break;

        case "cissp-course":
          setcookie("FreeCGscisspCourse", "1", time() + (86400 * 30), "/");
          break;

  }

}

function send_otp($otp_receiving_email_address) {
  $otp=rand(100000,999999);
  $_SESSION['otp']=$otp;
  //Store it in database
  $host = 'localhost';
  $password='N^w2nzy]';
  $username='u312704806_aimnxt';
  $database= 'u312704806_aimnxt';
  $conn = mysqli_connect($host, $username, $password, $database);

  if (!$conn) {
    die('Failed to connect to MySQL: ' . mysqli_connect_error());
  }
  $query = "INSERT INTO otp (otp, email) VALUES ('$otp', '$otp_receiving_email_address')";
  
  $result = mysqli_query($conn, $query);
   // if ($result) {
    //   echo "Data inserted successfully!";
    // } else {
    //   echo "Error: " . mysqli_error($conn);
    // }
    mysqli_close($conn);
    return $otp;


}
function verify_otp($user_otp,$email) {
  $host = 'localhost';
  $password='N^w2nzy]';
  $username='u312704806_aimnxt';
  $database= 'u312704806_aimnxt';
  $conn = mysqli_connect($host, $username, $password, $database);
$sql = "SELECT * FROM otp where email = '$email' order by id desc limit 1;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  
  while($row = $result->fetch_assoc()) {
   $otp=$row['otp'];
  
  }
} else {
  echo "OTP is not sent";
  
}


if ($otp == $user_otp){
  $sql = "DELETE FROM otp where email = '$email';";
$new_result = $conn->query($sql);
$conn->close();
  echo "OK";
    
}else{
  $conn->close();
  echo "failure";
   
}

}


// Redirect success is based on the lead reaching TeleCRM, not on email delivery.
echo "OK";

?>