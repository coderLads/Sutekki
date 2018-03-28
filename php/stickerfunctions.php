<?php

require_once("connection.php");
require_once("attendanceconnection.php");

function resetStickers(){
	global $db_server;
	global $db_stickers;
	$db_stickers->query("TRUNCATE `offerings`");
	$rss = new DOMDocument();
	$rss->load('http://classes.pscs.org/feed/');
	$feed = array();
	foreach ($rss->getElementsByTagName('item') as $node) {
		$item = array (
			'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
			'category' => $node->getElementsByTagName('category')->item(0)->nodeValue,
			'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
			'creator' => $node->getElementsByTagName('creator')->item(0)->nodeValue,
			'content' => $node->getElementsByTagName('encoded')->item(0)->nodeValue,
			'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
			'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
			);
		array_push($feed, $item);
	}
	
	foreach($feed as &$k){
		$img = $k['content'];
		if (preg_match('/src="(.*?)"/', $img, $matches)) {
				$src = $matches[1];
				$k['content'] = $src;
		}
	}
	
	foreach ($feed as $class) {
		$facilitator = get_teacher($class['link']);
        $block = is_block($class['link']);
        
		$stmt = $db_stickers->prepare("INSERT INTO offerings (`class-name`,`facilitator`,`category`,`description`,`link`,`image`,`block`) VALUES (?,?,?,?,?,?,?)");
		$stmt->bind_param('sssssss', $class['title'], $facilitator, $class['category'], $class['desc'],$class['link'], $class['content'], $block);
		$stmt->execute();
		$stmt->close();
    }

	$db_stickers->query("truncate stickers");
    $getallottedstickers = $db_stickers->query("SELECT * FROM allotted");
    $allottedstickers = $getallottedstickers->fetch_row();
    $getstudents = $db_server->query("SELECT * FROM studentdata WHERE current=1");

    $studentinfo = array();
	while ($student_data = $getstudents->fetch_assoc()) {
		array_push($studentinfo, $student_data);
	}
	
    foreach($studentinfo as $student){
        $stmt = $db_stickers->prepare("INSERT INTO stickers (`student-id`,`black`,`grey`,`white`,`blockblack`,`blockgrey`,`blockwhite`) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('iiiiiii', $student['studentid'], $allottedstickers[0], $allottedstickers[1], $allottedstickers[2],$allottedstickers[3],$allottedstickers[4],$allottedstickers[5]);
        $stmt->execute();
        $stmt->close();

    }

echo "Reset complete!";

}

function get_teacher($url){
	$text = file_get_contents($url);
	$regex = "/<span>(.*?)<\/span>/";
	
	if (preg_match_all($regex, $text ,$matches)) {
		$string = $matches[1][0];
		$string = str_replace("(", null, $string);
		$string = str_replace(")", null, $string);
		return $string;
	}
}

function is_block($url){
	$text = file_get_contents($url);
	$regex = "/<span class=block>(.*?)<\/span>/";
	
	if (strpos($text, '<span class="block">This is a block class.</span>')) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function getClasses(){
    global $db_stickers;
    $query = $db_stickers->query("SELECT `class-id`,`class-name`,`facilitator`,`link`,`block`,`black`,`grey`,`white` FROM offerings");
    $offerings = array();
	while ($classData = $query->fetch_assoc()) {
		array_push($offerings, $classData);
    }
    echo(json_encode($offerings, JSON_PRETTY_PRINT));
}

?>