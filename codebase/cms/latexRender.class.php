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
 * For more details, see README
 */

class latexrender {

	var $LATEX_PATH = "/usr/bin/latex";
	var $DVIPS_PATH = "/usr/bin/dvips";
	var $CONVERT_PATH = "/usr/bin/convert";
	/** Redefined in transform() */
	var $TMP_DIR = "./cache";
	var $CACHE_DIR = "/var/www/html/workspace2/pragyan_v2/cms/modules/article/latex/demo/pictures";
	var $URL_PATH = "http://pragyan.org/workspace2/pragyan_v2/cms/modules/article/latex/demo/pictures";

	function wrap($thunk) {
		return<<<EOS
    \documentclass[10pt]{article}

    % add additional packages here
    \usepackage{amsmath}
    \usepackage{amsfonts}
    \usepackage{amssymb}
    \usepackage{pst-plot}
    \usepackage{color}  
    \pagestyle{empty}
    \begin{document}
    \pagecolor{white}
    $thunk
    \end{document}
EOS;
	}
	function render_latex($thunk, $hash) {
		$thunk = $this->wrap($thunk);

		$current_dir = getcwd();
		chdir($this->TMP_DIR);
		// create temporary LaTeX file
		$fp = fopen("$hash.tex", "w+");
		fputs($fp, $thunk);
		fclose($fp);
		// run LaTeX to create temporary DVI file
		$command = $this->LATEX_PATH .
		" --interaction=nonstopmode " .
		$hash . ".tex";
		exec($command);
		// run dvips to create temporary PS file
		$command = $this->DVIPS_PATH .
		" -E $hash" .
		".dvi -o " . "$hash.ps";
		exec($command);
		// run PS file through ImageMagick to
		// create PNG file
		$command = $this->CONVERT_PATH .
		" -density 120 $hash.ps $hash.png";
		exec($command);
		// copy the file to the cache directory
		copy($this->TMP_DIR."/$hash.png", $this->CACHE_DIR ."/$hash.png");
		chdir($current_dir);

	}
	function cleanup($hash) {
		$current_dir = getcwd();
		chdir($this->TMP_DIR);
		unlink($this->TMP_DIR . "/$hash.tex");
		unlink($this->TMP_DIR . "/$hash.aux");
		unlink($this->TMP_DIR . "/$hash.log");
		unlink($this->TMP_DIR . "/$hash.dvi");
		unlink($this->TMP_DIR . "/$hash.ps");
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
		preg_match_all("/\[tex\](.*?)\[\/tex\]/si", $text, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			$position = strpos($text, $matches[0][$i]);
			$thunk = $matches[1][$i];
			$hash = md5($thunk);
			$full_name = $this->CACHE_DIR . "/" .
			$hash . ".png";
			$url = $this->URL_PATH . "/" .
			$hash . ".png";
			if (!is_file($full_name)) {
				$this->render_latex($thunk, $hash);
				$this->cleanup($hash);
			} else
				touch($full_name);
			$text = substr_replace($text, "<img src=\"$url\" alt=\"Formula: $i\" />", $position, strlen($matches[0][$i]));
		}
		exec("find " .  $uploadDir . "/cache -type f -mtime +14 | xargs rm -f");
		return $text;
	}

}
