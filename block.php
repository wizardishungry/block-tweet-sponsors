<?php
define('TEST', false);
require_once('twitteroauth/twitteroauth/twitteroauth.php');
require_once('config.php');

$path = dirname(__FILE__);
$twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

if(!TEST) {

    $twitter->host = "https://twitter.com/";
    $tweets = $twitter->get("/i/timeline", array('include_available_features'=>1, 'include_entities'=>1, 'last_note_ts'=>'0','max_position'=>''));
    $body = $tweets->items_html;
}
else {
    $body = file_get_contents("$path/test.txt");
}

$dom = DOMDocument::loadHTML($body);
$finder = new DomXPath($dom);
$classname="promoted-tweet";
$nodeList = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
$blocklist = array();
foreach ($nodeList as $node) {
    $name = $node->getAttribute("data-screen-name");
    $follow = $node->getAttribute("data-you-follow");
    $following = $follow=="true";
    $block = $node->getAttribute("data-you-block");
    $blocking = $block=="true";
    echo "$name ".($following?' following...':''). ($blocking?' blocking...':'')."\n";
    if(!$following && !$blocking) {
        $blocklist[$name] = $name;
    }
}

$twitter->host = "https://api.twitter.com/1.1/";
foreach($blocklist as $name) {
    echo "blocking $name...\n";
    $result = $twitter->post('blocks/create',array('screen_name' => $name));
    sleep(1);
}
echo "done.";
