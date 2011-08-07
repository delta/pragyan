<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * @author akash6190
 * For more details, see README
 */

class pngrender {
	function render_png($string,$hash){
		$font   = 4;
		$width  = ImageFontWidth($font) * strlen($string);
		$height = ImageFontHeight($font);
		 
		$im = @imagecreate ($width,$height);
		$background_color = imagecolorallocate ($im, 255, 255, 255); //white background
		$text_color = imagecolorallocate ($im, 0, 0,0);//black text
		imagecolortransparent($im,$background_color);
		imagestring ($im, $font, 0, 0,  $string, $text_color);
		$pngdata=imagepng ($im,$this->CACHE_DIR."/$hash.png");
		chdir($current_dir);

	}
	function cleanup($hash) {
		$current_dir = getcwd();
		chdir($this->TMP_DIR);
		unlink($this->TMP_DIR . "/$hash.png");
		chdir($current_dir);
	}
	function transform($text) {
		global $sourceFolder;
		global $uploadFolder;
		global $urlRequestRoot, $cmsFolder;
		$uploadDir = $sourceFolder . "/" . $uploadFolder;
		if (!file_exists($uploadDir . "/temp"))
			mkdir($uploadDir . "/temp", 0755);
		if (!file_exists($uploadDir . "/cache"))
			mkdir($uploadDir . "/cache", 0755);
		$this->TMP_DIR = $uploadDir . "/temp";
		$this->CACHE_DIR = $uploadDir . "/cache";
		$this->URL_PATH = $urlRequestRoot . "/" . $cmsFolder ."/" .$uploadFolder . "/cache";
		preg_match_all("/\[tex\](.*?)\[\/tex\]/si", $text, $matches1);		
		preg_match_all("/\[img\](.*?)\[\/img\]/si", $text, $matches2);
		$matches[0]=array_merge($matches2[0],$matches1[0]);
		$matches[1]=array_merge($matches2[1],$matches1[1]);
		for ($i = 0; $i < count($matches[0]); $i++) {
			$position = strpos($text, $matches[0][$i]);
			$thunk = $matches[1][$i];
			$hash = md5($thunk);
			$full_name = $this->CACHE_DIR . "/" .
			$hash . ".png";
			$url = $this->URL_PATH . "/" .
			$hash . ".png";
			if (!is_file($full_name)) {
				$this->render_png($thunk, $hash);
				$this->cleanup($hash);
			} else
				touch($full_name);
			$text = substr_replace($text, "<img src=\"$url\" alt=\"Formula: $i\" />", $position, strlen($matches[0][$i]));
		}
		exec("find " .  $uploadDir . "/cache -type f -mtime +14 | xargs rm -f");
		return $text;
	}

}
