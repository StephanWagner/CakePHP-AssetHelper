CakePHP-AssetHelper
===================

AssetHelper is a CakePHP helper that automatically combines and minifies your JavaScript and CSS files, reducing the amount of requests and bandwidth for your webpages.

Get started
-----------

Copy the file AssetHelper.php into /View/Helper and the folder AssetHelper into /Vendor.
Now create the folder where to store the minified files, by default this is /webroot/min. Remember to set CHMOD to 0777.
Setup is complete, you can use the helper now:

	$this->Asset->minCSS(array(
		'css/common.css',
		'css/default.css'
	));
	
	$this->Asset->minJS(array(
		'js/common.js',
		'js/default.js'
	));
  
If your CakePHP app runs in debug mode, if (Configure::read('debug') > 0), the helper will load the original files seperately, if not in debug mode, the helper will combine and minify them.

You can also force the debgging mode to a different value:

	$this->Asset->minCSS($files, 0);       // Debug mode 0
	$this->Asset->minJS($files, false, 2); // Debug mode 2

To pack your Javascript files set the second argument of minJS to true:

	$this->Asset->minJS($files, true);
  
Use the packing option with care, it sometimes seems to fail and I'm not quite sure yet when and why.