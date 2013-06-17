<?php

function get_string_between($string, $start, $end){
	$string = " ".$string;
	$ini = strpos($string,$start);
	if ($ini == 0) return "";
	$ini += strlen($start);
	$len = strpos($string,$end,$ini) - $ini;
	return substr($string,$ini,$len);
}

function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
{
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('handleError');

function getServerInfo($store, $instance, $port)
{
	$url = 'https://'.$store.'-'.$instance.'.us.gspt.net:'. $port . '/is-bin/INTERSHOP.enfinity/WFS/SLDSystem';
	if (strpos($instance, 'tst') > 0) {
		$portDesc = 'live';
		if($port == '1444') $portDesc = 'edit';
		$url = 'https://'.$store.'-'.$instance. '-' . $portDesc . '.us.gspt.net' . '/is-bin/INTERSHOP.enfinity/WFS/SLDSystem';	
	} 

	$output = shell_exec('curl -k -f  -m 1 ' . $url);
	$output = strip_tags($output); 
	$enfinityVersion = get_string_between($output, "6&nbsp;-&nbsp;", "]");
	$webstoreVersion =  get_string_between(get_string_between($output, 'GSI Webstore', ']'), 'GSI Webstore', '[');
	if(strlen($enfinityVersion) > 1) {
		$status = 'alive';
	} else {
		$enfinityVersion = 'N/A';
		$webstoreVersion = 'N/A';
		$status = 'down';
	}
	$serverInfo = array("enfinityVersion" => $enfinityVersion, 
				 "webstoreVersion" => $webstoreVersion,
				 "status" => $status,
				 "managerURL" => $url);
	return $serverInfo;

}

function getBambooBuildInfoDetail($project) {
	$username = 'ampUser';
	$password = 'ampUser2011';
	try {	
		$url = 'http://bamboo.tools.us.gspt.net/rest/api/latest/build/OPSV11VERSION/'.$project.'.json?os_username='. $username . '&os_password=' . $password;
		$response = file_get_contents ($url);
		$array = json_decode($response, true);
		$builds = $array['builds']['build'][0];
		$latestbuild = $builds['number'];
		$project = 'OPSV11VERSION-' . $project . '-' . $latestbuild;
		$url = 'http://bamboo.tools.us.gspt.net/rest/api/latest/build/'. $project . '.json?os_username='. $username . '&os_password=' . $password;
		

		$response = file_get_contents ($url);
		$array = json_decode($response, true);
		$buildInfoDetail = array("buildRelativeTime" => $array['buildRelativeTime'], 
					 "buildCompletedTime" => $array['buildCompletedTime'],
					 "buildNumber" => $array['number'],
					 "buildState" => $array['state'],
					 "buildURL" => 'http://bamboo.tools.us.gspt.net/browse/' . $project);
	} catch (Exception $e)	 {
		$buildInfoDetail = array("buildRelativeTime" => 'n/a', 
					 "buildCompletedTime" => 'n/a',
					 "buildNumber" => 'n/a',
					 "buildState" => 'n/a',
					 "buildURL" => 'n/a');
	}

	return $buildInfoDetail;

}

function getJiraInfo($jira, $gec, $instance, $app) {
	$client = new SoapClient($jira['wsdl']);
	// var_dump($client->__getFunctions());
	try {
	    $token = $client->login($jira['username'], $jira['password']);
		$issueArray = $client->getIssuesFromJqlSearch($token, "project = RM and resolution = UNRESOLVED and environment is not empty and component in (\"".$app."\") and component in (\"".strtoupper($gec)."\")  and environment ~ \"".$instance."\" order by due desc", 1);

    } catch (SoapFault $fault) {
	    echo "Error logging in to JIRA";
	    print_r($fault);
	}
	return $issueArray;
}
?>