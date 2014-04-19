<?php
App::uses('AppHelper', 'View/Helper');

class AssetHelper extends AppHelper {
	
	// Where to store minified files relative to webroot (remember to set chmod 0777)
	public $dir = 'min';
	
	// Path to CSS minify library relative to app
	public $cssMin = 'Vendor/AssetHelper/CSSMin.php';
	
	// Path to JS minify library relative to app
	public $jsMin = 'Vendor/AssetHelper/JSMin.php';
	
	// Path to JS packer relative to app
	public $jsPacker = 'Vendor/AssetHelper/JSPacker.php';
	
	/**
	 * Minifies JavaScript and CSS files
	 *
	 * $assets = array()   // Array with paths to asset files relative to webroot
	 * $packjs = bool      // Pack javascript files
	 * $forceDebug = bool  // Forces debug mode
	 */
	private function min($assets, $type, $packjs = false, $forceDebug = null) {
		
		// Get forced debug value or use value from CakePHPs Configure
		$debug = ($forceDebug !== null) ? $forceDebug : Configure::read('debug');
		
		// Vars
		$return = $filemtime = $filenames = $md5_filenames = $md5 = '';
		$files = array();
		
		// Loop through assets, add to process array or debugging return value
		foreach($assets as $asset) {
			
			// Remove slash from start of string
			if (substr($asset, 0, 1) == DS) $asset = substr($asset, 1);
			
			// Only process if file exists
			if (file_exists(WWW_ROOT . $asset)) {
				
				// Add original file to return value if in debug mode
				if ($debug > 0) {
					$return .= ($type == 'js' ? '<script src="/' . $asset . '"></script>' : '<link rel="stylesheet" href="/' . $asset . '">') . "\n";
				
				// Otherwise add file to array
				} else {
					$files[] = $asset;
					$filemtime .= filemtime(realpath($asset));
					$filenames .= $asset;
				}
			}
		}
		
		// If in debug mode, echo original files and stop
		if ($debug > 0) {
			echo $return;
			return;
		}
		
		// Remove slash from start and end of dir
		if (substr($this->dir, 0, 1) == DS) $this->dir = substr($this->dir, 1);
		if (substr($this->dir, -1) == DS) $this->dir = substr($this->dir, 0, strlen($this->dir) - 1);
		
		// Try to cminreate folder if not found
		if (!file_exists(WWW_ROOT . $this->dir)) mkdir(WWW_ROOT . $this->dir);
		
		// The filename of the minified version
		$filename = DS . $this->dir . DS . substr(md5($filenames), 0, 6) . '-' . substr(md5($filemtime), 0, 6) . '.' . $type;
		
		// The full filename path
		$filename_root = APP . 'webroot' . $filename;
		
		// If file doesn't exist, create it
		if (!file_exists($filename_root)) {
			
			// Loop through files and add content to single value
			$file_contents = '';
			foreach($files as $file) {
				$file_contents .= file_get_contents($file) . "\n";
			}
			
			// Pack JS
			if ($type == 'js' && $packjs) {
				require_once(APP . $this->jsPacker);
				$packer = new JavaScriptPacker($file_contents, 62, true, false);
				$minified = $packer->pack();
				
			// Minify JS
			} else if ($type == 'js') {
				require_once(APP . $this->jsMin);
				$minified = JSMin::minify($file_contents);
				
			// Minify CSS
			} else {
				require_once(APP . $this->cssMin);
				$minified = CSSMin::minify($file_contents);
			}
			
			// Create file
			file_put_contents($filename_root, $minified);
		}
		
		// Output file
		echo ($type == 'js' ? '<script src="' . $filename . '"></script>' : '<link rel="stylesheet" href="' . $filename . '">') . "\n";
		
		// TODO
		// Delete old files
		
		// TODO
		// URL rewrite:
		// http://minify.googlecode.com/svn/trunk/min/lib/Minify/CSS/UriRewriter.php
		
		// TODO
		// JSMin seems to be outdated, use a different one:
		// https://github.com/rgrove/jsmin-php/
	}
	
	// Wrapper to minify JS files
	public function minJS($assets, $packjs = false, $forceDebug = null) {
		$this->min($assets, 'js', $packjs, $forceDebug);
	}
	
	// Wrapper to minify CSS file
	public function minCSS($assets, $forceDebug = null) {
		$this->min($assets, 'css', false, $forceDebug);
	}
}