<?php

include 'functions.php';

$properties = parse_ini_file("properties.ini", true);

$gecs = $properties['stores'];
$ports = $properties['ports'];
$jira = $properties['jira'];
$components = $properties['components'];

?>

<html>
<head>
	<title>GSI Commerce - v11 Build and Deploy Dashboard</title>
	<link rel="stylesheet" href="stylesheets/style.css" type="text/css" />
	<script type="text/javascript" src="javascripts/jquery-1.6.1.js"></script>
</head>
<body>
<img src="images/logo.jpg" />
<div style="float: right; margin-right: 100px"><h2>v11 Build & Deploy Dashboard</h2></div>
<br /><br />
Select a GEC
<form action="index.php">
<select name="gec"><?php
	foreach ($gecs as $key => $gec) {
		?><option value="<?php echo $key; ?>"><?php echo $gec; ?></option><?php
	}
?></select>
<input type="submit" value="Go">
</form><?php 
if(isset($_REQUEST['gec'])) 
{
$gec = $_REQUEST['gec'];
	?><h3><?php echo $gecs[$gec]; ?></h3>
	<table width="900" cellpadding="2">
		<tr>
			<td><b>Environment</b></td><td><b>Enfinity Version</b></td>
			<td><b>Webstore Version</b></td>
			<td width="200"><b>Base Deploy</td>
			<td><b>Status</b></td>
			<td><b>Release Notes</b></td>
		</tr><?php
		$gecproperties = parse_ini_file($gec . ".ini", true);
		$instances = $gecproperties['instances'];		
		foreach($instances as $instance => $server) 
		{
			foreach($ports as $port => $description)
			{
				?><tr bgcolor="e0e0e0">
					<td align="left" width="100"><b><?php echo strtoupper($instance); ?> - <?php echo $description; ?></td><?php
					$serverInfo = getServerInfo($gec, $server, $port);
					?>
					<td align="right"><?php echo $serverInfo['enfinityVersion']; ?></td>
					<td align="right"><?php
						if($serverInfo['status'] == 'alive') {
							?><a href="<?php echo $serverInfo['managerURL'] ?>"><?php echo $serverInfo['webstoreVersion']; ?></a><?php							
						} else {
							echo 'N/A';
						}
					
					?></td>
					<td>Build Number: <?php
					$buildInfo = getBambooBuildInfoDetail(strtoupper($gec.$instance));
					if($buildInfo['buildState'] != 'n/a') {
						echo '<a href=' . $buildInfo['buildURL'] . '>' . $buildInfo['buildNumber'] . '</a><br />';
					} else {
						echo $buildInfo['buildState'] . '</br>';
					}
					echo 'Build Status: ' . $buildInfo['buildState'] . '<br />';
					echo 'Build Time: ' . $buildInfo['buildRelativeTime'];
					?></td>
					<td><img src="images/<?php echo $serverInfo['status']; ?>.png"></td>
					<td><?php 
	
							$issues = getJiraInfo($jira, $gec, $instance, 'Webstore'); 
							for ($i = 0;$i < count($issues); $i++) {
									$issue = (object) $issues[$i];
									$tmpJIRAKey = $issue->key; 
									$tmpReleaseTitle = $issue->summary;
									echo "[ <a href=\"http://devsvn.gspt.net/amp/release/manifest.php?id=".$tmpJIRAKey."\">".$tmpJIRAKey."</a> ] <a href=\"http://devsvn.gspt.net/amp/release/manifest.php?id=".$tmpJIRAKey."\">".$tmpReleaseTitle."</a></span><br>";
								}
					?></td>
				</tr><?php
			}
		}
		?></table><?php 
}
 ?><br /><br />
</body>
</html>