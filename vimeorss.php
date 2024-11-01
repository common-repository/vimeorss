<?php
/*
Plugin Name: vimeorss
Version: 2.1
Plugin URI: http://ben.momillett.org/vimeorss/
Description: Creates a badge of recent videos from a vimeo user's account, now with image caching and zoombox compatability.
Author: Ben Millett
Author URI: http://benmillett.us/

Based on the del.icio.us plugin by Tom Gilbert (http://linuxbrit.co.uk)

Copyright (c) 2007
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt
*/

/*	USAGE		
	<?php if (function_exists("vimeorss")) { vimeorss(); } ?>
 	with these options:
 	vimeorss(User name, Feed type (videos, subscriptions, contacts_like, contacts_videos, appears_in), Number of thumbnails, "Before image", "After image", Thumbnail size (small, medium, large), Cache time length in seconds )
 	Style the output using CSS. Don't forget to enable
        the plugin on the wordpress admin page.
*/

$useVImageCache = 1;  // SET TO 0 TO TURN OFF CACHING
$useVLightbox = 0; // SET TO 1 TO TURN ON ZOOMBOX CAPABILITY
$useVShadowbox = 0; // SET TO 1 TO TURN ON SHADOWBOX CAPABILITY
$boxHeight = 600; // SET TO YOUR DESIRED HEIGHT FOR THE ZOOMBOX/SHADOWBOX
$boxWidth = 380; // SET TO YOUR DESIRED WIDTH FOR THE ZOOMBOX/SHADOWBOX

/* 		There is no need to edit below this.	 */


function vimeorss(
  $vimeoxmluser,            	# your vimeoxml username
  $vimeoxmlrequest="videos",  #videos,subscriptions,contacts_like,contacts_videos,appears_in   
  $vcount=4,
  $vimeoxbefore="<li>",
  $vimeoxafter="</li>",
  $vcache_time=600,        	# how long to cache the results for (default=10 minutes)
  $vsize="small",
  $cache_vfile = ""         #	cache file (defaults to /vimeopluginlocation/vimeoxml.$vimeoxmluser.$vcount.$request.$vcache_time.cache)
) {
  
	global $_fpvimeowrite, $_vimeoxml_user, $_curl_error_code;

	global $insideitemv, $vimeotag, $vimeotitle, $videouploader, $vimeoclipid, $vimeourl, $vimeothumbnail, $vimeoxmlbefore, $vimeoxmlafter, $vxcount, $size, $useVLightbox, $useVShadowbox, $boxHeight, $boxWidth;
	
	global $itemsv;

	$insideitemv = FALSE;
	$vimeoxmlbefore = $vimeoxbefore;
	$vimeoxmlafter = $vimeoxafter;
	$vimeotag = "";
	$vimeotitle = "";
	$videouploader = "";
	$vimeoclipid = "";
	$vimeourl = "";
	$itemsv = 0;
	$vxcount = $vcount;
	$size = $vsize;
	
	if ($vimeoxmlrequest=="clips" || $vimeoxmlrequest=="videos") {$vimeoxmlrequest="videos";}
	if ($vimeoxmlrequest=="contacts_clips" || $vimeoxmlrequest=="contacts_videos") {$vimeoxmlrequest="contacts_videos";}
	  
	$_vimeoxml_user = $vimeoxmluser; 
  	$vapi_url = "http://vimeo.com/api/v2/$vimeoxmluser/$vimeoxmlrequest.xml";

	if ($cache_vfile == "") 
	{
		$_vimeoxml_user = ereg_replace('/','-',$vimeoxmluser);
		
		if ( !function_exists('sys_get_temp_dir')) {
			function sys_get_temp_dir() {
				if (!empty($_ENV['TMP'])) { return realpath($_ENV['TMP']); }
				if (!empty($_ENV['TMPDIR'])) { return realpath( $_ENV['TMPDIR']); }
				if (!empty($_ENV['TEMP'])) { return realpath( $_ENV['TEMP']); }
				$tempfile=tempnam(uniqid(rand(),TRUE),'');
				if (file_exists($tempfile)) {
					unlink($tempfile);
					return realpath(dirname($tempfile));
				}
			}
		}
		if ($useVImageCache==1) { $dirvimeocache = dirname(__FILE__); } 
		else { $dirvimeocache = sys_get_temp_dir(); }
		$cache_vfile  =  "$dirvimeocache/vimeoxml.$_vimeoxml_user.$vcache_time.txt";
		//$cache_vfile  =  "/tmp/vimeoxml.$vcache_time.cache";
	}
  	
	$cache_vfile_tmp = "$cache_vfile.tmp";

	$time = split(" ", microtime());
	srand((double)microtime()*1000000);
	
	# randomise a bit, between 30 and 60s "off the mark" to avoid a bunch of
	# requests all simultaneously deciding to refresh the file
	$vcache_time_rnd = 30 - rand(0, 60);

	if (
		!file_exists($cache_vfile)
	  	|| !filesize($cache_vfile) > 20
	  	|| ((filemtime($cache_vfile) + $vcache_time - $time[1]) + $vcache_time_rnd < 0)
	  	|| (filemtime(__FILE__) > filemtime($cache_vfile))
	  ) 
	{
	  
		$c = curl_init($vapi_url);
	  	curl_setopt($c, CURLOPT_RETURNTRANSFER,1); 
	  	//curl_setopt($c, CURLOPT_USERPWD,"$vimeoxmluser:$pass"); 
	  	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
	  	curl_setopt($c, CURLOPT_TIMEOUT, 4);
	  	curl_setopt($c, CURLOPT_USERAGENT, "vimeoxml WordPress Plugin");
	  	$response = curl_exec($c);
	  	$info = curl_getinfo($c);

		// check if the response contains actual clips
		$findme  = '<clip';
		$pos = strpos($response, $findme); 
		$findme2  = '<video';
		$pos2 = strpos($response, $findme2); 

		if ($pos !== false || $pos2 !== false) 
		{
		
			//echo "write new cache file";
			
		  	$_curl_error_code = $info['http_code'];
		  	curl_close($c);
			if ($_curl_error_code == 200) // STATUS OK
			{
				$_fpvimeowrite = fopen($cache_vfile_tmp, 'w');
			 	if ($_fpvimeowrite) 
				{
					# parse the XML, then write out includable html to the cache file.
			   		if (!($vimeoxml_parser = xml_parser_create()))
			     		die("Couldn't create parser.");

			   		xml_set_element_handler($vimeoxml_parser,
			                           "startvElement",
			                           "endvElement");
			   		fputs($_fpvimeowrite, "\n");
					xml_set_character_data_handler($vimeoxml_parser, "charactervData"); 
			 		xml_parse($vimeoxml_parser, $response);
				 		fputs($_fpvimeowrite, "\n");

			   		xml_parser_free($vimeoxml_parser);
			   		fclose($_fpvimeowrite);
			   		# be atomic
					rename($cache_vfile_tmp, $cache_vfile);
				}
			}
		}
	}
		
	if ((file_exists($cache_vfile)) && filesize($cache_vfile) > 20) 
	{
		include($cache_vfile); //prints file
	} 
	elseif ($_curl_error_code) 
	{
		echo "<li>No recent clips (error $_curl_error_code)</li>";
	} 
	else 
	{
		echo "<li>No recent clips</li>";
	}
}

function startvElement($parser, $namev, $attrs) {
	global $insideitemv, $vimeotag, $vimeotitle, $videouploader, $vimeourl, $itemsv,$vxcount, $_fpvimeowrite;
	if ($insideitemv && $itemsv < $vxcount+1) {
		$vimeotag = $namev;
	} elseif ($namev == "CLIP") {
		 $insideitemv = true;
		 $itemsv++;
	} elseif ($namev == "VIDEO") {
		 $insideitemv = true;
		 $itemsv++;
	}
}

function endvElement($parser, $namev) {
	global $insideitemv, $vimeotag, $vimeotitle, $videouploader, $vxcount, $itemsv, $vimeothumbnail, $vimeoclipid, $vimeourl, $vimeoxmlbefore, $vimeoxmlafter, $useVImageCache, $useVLightbox, $useVShadowbox, $boxHeight, $boxWidth, $vwidth, $vheight, $size, $_fpvimeowrite, $cache_vfile;
	
	if (($namev == "CLIP" || $namev == "VIDEO") && $itemsv < $vxcount+1) 
	{
	
	$fullvPath = ABSPATH.'wp-content/plugins/vimeorss/'; 
	$cachevPath = get_bloginfo('wpurl')."/wp-content/plugins/vimeorss/";
	preg_match('@^(?:http://)?([^(?j)]+)@i', $vimeothumbnail, $vSlugMatches);
	$vSlug = $vSlugMatches[1];
	$vSlug = ereg_replace('/','-',$vSlug);
	$vSlug = ereg_replace('\.','',$vSlug);


	if ($useVImageCache==1) {

		if (!file_exists("$fullvPath$vSlug.jpg")) {   
			if ( function_exists('curl_init') ) { 
				$cv = curl_init();
                $localvimage = fopen("$fullvPath$vSlug.jpg", "wb");
                curl_setopt($cv, CURLOPT_URL, $vimeothumbnail);
                curl_setopt($cv, CURLOPT_CONNECTTIMEOUT, 1);
                curl_setopt($cv, CURLOPT_FILE, $localvimage);
                curl_exec($cv);
                curl_close($cv);
            } else {
            	$filevdata = "";
                $remotevimage = fopen($vimeothumbnail, 'rb');
                if ($remotevimage) {
                	while(!feof($remotevimage)) {
                    	$filevdata.= fread($remotevimage,1024*8);
                    }
                }
            fclose($remotevimage);
            $localvimage = fopen("$fullvPath$vSlug.jpg", 'wb');
            fwrite($localvimage,$filevdata);
            fclose($localvimage);
            } 
        } 
    	$vimeothumbnail = "$cachevPath$vSlug.jpg";
 	} else {

		$vimeothumbnail = $vimeothumbnail;      
    }

	if ($size == "large") { $vwidth = 640; $vheight = 360;
		} elseif ($size == "medium" ) { $vwidth = 200; $vheight = 150;
		} else { $vwidth = 100; $vheight = 75; }
	
	if ($useVLightbox==1){
		fputs($_fpvimeowrite, ''.$vimeoxmlbefore.'<a href="http://vimeo.com/'.trim($vimeoclipid).'" rel="zoombox[s '.$boxHeight.' '.$boxWidth.']" title="'.htmlspecialchars(trim($vimeotitle)).'"><img alt="'.htmlspecialchars(trim($vimeotitle)).'" src="'.trim($vimeothumbnail).'" width="'.$vwidth.'" height="'.$vheight.'"/></a>'.$vimeoxmlafter.'');
	}elseif ($useVShadowbox==1) {
		fputs($_fpvimeowrite, ''.$vimeoxmlbefore.'<a href="http://vimeo.com/moogaloop.swf?clip_id='.trim($vimeoclipid).'&server=vimeo.com&show_title=1&show_byline=1&autoplay=1" rel="shadowbox;width='.$boxHeight.';height='.$boxWidth.';" title="'.htmlspecialchars(trim($vimeotitle)).'"><img alt="'.htmlspecialchars(trim($vimeotitle)).'" src="'.trim($vimeothumbnail).'" width="'.$vwidth.'" height="'.$vheight.'"/></a>'.$vimeoxmlafter.'');
	}else {
		fputs($_fpvimeowrite, ''.$vimeoxmlbefore.'<a href="'.trim($vimeourl).'"><img alt="'.htmlspecialchars(trim($vimeotitle)).'" src="'.trim($vimeothumbnail).'" width="'.$vwidth.'" height="'.$vheight.'"/></a>'.$vimeoxmlafter.'');
	}
	$vimeotitle = "";
	$videouploader = "";
	$vimeoclipid = "";
	$vimeourl = "";
	$vimeothumbnail = "";
	$insideitemv = false;
	}
}

function charactervData($parser, $data) {
	global $insideitemv, $vimeotag, $vimeotitle, $vimeothumbnail, $videouploader, $vimeoclipid, $vimeourl, $size, $namev;
	if ($namev == "CLIP") {
	if ($insideitemv && $size == "medium") {
		switch ($vimeotag) {
			case "USER_NAME":
			$videouploader .= $data;
			break;
			case "THUMBNAIL_MEDIUM":
			$vimeothumbnail .= $data;
			break;
			case "TITLE":
			$vimeotitle .= $data;
			break;
			case "CLIP_ID":
			$vimeoclipid .= $data;
			break;
			case "URL":
			$vimeourl .= $data;
			break;
		}
	} elseif ($insideitemv && $size == "large") {
		switch ($vimeotag) {
			case "USER_NAME":
			$videouploader .= $data;
			break;
			case "THUMBNAIL_LARGE":
			$vimeothumbnail .= $data;
			break;
			case "TITLE":
			$vimeotitle .= $data;
			break;
			case "CLIP_ID":
			$vimeoclipid .= $data;
			break;
			case "URL":
			$vimeourl .= $data;
			break;
		}
	} else {
		switch ($vimeotag) {
			case "USER_NAME":
			$videouploader .= $data;
			break;
			case "THUMBNAIL_SMALL":
			$vimeothumbnail .= $data;
			break;
			case "TITLE":
			$vimeotitle .= $data;
			break;
			case "CLIP_ID":
			$vimeoclipid .= $data;
			break;
			case "URL":
			$vimeourl .= $data;
			break;
		}
	}}else{
		if ($insideitemv && $size == "medium") {
		switch ($vimeotag) {
			case "USER_NAME":
			$videouploader .= $data;
			break;
			case "THUMBNAIL_MEDIUM":
			$vimeothumbnail .= $data;
			break;
			case "TITLE":
			$vimeotitle .= $data;
			break;
			case "ID":
			$vimeoclipid .= $data;
			break;
			case "URL":
			$vimeourl .= $data;
			break;
		}
	} elseif ($insideitemv && $size == "large") {
		switch ($vimeotag) {
			case "USER_NAME":
			$videouploader .= $data;
			break;
			case "THUMBNAIL_LARGE":
			$vimeothumbnail .= $data;
			break;
			case "TITLE":
			$vimeotitle .= $data;
			break;
			case "ID":
			$vimeoclipid .= $data;
			break;
			case "URL":
			$vimeourl .= $data;
			break;
		}
	} else {
		switch ($vimeotag) {
			case "USER_NAME":
			$videouploader .= $data;
			break;
			case "THUMBNAIL_SMALL":
			$vimeothumbnail .= $data;
			break;
			case "TITLE":
			$vimeotitle .= $data;
			break;
			case "ID":
			$vimeoclipid .= $data;
			break;
			case "URL":
			$vimeourl .= $data;
			break;
		}
	}
}
}

?>