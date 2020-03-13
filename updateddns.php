<?php 
/* 
Author: Ariyanki
Version: v1.2

Step:
- Put this code in your web hosting. 
- Go to Advanced DNS Zone Editor in your webhosting cpanel and Add Record DNS Zone for initiation: (this code is not include add DNS Zone event in my code to reduce the process)
--- Name:<subdomain>.<domain>.<com/net/id/org/etc>
--- TTL:7200
--- Type:A
--- Address: your server IP
- hit that code from your server with scheduler every 10 minutes or up to you. This is the crontab script example:
10 * * * * /usr/bin/wget -q "http://<domain>/<updateddnsfilename>.php"
*/

$cpanelUrl="www.cpanel.com";
$cpanelPort="2083";
$cpanelUser="username";
$cpanelPassword="password";
$domain="yourdomain.com";
$subdomain="subdomain";
$newIP=$_SERVER['REMOTE_ADDR'];
$output=updateddns();
//print_r($output);

function updateddns(){
	global $cpanelUrl,$cpanelPort,$cpanelUser,$cpanelPassword,$domain,$subdomain,$newIP;
	$records = doquery("fetchzone", array());
	$line="";
	foreach ($records['cpanelresult']['data'][0]['record'] as $val) {
		if ($val['type'] == "A"){
			if(preg_match('/'.$subdomain.'.'.$domain.'/',$val["name"])){
				$line=$val['Line'];
				$ip=$val['record'];
				break;
			
			}
		}
	}
	if(!empty($line) and !empty($newIP) and $ip!=$newIP){												
		$params = array(	'Line' => $line,
						"type"		=> "A",
						"name"		=> $subdomain,
						"address"	=> $newIP,
						"ttl"		=> "7200",
						"class"		=> "IN");
		$records = doquery("edit_zone_record",$params);
		echo "success";
	}else{
		echo "failed.";
	}
	return $records;			
}

function doquery($function,$params){
	global $cpanelUrl,$cpanelPort,$cpanelUser,$cpanelPassword,$domain;
	$curl = curl_init();
	$query = "https://".$cpanelUrl.":".$cpanelPort."/json-api/cpanel?cpanel_jsonapi_module=ZoneEdit&cpanel_jsonapi_func=".$function."&cpanel_jsonapi_version=2&domain=".$domain."&".http_build_query($params);
	$headers[] = "Authorization: Basic ".base64_encode($cpanelUser.":".$cpanelPassword);
	$options = array(	CURLOPT_URL				=> $query,
					CURLOPT_SSL_VERIFYPEER 	=> 0,		//Allow self-signed cert :P
					CURLOPT_SSL_VERIFYHOST 	=> 0,		//Allow cert hostname mismatch
					CURLOPT_HEADER			=> 0,		//Output: Header not included
					CURLOPT_RETURNTRANSFER	=> 1,		//Output: Contents included
					CURLOPT_HTTPHEADER		=> $headers	//Auth
					);
	curl_setopt_array($curl, $options);
	$result = curl_exec($curl);

	if ($result === false) { throw new Exception("cURL Execution Error ".curl_error($curl)." in $query", 0); } //error handling for failure
	curl_close($curl);
	return json_decode($result, true);
}

 ?>
 