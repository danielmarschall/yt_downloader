<?php

// ViaThinkSoft YouTube Downloader Util 2.3
// Revision: 2022-02-07
// Author: Daniel Marschall <www.daniel-marschall.de>
// Licensed under the terms of the Apache 2.0 License

function cs_get_checksumfilename($file, $type='sfv') {
	$dir = dirname($file);
	$files = preg_grep('/\.'.preg_quote($type,'/').'$/i', glob($dir.'/*'));

	if (count($files) > 0) {
		$cs_file = array_pop($files);
	} else {
		$cs_file = $dir.'/'.basename(dirname($file)).'.'.$type;
	}
	return $cs_file;
}

function cs_add_automatically($file, $type='sfv') {
	$type = strtolower($type);
	$cs_file = cs_get_checksumfilename($file, $type);
	// echo "Checksum file: $cs_file\n";
	if ($type == 'sfv') {
		if (file_exists($cs_file)) {
			$files = sfv_get_files($cs_file);
			if (in_arrayi(basename($file), $files)) return true;
		} else {
			file_put_contents($cs_file, "; Generated by ViaThinkSoft\r\n"); // TODO: BOM
			$files = array();
		}
		$hash = crc32_file($file);
		if ($hash === false) {
			fwrite(STDERR, "Cannot calculate hash of file '$file'\n");
			return false;
		}
		$crc32 = strtoupper($hash);
		file_put_contents($cs_file, basename($file)." $crc32\r\n", FILE_APPEND);
		return true;
	}
	else if ($type == 'md5') {
		if (file_exists($cs_file)) {
			$files = md5_get_files($cs_file);
			if (in_arrayi(basename($file), $files)) return true;
		} else {
			file_put_contents($cs_file, "; Generated by ViaThinkSoft\r\n"); // TODO: BOM
			$files = array();
		}
		$hash = md5_file($file);
		if ($hash === false) {
			fwrite(STDERR, "Cannot calculate hash of file '$file'\n");
			return false;
		}
		$md5 = strtolower($hash);
		file_put_contents($cs_file, "$md5 *".basename($file)."\r\n", FILE_APPEND);
		return true;
	}
	else if ($type == 'none') {
		return true;
	}
	else {
		fwrite(STDERR, "Unknown checksum type '$type'. Must be SFV, MD5 or None\n");
		return false;
	}
}

function md5_get_files($filename) {
	// Source: https://github.com/danielmarschall/checksum-tools/blob/master/PHP/md5_generate.php
	$out = array();
	$lines = file($filename);
	foreach ($lines as $line) {
		$line = str_replace("\xEF\xBB\xBF",'',$line);
		$line = trim($line);
		if ($line == '') continue;
		$line = str_replace('*', ' ', $line);
		$line = str_replace("\t", ' ', $line);
		list($checksum, $origname) = explode(' ', $line, 2);
		$origname = dirname($filename) . '/' . trim($origname);
		$checksum = trim($checksum);
		$out[] = $origname;
	}

	return $out;
}

function sfv_get_files($filename) {
	// Source: https://github.com/danielmarschall/checksum-tools/blob/master/PHP/sfv_generate.php
	$out = array();
	$lines = file($filename);
	foreach ($lines as $line) {
		$line = rtrim($line);
		if ($line == '') continue;
		if (substr($line,0,1) == ';') continue;
		$out[] = substr($line, 0, strrpos($line, ' '));

	}
	return $out;
}

function swapEndianness($hex) {
	// Source: https://github.com/danielmarschall/checksum-tools/blob/master/PHP/sfv_generate.php
	return implode('', array_reverse(str_split($hex, 2)));
}

function crc32_file($filename, $rawOutput = false) {
	// Source: https://github.com/danielmarschall/checksum-tools/blob/master/PHP/sfv_generate.php
	$hash = hash_file('crc32b', $filename, true);
	if ($hash === false) return false;
	$out = bin2hex($hash);
	if (hash('crc32b', 'TEST') == 'b893eaee') {
		// hash_file() in PHP 5.2 has the wrong Endianess!
		// https://bugs.php.net/bug.php?id=47467
		$out = swapEndianness($out);
	}
	return $out;
}

function in_arrayi($needle, $haystack) {
	// Source: https://www.php.net/manual/en/function.in-array.php#89256
	return in_array(strtolower($needle), array_map('strtolower', $haystack));
}