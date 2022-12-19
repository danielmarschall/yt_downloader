<?php

// ViaThinkSoft YouTube Downloader Functions 2.3
// Revision: 2022-12-19
// Author: Daniel Marschall <www.daniel-marschall.de>
// Licensed under the terms of the Apache 2.0 License

// Get API key:   https://console.developers.google.com/apis/credentials
// Test API here: https://developers.google.com/apis-explorer/?hl=de#p/youtube/v3/youtube.playlistItems.list

$yt_apikey = null;
$yt_apikey_callback = null;

function yt_set_apikey($apikey) {
	global $yt_apikey;
	$yt_apikey = $apikey;
}

function yt_set_apikey_callback($apikey_callback) {
	global $yt_apikey_callback;
	$yt_apikey_callback = $apikey_callback;
}

function yt_get_apikey() {
	global $yt_apikey, $yt_apikey_callback;

	if (!empty($yt_apikey_callback)) {
		$apikey = call_user_func($yt_apikey_callback);
		if (!yt_check_apikey_syntax($apikey)) throw new Exception("Invalid API key '$apikey'");
	} else if (!empty($yt_apikey)) {
		$apikey = $yt_apikey;
		if (!yt_check_apikey_syntax($apikey)) throw new Exception("Invalid API key '$apikey'");
	} else {
		throw new Exception("This function requires a YouTube API key.\n");
	}

	return $apikey;
}

function yt_playlist_items($playlist_id, $maxresults=-1) {
	$out = array();

	$next_page_token = '';

	do {
		$cont = @file_get_contents('https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId='.urlencode($playlist_id).'&maxResults=50'.(($next_page_token!='') ? '&pageToken='.urlencode($next_page_token) : '').'&key='.urlencode(yt_get_apikey()));
		if (!$cont) return false; // e.g. if Playlist was deleted

		$obj = json_decode($cont, true);
		if (!$obj) return false;

		if (!isset($obj['items'])) return false;

		foreach ($obj['items'] as $item) {
			if ($item['snippet']['resourceId']['kind'] == 'youtube#video') {
				$title    = $item['snippet']['title'];
				$video_id = $item['snippet']['resourceId']['videoId'];
				$out[] = array($video_id, $title);
				if (($maxresults != -1) && ($maxresults == count($out))) return $out;
			}
		}

		$next_page_token = isset($obj['nextPageToken']) ? $obj['nextPageToken'] : '';
	} while ($next_page_token != '');

	return $out;
}

function yt_get_channel_id($username) {
	$cont = @file_get_contents('https://www.googleapis.com/youtube/v3/channels?key='.urlencode(yt_get_apikey()).'&forUsername='.urlencode($username).'&part=id');
	if (!$cont) return false;

	$obj = json_decode($cont, true);
	if (!$obj) return false;

	if (!isset($obj['items'])) return false;

	foreach ($obj['items'] as $item) {
		if ($item['kind'] == 'youtube#channel') {
			return $item['id'];
		}
	}
}

function yt_get_channel_id_and_stats($username) {
	$cont = @file_get_contents('https://www.googleapis.com/youtube/v3/channels?key='.urlencode(yt_get_apikey()).'&forUsername='.urlencode($username).'&part=id,statistics');
	if (!$cont) return false;

	$obj = json_decode($cont, true);
	if (!$obj) return false;

	if (!isset($obj['items'])) return false;

	foreach ($obj['items'] as $item) {
		if ($item['kind'] == 'youtube#channel') {
			return array($item['id'], $item['statistics']);
		}
	}
}

function yt_get_channel_stats($channel_id) {
	$cont = @file_get_contents('https://www.googleapis.com/youtube/v3/channels?key='.urlencode(yt_get_apikey()).'&id='.urlencode($channel_id).'&part=statistics');
	if (!$cont) return false;

	$obj = json_decode($cont, true);
	if (!$obj) return false;

	if (!isset($obj['items'])) return false; //totalResults could be 0

	foreach ($obj['items'] as $item) {
		if ($item['kind'] == 'youtube#channel') {
			return $item['statistics'];
		}
	}
}

function yt_get_playlist_stats($playlist_id) {
	$cont = @file_get_contents('https://www.googleapis.com/youtube/v3/playlists?part=contentDetails&id='.urlencode($playlist_id).'&key='.urlencode(yt_get_apikey()));
	if (!$cont) return false;

	$obj = json_decode($cont, true);
	if (!$obj) return false;

	if (!isset($obj['items'])) return false;

	foreach ($obj['items'] as $item) {
		if ($item['kind'] == 'youtube#playlist') {
			if (!isset($item['contentDetails']) || is_null($item['contentDetails'])) return false; // can happen to deleted playlists
			return $item['contentDetails'];
		}
	}
}

function yt_channel_items($channel_id, $searchterms='', $maxresults=-1) {
	$out = array();

	$next_page_token = '';

	do {
		$cont = @file_get_contents('https://www.googleapis.com/youtube/v3/search?part=snippet&channelId='.urlencode($channel_id).(($searchterms!='') ? '&q='.urlencode($searchterms) : '').'&maxResults=50'.(($next_page_token!='') ? '&pageToken='.urlencode($next_page_token) : '').'&key='.urlencode(yt_get_apikey()));
		if (!$cont) return false;

		$obj = json_decode($cont, true);
		if (!$obj) return false;

		if (!isset($obj['items'])) return false;

		foreach ($obj['items'] as $item) {
			if ($item['id']['kind'] == 'youtube#video') {
				$title    = $item['snippet']['title'];
				$video_id = $item['id']['videoId'];
				$out[] = array($video_id, $title);
				if (($maxresults != -1) && ($maxresults == count($out))) return $out;
			}
		}

		$next_page_token = isset($obj['nextPageToken']) ? $obj['nextPageToken'] : '';
	} while ($next_page_token != '');

	return $out;
}

// Acceptable order values are: date, rating, relevance(default), title, videoCount, viewCount
function yt_search_items($searchterms, $order='', $maxresults=-1) {
	$out = array();

	$next_page_token = '';

	do {
		$cont = @file_get_contents('https://www.googleapis.com/youtube/v3/search?part=snippet&q='.urlencode($searchterms).(($order!='') ? '&order='.urlencode($order) : '').'&maxResults=50'.(($next_page_token!='') ? '&pageToken='.urlencode($next_page_token) : '').'&key='.urlencode(yt_get_apikey()));
		if (!$cont) return false;

		$obj = json_decode($cont, true);
		if (!$obj) return false;

		if (!isset($obj['items'])) return false;

		foreach ($obj['items'] as $item) {
			if ($item['id']['kind'] == 'youtube#video') {
				$title    = $item['snippet']['title'];
				$video_id = $item['id']['videoId'];
				$out[] = array($video_id, $title);
				if (($maxresults != -1) && ($maxresults == count($out))) return $out;
			}
		}

		$next_page_token = isset($obj['nextPageToken']) ? $obj['nextPageToken'] : '';
	} while ($next_page_token != '');

	return $out;
}

function getVideoIDFromURL($url) {
	// Extract video ID from the URL

	$vid = false;
	$m = null;

	# Usual format
	if (($vid === false) && (preg_match("@https{0,1}://(www\\.|)youtube\\.com/watch(.*)(/|&|\\?)v=([a-zA-Z0-9_-]{11})@ismU", $url, $m))) {
		$vid = $m[4];
	}

	# Short format
	if (($vid === false) && (preg_match("@https{0,1}://(www\\.|)youtu\\.be/([a-zA-Z0-9_-]{11})@ismU", $url, $m))) {
		$vid = $m[2];
	}

	# YouTube "Shorts"
	if (($vid === false) && (preg_match("@https{0,1}://(www\\.|)youtube\\.com/shorts/([a-zA-Z0-9_-]{11})@ismU", $url, $m))) {
		$vid = $m[2];
	}

	return $vid;
}

function getPlaylistIDFromURL($url) {
	$pid = false;

	# Usual format
	$m = null;
	if (($pid === false) && (preg_match("@https{0,1}://(www\\.|)youtube\\.com/(.*)(/|&|\\?)list=(.+)&@ismU", $url.'&', $m))) {
		$pid = $m[4];
	}

	return $pid;
}

function yt_check_apikey_syntax($apikey) {
	return preg_match('@^[a-zA-Z0-9]{39}$@', $apikey);
}

function yt_check_video_id($video_id) {
	return preg_match('@^[a-zA-Z0-9\-_]{11}$@', $video_id);
}

function yt_get_channel_id_from_custom_url($custom_url) {
	// TODO: is there any API possibility??? API only accepts username and id ?!

	// https://www.youtube.com/c/SASASMR
	// <link rel="canonical" href="https://www.youtube.com/channel/UCp4LfMtDfoa29kTlLnqQ5Mg">
	// https://www.youtube.com/impaulsive
	// <link rel="canonical" href="https://www.youtube.com/channel/UCGeBogGDZ9W3dsGx-mWQGJA">
	// https://www.youtube.com/@KosmonautMusicSpecials
	// <link rel="canonical" href="https://www.youtube.com/channel/UCayYOQ0DEIpcTgO9z_b-K1A">

	$cont = @file_get_contents($custom_url);
	if ($cont === false) {
		throw new Exception("Cannot open $custom_url using file_get_contents.");
	}
	if (!preg_match('@<link rel="canonical" href="https://www.youtube.com/channel/([^"]+)">@ismU', $cont, $m)) {
		return false;
	}

	return $m[1];
}

function yt_get_channel_id_from_url($channel_url) {
	$m = null;
	if (preg_match("@https{0,1}://(www\\.|)youtube\\.com/user/(.*)(/|&|\\?)@ismU", $channel_url.'&', $m)) {
		// Username (deprecated feature. Not every channel has a username associated with it)
		// https://www.youtube.com/user/USERNAME
		$username = $m[2];
		$channel_id = yt_get_channel_id($username);
		return $channel_id;
	} else if (preg_match("@https{0,1}://(www\\.|)youtube\\.com/channel/(.*)(/|&|\\?)@ismU", $channel_url.'&', $m)) {
		// Real channel ID
		// https://www.youtube.com/channel/ID
		$channel_id = $m[2];
		return $channel_id;
	} else if (preg_match("@https{0,1}://(www\\.|)youtube\\.com/(c/){0,1}(.*)(/|&|\\?)@ismU", $channel_url.'&', $m)) {
		// Channel custom URL
		// https://www.youtube.com/NAME or https://www.youtube.com/c/NAME
		return yt_get_channel_id_from_custom_url($channel_url);
	} else {
		return false;
	}
}

// Examples:
//yt_set_apikey(trim(file_get_contents(__DIR__ . '/.yt_api_key')));
//print_r(yt_playlist_items('PL9GbGAd-gY1pyxZJIX5MOdYdRbdweVAID'));
//print_r(yt_channel_items('UCjPjcMEAN64opOoNQE9GykA'));
//print_r(yt_channel_items('UCjPjcMEAN64opOoNQE9GykA', 'Knight'));
//print_r(yt_search_items('Wesley Willis', 'date', 10));
