<?php

require_once("connection.php");
require_once("../../attendance_system/connection.php");

function resetstickers(){
	global $db_attendance;
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
	
	$zero = "0";
	
	foreach ($feed as $class) {
	
		$facilitator = get_teacher($class['link']);
	
		$block = is_block($class['link']);
	
		$stmt = $db_stickers->prepare("INSERT INTO offerings (classname,facilitator,category,description,link,image,block,blackstickers,greystickers,whitestickers) VALUES (?,?,?,?,?,?,?,?,?,?)");
	
		$stmt->bind_param('ssssssssss', $class['title'], $facilitator, $class['category'], $class['desc'],$class['link'], $class['content'], $block, $zero, $zero, $zero);
	
		$stmt->execute();
	
		$stmt->close();
    }
	

}

?>