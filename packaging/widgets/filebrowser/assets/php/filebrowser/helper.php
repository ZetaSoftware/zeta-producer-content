<?php
// http://stackoverflow.com/questions/19361282/why-would-json-encode-returns-an-empty-string
function utf8ize($d) {
    if (is_array($d)) 
        foreach ($d as $k => $v) 
            $d[$k] = utf8ize($v);

     else if(is_object($d))
        foreach ($d as $k => $v) 
            $d->$k = utf8ize($v);

     else 
        return utf8_encode($d);

    return $d;
}

function joinPaths() {
    $args = func_get_args();
    $paths = array();
    foreach ($args as $arg) {
        $paths = array_merge($paths, (array)$arg);
    }

    $paths = array_map(create_function('$p', 'return trim($p, "/");'), $paths);
    $paths = array_filter($paths);
    return join('/', $paths);
}

function normalizePath($path) {
	$ret = str_replace("\\", "/", $path);
	return str_replace("/", DIRECTORY_SEPARATOR , $path);
}

function normalizePathV($path) {
	$ret = str_replace("\\", "/", $path);
	return $ret;
}

function my_mime_content_type($filename) {
    $result = new finfo();

    if (is_resource($result) === true) {
        return $result->file($filename, FILEINFO_MIME_TYPE);
    }

    return false;
}

function my_fnmatch($pattern, $string) {
	return preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*?', '\?' => '.'))."$#i", $string);
}

function sendJsonToBrowser($json) {
	ob_clean();
	header('Content-type: application/json');
	echo json_encode(utf8ize($json), JSON_PRETTY_PRINT);
	ob_flush();
	exit;
}

function sendFileToBrowser($file) {
	ob_clean();

	$filename = basename($file);
	header('Content-Type: ' . my_mime_content_type($file));
	header('Content-Length: '. filesize($file));
	header(sprintf('Content-Disposition: attachment; filename=%s',
		strpos('MSIE',$_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\"" ));
	ob_flush();
	readfile($file);
	exit;
}
?>