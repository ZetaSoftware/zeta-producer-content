<?php

/********************************
Simple PHP File Manager
Copyright John Campbell (jcampbell1)
License: MIT
********************************/

require_once('helper.php'); 
require_once('additional.php'); 

// must be in UTF-8 or `basename` doesn't work
setlocale(LC_ALL,'en_US.UTF-8');

$tmp = realpath(joinPaths($serverBaseFolderPath, isset($_REQUEST['file']) ? $_REQUEST['file']: ''));
if($tmp === false)
	err(404,'File or Directory Not Found');
if(substr(normalizePath($tmp), 0, strlen($serverBaseFolderPath)) !== normalizePath($serverBaseFolderPath))
	err(403,"Forbidden");

if(!isset($_COOKIE['_sfm_xsrf']) || !$_COOKIE['_sfm_xsrf'])
	setcookie('_sfm_xsrf',bin2hex(openssl_random_pseudo_bytes(16)));
if($_POST) {
	if($_COOKIE['_sfm_xsrf'] !== $_POST['xsrf'] || !$_POST['xsrf'])
		err(403,"XSRF Failure");
}

$file = isset($_REQUEST['file']) && $_REQUEST['file']!='' 
	? joinPaths($serverBaseFolderPath, $_REQUEST['file'] ) 
	: joinPaths($serverBaseFolderPath, '.' );
	
$file = normalizePath($file);

function cmpFileAndFolderList($a, $b)
{
	$aDir = $a["is_dir"];
	$bDir = $b["is_dir"];

	if( $aDir==$bDir ) return strcasecmp($a["name"], $b["name"]);
	else if( $aDir && !$bDir ) return -1;
	else return +1;
}
	
if(isset($_GET['do']) && $_GET['do'] == 'list') {
	if (is_dir($file)) {
		$directory = $file;
		$result = array();
		$files = array_diff(scandir($directory), array('.','..'));
	    foreach($files as $entry)  {
    		$i = $directory . '/' . $entry;
		
			$isMatch = is_dir($i) || doesMatchGlobalWildcards(basename($i));
			
			if( $isMatch ) {
				$stat = stat($i);
				$result[] = array(
					'mtime' => $stat['mtime'],
					'size' => $stat['size'],
					'name' => basename($i),
					'path' => normalizePathV(preg_replace('@^\./@', '', trim(substr($i, strlen($serverBaseFolderPath)),"\\/"))),
					'is_dir' => is_dir($i)
				);
			}
	    }
	} else {
		err(412,"Not a Directory");
	}
	
	// So sortieren, dass Ordner zuerst kommen und anschließend Dateien.
	usort($result, "cmpFileAndFolderList");
	
	sendJsonToBrowser(array('success' => true, 'results' =>$result));
	
} elseif (isset($_GET['do']) && $_GET['do'] == 'download') {
	sendFileToBrowser($file);
}
?>

<script>
	$z(function(){
		var XSRF = (document.cookie.match('(^|; )_sfm_xsrf=([^;]*)')||0)[2];
		var $tbody = $z('#list-<?php echo $unique ?>');
		
		$z(window).bind('hashchange',list).trigger('hashchange');
		
		$z('.delete').live('click',function(data) {
			$z.post("",{'do':'delete',file:$z(this).attr('data-file'),xsrf:XSRF},function(response){
				list();
			},'json');
			return false;
		});

		function list() {
			var hashval = window.location.hash.substr(1);
			$z.get('?',{'do':'list','file':hashval},function(data) {
				$tbody.empty();
				$z('#breadcrumb-<?php echo $unique ?>').empty().html(renderBreadcrumbs(hashval));
				if(data.success) {
					$z.each(data.results,function(k,v){
						$tbody.append(renderFileRow(k, v));
					});
					!data.results.length && $tbody.append('<tr><td class="empty" colspan="3">Keine Dateien vorhanden.</td></tr>')
				} else {
					console.warn(data.error.msg);
				}
			},'json');
		}
		
		function renderFileRow(index, data) {
			var $link = $z('<a class="name" />')
				.attr('href', data.is_dir ? '#' + data.path : './'+data.path)
				.text(data.name);
			var $dl_link = $z('<a class="name" />').attr('href','?do=download&file='+encodeURIComponent(data.path))
				.text(data.name);
				
			var $html = $z('<tr />')
				.addClass(data.is_dir ? 'is-dir' : 'is-file')
				.addClass(index%2===0 ? 'odd' : 'even')
				.append( $z('<td class="first" />').append(data.is_dir ? $link : $dl_link) )
				.append( $z('<td class="size" />').text(data.is_dir ? ' ' : formatFileSize(data.size)) )  
				.append( $z('<td class="time" />').text(data.is_dir ? ' ' : formatTimestamp(data.mtime)) )
			return $html;
		}
		
		function renderBreadcrumbs(path) {
			var base = "",
				$html = $z('<div/>').append( path ? $z('<a href=#>Start</a></div>') : $z('<span class="current">Start</span>'));
				
			var splits = path.split('/');
				
			$z.each(splits,function(k,v){
				if(v) {
					if ( k===splits.length-1 ) {
						$html.append( $z('<span class="sep" />').text(' ▸ ') ).append( $z('<span class="current"/>').text(v) );
					} else {
						$html.append( $z('<span class="sep" />').text(' ▸ ') ).append( $z('<a/>').attr('href','#'+base+v).text(v) );
					}
					base += v + '/';
				}
			});
			return $html;
		}
		
		<?php require_once('helper.js'); ?>
	})
</script>
