<?php

require_once(__DIR__ . '/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$token = $_ENV['TOKEN'];
$openAiApi = $_ENV['OPENAI_API'];
$WhatsAppToken = $_ENV['WHATSAPP_TOKEN'];
$api_version = $_ENV['API_VERSION'];
$phoneid = $_ENV['PHONE_ID'];

if(isset($_REQUEST['hub_challenge'])){
    $challenge = $_REQUEST['hub_challenge'];
}
if(isset($_REQUEST['hub_verify_token'])){
    $verify_token = $_REQUEST['hub_verify_token'];
    
if ($verify_token === $token) {
    echo $challenge;
    }

}








$payload = file_get_contents('php://input');


if(empty($payload)){
	$payload = '{"object":"whatsapp_business_account","entry":[{"id":"105208215998830","changes":[{"value":{"messaging_product":"whatsapp","metadata":{"display_phone_number":"15550620876","phone_number_id":"115719724934135"},"contacts":[{"profile":{"name":"----"},"wa_id":"917018601747"}],"messages":[{"from":"917018601747","id":"wamid.HBgMOTE3MDE4NjAxNzQ3FQIAEhggRkMzM0Y3MTIyNkIzQ0NEQ0RGNDk4RDcyNzAyNkI4RjgA","timestamp":"1693809528","text":{"body":"who is Sachin Tendulkar"},"type":"text"}]},"field":"messages"}]}]}';
}


$decode = json_decode($payload,true);
//echo '<pre>';
//print_r($decode);
//echo '</pre>';

//die;
$ownerno = $decode['entry'][0]['changes']['0']['value']['metadata']['display_phone_number'];
$username = $decode['entry'][0]['changes']['0']['value']['contacts'][0]['profile']['name'];
$userno = $decode['entry'][0]['changes']['0']['value']['messages'][0]['from'];
$usermessage = $decode['entry'][0]['changes']['0']['value']['messages'][0]['text']['body'];

echo $ownerno;
echo $username;
echo $usermessage;

//send message to openai 
	
$ar = array(
//'prompt' => 'My name is '.$username.' and my question is '.$usermessage,
"messages" => [["role"=> "user", "content"=>' My name is '.$username.' and my question is '.$usermessage]],
'model' => 'gpt-4',
'temperature' => 0.6,
'max_tokens' => 150,
'top_p' => 1,
'frequency_penalty' => 1,
'presence_penalty' => 1,
);

$data = json_encode($ar);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,"https://api.openai.com/v1/chat/completions");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
	$data);

	$headers = array();
	$headers[] = 'Content-Type: application/json';
	$headers[] = 'Authorization:Bearer '.$openAiApi;
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


	$result = curl_exec($ch);


	curl_close($ch);

	$openapirespose = json_decode($result,true);


echo "<pre>";
print_r($openapirespose);

	$finalresponse = $openapirespose['choices'][0]['message']['content'];








try {

	/// sending message back to user ///
// Set your access token and API version

//$finalresponse = "message recived ";
// Set the endpoint URL and request payload
$endpoint = "https://graph.facebook.com/{$api_version}/115719724934135/messages";
$data = array(
    'messaging_product' => 'whatsapp',
    'to' => $userno,
    'text' => array(
        'body' => $finalresponse
    )
);

// Set the cURL options and execute the request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer {$WhatsAppToken}",
    "Content-Type: application/json"
));
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Output the response
echo $response;
	
	
	
}

catch (Exception $e) {
  //display custom message
  echo $e->getMessage()
  ;
}


$myfile = fopen("response.txt", "w") or die("Unable to open file!");
fwrite($myfile, $payload);
fclose($myfile);






?>