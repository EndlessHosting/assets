<?php
header ("content-type: text/xml");
$xtn = array_key_exists('xtn', $_GET) ? $_GET['xtn'] : NULL;
$mode = array_key_exists('mode', $_GET) ? $_GET['mode'] : NULL;
$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}
// -----
switch ($mode) {
    case "extensions":
        echo directoryShow("extensions", $url, $xtn);
    break;
    case "groups":
        echo directoryShow("groups", $url, $xtn);
    break;
    case "personal":
        echo directoryShow("personal", $url, $xtn);
    break;
    case "system":
    	echo systemShow($url, $xtn);
    break;
    case "tools":
    	echo toolsShow($url, $xtn);
    break;
    default:
        echo directoryMenu($url, $xtn);
    }
// -----
function systemShow($url, $xtn) {
	$menudata = array(
		array('Voicemail', "*97")
		,array('Wake Up Call', "*68")
		,array('DND Toggle', "*76")
		,array('Staff Conference', "104")
		,array('Begin Dictation', "*34")
		,array('Email Dictation', "*35")
	);
	$xml = new SimpleXMLElement('<CiscoIPPhoneDirectory/>');
	$xml -> addChild('Title', 'System Extensions');
	$xml -> addChild('Prompt', 'Select an option');
	foreach ($menudata as $item){
       $menuItem = $xml -> addChild('DirectoryEntry');
       $menuItem -> addChild('Name', $item[0]);
       $menuItem -> addChild('Telephone', $item[1]);
    }
    return ($xml->asXML());

}

function toolsShow($url, $xtn) {
	$menudata = array(
		array('Simulate Call', "7777")
		,array('Call Listen', "555")
		,array('Echo Test', "*43")
		,array('Speaking Clock', "*60")
		,array('Other Ext. Voicemail', "*98")
		,array('Blacklist a Number', "*30")
		,array('Remove a Blacklisted Number', "*31")
	);
	$xml = new SimpleXMLElement('<CiscoIPPhoneDirectory/>');
	$xml -> addChild('Title', 'System Tools');
	$xml -> addChild('Prompt', 'Select a tool');
	foreach ($menudata as $item){
       $menuItem = $xml -> addChild('DirectoryEntry');
       $menuItem -> addChild('Name', $item[0]);
       $menuItem -> addChild('Telephone', $item[1]);
    }
    return ($xml->asXML());

}



function directoryMenu($url, $xtn) {
    $menudata = array(
        array('Internal Phonebook', "$url?mode=extensions")
        ,array('Ring Groups', "$url?mode=groups")
        ,array('Useful Numbers', "$url?mode=system")
        ,array('System Tools', "$url?mode=tools")
    );
    $xml = new SimpleXMLElement('<CiscoIPPhoneMenu/>');
    $xml -> addChild('Prompt', 'Select a directory');

    foreach ($menudata as $menuitem) {
        $menuItem = $xml -> addChild('MenuItem');
        $menuItem -> addChild('Name', $menuitem[0]);
        $menuItem -> addChild('URL', $menuitem[1]);
    }
    return ($xml->asXML());
}
// -----
function directoryShow($mode, $url, $xtn) {
    global $db;
    switch ($mode) {
        case "extensions":
            $sql = "SELECT name, extension FROM users WHERE name NOT LIKE '%FAX%' AND extension < 400 ORDER BY name";
            $title = "Internal directory";
            $prompt = "Select a name";
        break;
        case "groups":
            $sql = "SELECT description, grpnum FROM ringgroups WHERE grpnum < 599 ORDER BY description";
            $title = "Department directory";
            $prompt = "Select a group";
        break;
        case "personal":
            $sql = "
                SELECT
                    CONCAT(contactmanager_group_entries.displayname, ' (', contactmanager_entry_numbers.type,')') AS 'name'
                    ,contactmanager_entry_numbers.number AS 'extension'
                FROM contactmanager_groups
                LEFT JOIN contactmanager_group_entries ON contactmanager_groups.id = contactmanager_group_entries.groupid
                LEFT JOIN contactmanager_entry_numbers ON contactmanager_group_entries.id = contactmanager_entry_numbers.entryid
                WHERE contactmanager_groups.name = '$xtn'
                ORDER BY contactmanager_group_entries.displayname
            ";
            $title = "Personal contacts";
            $prompt = "Select a name";
        break;
        default:
        return;
    }

    $results = $db->getAll($sql, DB_FETCHMODE_ORDERED);
    $numrows = count($results);
    $endoflist = false;
    $xml = new SimpleXMLElement('<CiscoIPPhoneDirectory/>');
    $xml -> addChild('Title', $title);
    $xml -> addChild('Prompt', $prompt);

    $page = array_key_exists('page', $_GET) ? $_GET['page'] : 0;
    $count = $page * 32;

    for ($row=$count; $row <= $count+31; $row++) {
        if (is_null($results[$row][0])) {
            $endoflist = true;
        } else {
            $endoflist = false;
            $directoryEntry = $xml -> addChild('DirectoryEntry');
            $directoryEntry -> addChild('Name', $results[$row][0]);
            $directoryEntry -> addChild('Telephone', $results[$row][1]);
        }
    }
    return ($xml->asXML());
}
?>
