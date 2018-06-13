<?php
/**
 * Mobile Device Detect (MDD)
 *
 * @package MDD
 * @author emanuele
 * @2nd author feline
 * @copyright the class uagent_info is copyright of Anthony Hand (see Subs-MobileDetect.php for details)
 * @copyright 2012 feline, emanuele, Simple Machines
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0 (AL2)
 *
 * @version 0.2.2
 */

/**
 * Simple function for the hook
 * Since I'm missusing integrate_verify_user the function SHALL not return anything!
 */
function setThemeForMobileDevices()
{
	global $modSettings;

	// Reasoning:
	//   * if the user has already a session with a theme fine, don't need to mess with it
	//   * if the mobile theme is not set it's useless to check
	//   * CheckIsMobile already takes care to see if $context['MobilDevice'] is set
	if (!isset($_SESSION['id_theme']) && isset($modSettings['mobile_theme_id']) && CheckIsMobile())
	{
		$_SESSION['id_theme'] = $modSettings['mobile_theme_id'];
		// On-the-fly override settings (i.e. if admins didn't set allow change themes)
		$modSettings['theme_allow'] = true;
	}
}

/**
 * This function checks if the current user is using a mobile device
 *
 * It also populates the array $context['MobilDevices'] with:
 *   'isMobile' => (bool) true/false,
 *   'device' => (array) list of detected devices,
 *
 * @param: $device, boolean - if true the function returns the device name (array),
 *                            if false returns only if is mobile or not (true/false)
 * @return: boolean true if the user is using a mobile device, false if not
 */
function CheckIsMobile($device = false)
{
	global $context;

	if(isset($context['MobilDevice']))
	{
		if ($device)
			return $context['MobilDevice']['device'];
		else
			return $context['MobilDevice']['isMobile'];
	}

	$useragent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
	$context['MobilDevice'] = array(
		'isMobile' => false,
		'device' => array(),
	);

	// These strings cannot be used in isMobile because are way too generic
	$genericStrings = array(
		'engineWebKit' => 'webkit',
		'deviceMacPpc' => 'macintosh', //Used for disambiguation
		'deviceWindows' => 'windows',
		'devicePpc' => 'ppc', //Stands for PocketPC
		'linux' => 'linux',
		'engineOpera' => 'opera', //Popular browser
		'deviceTablet' => 'tablet', //Generic term for slate and tablet devices
	);

	//Initialize some initial smartphone string variables.
	$mobileStrings = array(
		'engineWebKit' => 'webkit',
		'deviceIphone' => 'iphone',
		'deviceIpod' => 'ipod',
		'deviceIpad' => 'ipad',
		'deviceMacPpc' => 'macintosh', //Used for disambiguation

		'deviceAndroid' => 'android',
		'deviceGoogleTV' => 'googletv',
		'deviceXoom' => 'xoom', //Motorola Xoom
		'deviceHtcFlyer' => 'htc_flyer', //HTC Flyer
		'deviceNuvifone' => 'nuvifone', //Garmin Nuvifone
		'deviceGTI9000' => 'gt-i9000', //Samsung Galaxy I9000

		'deviceSymbian' => 'symbian',
		'deviceS60' => 'series60',
		'deviceS70' => 'series70',
		'deviceS80' => 'series80',
		'deviceS90' => 'series90',

		'deviceWinPhone7' => 'windows phone os 7',
		'deviceWinMob' => 'windows ce',
		'deviceWindows' => 'windows',
		'deviceIeMob' => 'iemobile',
		'devicePpc' => 'ppc', //Stands for PocketPC
		'enginePie' => 'wm5 pie', //An old Windows Mobile

		'deviceBB' => 'blackberry',
		'vndRIM' => 'vnd.rim', //Detectable when BB devices emulate IE or Firefox
		'deviceBBStorm' => 'blackberry95',  //Storm 1 and 2
		'deviceBBBold' => 'blackberry97', //Bold 97x0 (non-touch)
		'deviceBBBoldTouch' => 'blackberry 99', //Bold 99x0 (touchscreen)
		'deviceBBTour' => 'blackberry96', //Tour
		'deviceBBCurve' => 'blackberry89', //Curve2
		'deviceBBTorch' => 'blackberry 98', //Torch
		'deviceBBPlaybook' => 'playbook', //PlayBook tablet

		'devicePalm' => 'palm',
		'deviceWebOS' => 'webos', //For Palm's line of WebOS devices
		'deviceWebOShp' => 'hpwos', //For HP's line of WebOS devices

		'engineBlazer' => 'blazer', //Old Palm browser
		'engineXiino' => 'xiino', //Another old Palm

		'deviceKindle' => 'kindle', //Amazon Kindle, eInk one.

		//Initialize variables for mobile-specific content.
		'vndwap' => 'vnd.wap',
		'wml' => 'wml',

		//Initialize variables for other random devices and mobile browsers.
		'deviceTablet' => 'tablet', //Generic term for slate and tablet devices
		'deviceBrew' => 'brew',
		'deviceDanger' => 'danger',
		'deviceHiptop' => 'hiptop',
		'devicePlaystation' => 'playstation',
		'deviceNintendoDs' => 'nitro',
		'deviceNintendo' => 'nintendo',
		'deviceWii' => 'wii',
		'deviceXbox' => 'xbox',
		'deviceArchos' => 'archos',

		'engineOpera' => 'opera', //Popular browser
		'engineNetfront' => 'netfront', //Common embedded OS browser
		'engineUpBrowser' => 'up.browser', //common on some phones
		'engineOpenWeb' => 'openweb', //Transcoding by OpenWave server
		'deviceMidp' => 'midp', //a mobile Java technology
		'uplink' => 'up.link',
		'engineTelecaQ' => 'teleca q', //a modern feature phone browser

		'devicePda' => 'pda', //some devices report themselves as PDAs
		'mini' => 'mini',  //Some mobile browsers put 'mini' in their names.
		'mobile' => 'mobile', //Some mobile browsers put 'mobile' in their user agent strings.
		'mobi' => 'mobi', //Some mobile browsers put 'mobi' in their user agent strings.

		//Use Maemo, Tablet, and Linux to test for Nokia's Internet Tablets.
		'maemo' => 'maemo',
		'linux' => 'linux',
		'qtembedded' => 'qt embedded', //for Sony Mylo and others
		'mylocom2' => 'com2', //for Sony Mylo also

		//In some UserAgents, the only clue is the manufacturer.
		'manuSonyEricsson' => 'sonyericsson',
		'manuericsson' => 'ericsson',
		'manuSamsung1' => 'sec-sgh',
		'manuSony' => 'sony',
		'manuHtc' => 'htc', //Popular Android and WinMo manufacturer

		//In some UserAgents, the only clue is the operator.
		'svcDocomo' => 'docomo',
		'svcKddi' => 'kddi',
		'svcVodafone' => 'vodafone',
	);

	$find = implode('\b|', array_diff($mobileStrings, $genericStrings));
	if(preg_match_all('~(' . $find . '\b)~i', $useragent, $tmp))
	{
		$context['MobilDevice'] = array(
			'isMobile' => true,
			'device' => $tmp[1]
		);
	}

	if ($device)
		return $context['MobilDevice']['device'];
	else
		return $context['MobilDevice']['isMobile'];
}

/**
 * Compared to the original class I (emanuele) slightly changed the strcture of the "detection strings"
 * in order to facilitate the use in the newly introduced function isMobile.
 *
 * Copyright 2010-2012, Anthony Hand
 *
 * Class version date: January 21, 2012
 *              Update:
 *              - Added the constructor method per new features in PHP 5.0: __construct().
 *              - Moved Windows Phone 7 to the iPhone Tier. WP7.5's IE 9-based browser is good enough now.
 *              - Added a new variable for 2 versions of the new BlackBerry Bold Touch (9900 and 9930): deviceBBBoldTouch.
 *              - Updated DetectBlackBerryTouch() to support the 2 versions of the new BlackBerry Bold Touch (9900 and 9930).
 *              - Updated DetectKindle() to focus on eInk devices only. The Kindle Fire should be detected as a regular Android device.
 *
 * Class version date: August 22, 2011
 *              Update:
 *              - Updated DetectAndroidTablet() to fix a bug introduced in the last fix! The true/false returns were mixed up.
 *
 * Class version date: August 16, 2011
 *              Update:
 *              - Updated DetectAndroidTablet() to exclude Opera Mini, which was falsely reporting as running on a tablet device when on a phone.
 *
 * Class version date: August 7, 2011
 *              Update:
 *              - The Opera for Android browser doesn't follow Google's recommended useragent string guidelines, so some fixes were needed.
 *              - Updated DetectAndroidPhone() and DetectAndroidTablet() to properly detect devices running Opera Mobile.
 *              - Created 2 new methods: DetectOperaAndroidPhone() and DetectOperaAndroidTablet().
 *              - Updated DetectTierIphone(). Removed the call to DetectMaemoTablet(), an obsolete mobile OS.
 *
 *
 * LICENSE INFORMATION
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *        http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 *
 * ABOUT uagent_info PROJECT
 *   uagent_info Owner: Anthony Hand
 *   Email: anthony.hand@gmail.com
 *   Web Site: http://www.mobileesp.com
 *   Source Files: http://code.google.com/p/mobileesp/
 *
 *   Versions of this code are available for:
 *      PHP, JavaScript, Java, ASP.NET (C#), and Ruby
 *
 * *******************************************
 */

/**
 * The uagent_info class encapsulates information about
 *   a browser's connection to your web site.
 *   You can use it to find out whether the browser asking for
 *   your site's content is probably running on a mobile device.
 *   The methods were written so you can be as granular as you want.
 *   For example, enquiring whether it's as specific as an iPod Touch or
 *   as general as a smartphone class device.
 *   The object's methods return 1 for true, or 0 for false.
 */
class uagent_info
{
	var $useragent = "";
	var $httpaccept = "";

	//Let's store values for quickly accessing the same info multiple times.
	var $isIphone = 0; //Stores whether the device is an iPhone or iPod Touch.
	var $isAndroidPhone = 0; //Stores whether the device is a (small-ish) Android phone or media player.
	var $isTierTablet = 0; //Stores whether is the Tablet (HTML5-capable, larger screen) tier of devices.
	var $isTierIphone = 0; //Stores whether is the iPhone tier of devices.
	var $isTierRichCss = 0; //Stores whether the device can probably support Rich CSS, but JavaScript support is not assumed. (e.g., newer BlackBerry, Windows Mobile)
	var $isTierGenericMobile = 0; //Stores whether it is another mobile device, which cannot be assumed to support CSS or JS (eg, older BlackBerry, RAZR)

	// These strings cannot be used in isMobile because are way too generic
	var $genericStrings = array(
		'engineWebKit' => 'webkit',
		'deviceMacPpc' => 'macintosh', //Used for disambiguation
		'deviceWindows' => 'windows',
		'devicePpc' => 'ppc', //Stands for PocketPC
		'linux' => 'linux',
		'engineOpera' => 'opera', //Popular browser
		'deviceTablet' => 'tablet', //Generic term for slate and tablet devices
	);

	//Initialize some initial smartphone string variables.
	var $mobileStrings = array(
		'engineWebKit' => 'webkit',
		'deviceIphone' => 'iphone',
		'deviceIpod' => 'ipod',
		'deviceIpad' => 'ipad',
		'deviceMacPpc' => 'macintosh', //Used for disambiguation

		'deviceAndroid' => 'android',
		'deviceGoogleTV' => 'googletv',
		'deviceXoom' => 'xoom', //Motorola Xoom
		'deviceHtcFlyer' => 'htc_flyer', //HTC Flyer

		'deviceNuvifone' => 'nuvifone', //Garmin Nuvifone

		'deviceSymbian' => 'symbian',
		'deviceS60' => 'series60',
		'deviceS70' => 'series70',
		'deviceS80' => 'series80',
		'deviceS90' => 'series90',

		'deviceWinPhone7' => 'windows phone os 7',
		'deviceWinMob' => 'windows ce',
		'deviceWindows' => 'windows',
		'deviceIeMob' => 'iemobile',
		'devicePpc' => 'ppc', //Stands for PocketPC
		'enginePie' => 'wm5 pie', //An old Windows Mobile

		'deviceBB' => 'blackberry',
		'vndRIM' => 'vnd.rim', //Detectable when BB devices emulate IE or Firefox
		'deviceBBStorm' => 'blackberry95',  //Storm 1 and 2
		'deviceBBBold' => 'blackberry97', //Bold 97x0 (non-touch)
		'deviceBBBoldTouch' => 'blackberry 99', //Bold 99x0 (touchscreen)
		'deviceBBTour' => 'blackberry96', //Tour
		'deviceBBCurve' => 'blackberry89', //Curve2
		'deviceBBTorch' => 'blackberry 98', //Torch
		'deviceBBPlaybook' => 'playbook', //PlayBook tablet

		'devicePalm' => 'palm',
		'deviceWebOS' => 'webos', //For Palm's line of WebOS devices
		'deviceWebOShp' => 'hpwos', //For HP's line of WebOS devices

		'engineBlazer' => 'blazer', //Old Palm browser
		'engineXiino' => 'xiino', //Another old Palm

		'deviceKindle' => 'kindle', //Amazon Kindle, eInk one.

		//Initialize variables for mobile-specific content.
		'vndwap' => 'vnd.wap',
		'wml' => 'wml',

		//Initialize variables for other random devices and mobile browsers.
		'deviceTablet' => 'tablet', //Generic term for slate and tablet devices
		'deviceBrew' => 'brew',
		'deviceDanger' => 'danger',
		'deviceHiptop' => 'hiptop',
		'devicePlaystation' => 'playstation',
		'deviceNintendoDs' => 'nitro',
		'deviceNintendo' => 'nintendo',
		'deviceWii' => 'wii',
		'deviceXbox' => 'xbox',
		'deviceArchos' => 'archos',

		'engineOpera' => 'opera', //Popular browser
		'engineNetfront' => 'netfront', //Common embedded OS browser
		'engineUpBrowser' => 'up.browser', //common on some phones
		'engineOpenWeb' => 'openweb', //Transcoding by OpenWave server
		'deviceMidp' => 'midp', //a mobile Java technology
		'uplink' => 'up.link',
		'engineTelecaQ' => 'teleca q', //a modern feature phone browser

		'devicePda' => 'pda', //some devices report themselves as PDAs
		'mini' => 'mini',  //Some mobile browsers put 'mini' in their names.
		'mobile' => 'mobile', //Some mobile browsers put 'mobile' in their user agent strings.
		'mobi' => 'mobi', //Some mobile browsers put 'mobi' in their user agent strings.

		//Use Maemo, Tablet, and Linux to test for Nokia's Internet Tablets.
		'maemo' => 'maemo',
		'linux' => 'linux',
		'qtembedded' => 'qt embedded', //for Sony Mylo and others
		'mylocom2' => 'com2', //for Sony Mylo also

		//In some UserAgents, the only clue is the manufacturer.
		'manuSonyEricsson' => 'sonyericsson',
		'manuericsson' => 'ericsson',
		'manuSamsung1' => 'sec-sgh',
		'manuSony' => 'sony',
		'manuHtc' => 'htc', //Popular Android and WinMo manufacturer

		//In some UserAgents, the only clue is the operator.
		'svcDocomo' => 'docomo',
		'svcKddi' => 'kddi',
		'svcVodafone' => 'vodafone',
	);

	//Disambiguation strings.
	var $disUpdate = "update"; //pda vs. update

	// We don't know the device so we assume it's not a mobile-thing
	var $device = '';
	var $is_mobile = false;
	var $previously_detected = false;

	/**
	 * The object initializer. Initializes several default variables.
	 */
	function uagent_info()
	{
		$this->useragent = isset($_SERVER['HTTP_USER_AGENT'])?strtolower($_SERVER['HTTP_USER_AGENT']):'';
		$this->httpaccept = isset($_SERVER['HTTP_ACCEPT'])?strtolower($_SERVER['HTTP_ACCEPT']):'';
	}

	function getDevice()
	{
		if (empty($this->device))
			//Let's initialize some values to save cycles later.
			$this->InitDeviceScan();

		return $this->device;
	}

	function isMobile()
	{
		if (!$this->previously_detected)
		{
			$match = implode('\b|', array_diff($this->mobileStrings, $this->genericStrings));
			$this->is_mobile = preg_match('~(' . $match . '\b)~i', $this->useragent);
			$this->previously_detected = true;
		}

		return $this->is_mobile;
	}

	/**
	 * Initialize Key Stored Values.
	 */
	function InitDeviceScan()
	{
		global $isIphone, $isAndroidPhone, $isTierTablet, $isTierIphone;

		//We'll use these 4 variables to speed other processing. They're super common.
		$this->isIphone = $this->DetectIphoneOrIpod();
		$this->isAndroidPhone = $this->DetectAndroidPhone();
		$this->isTierIphone = $this->DetectTierIphone();
		$this->isTierTablet = $this->DetectTierTablet();

		//Optional: Comment these out if you don't need them.
		global $isTierRichCss, $isTierGenericMobile;
		$this->isTierRichCss = $this->DetectTierRichCss();
		$this->isTierGenericMobile = $this->DetectTierOtherPhones();
	}

	/**
	 * Returns the contents of the User Agent value, in lower case.
	 */
	function Get_Uagent()
	{
		return $this->useragent;
	}

	/**
	 * Returns the contents of the HTTP Accept value, in lower case.
	 */
	function Get_HttpAccept()
	{
		return $this->httpaccept;
	}


	/**
	 * Detects if the current device is an iPhone.
	 */
	function DetectIphone()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceIphone']) > -1)
		{
			//The iPad and iPod Touch say they're an iPhone. So let's disambiguate.
			if ($this->DetectIpad() == true || $this->DetectIpod() == true)
				return false;
			//Yay! It's an iPhone!
			else
				return true;
		}
		else
			return false;
	}

	/**
	 * Detects if the current device is an iPod Touch.
	 */
	function DetectIpod()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceIpod']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is an iPad tablet.
	 */
	function DetectIpad()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceIpad']) > -1 && $this->DetectWebkit() == true)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is an iPhone or iPod Touch.
	 */
	function DetectIphoneOrIpod()
	{
		//We repeat the searches here because some iPods may report themselves as an iPhone, which would be okay.
		if (stripos($this->useragent, $this->mobileStrings['deviceIphone']) > -1 || stripos($this->useragent, $this->mobileStrings['deviceIpod']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects *any* iOS device: iPhone, iPod Touch, iPad.
	 */
	function DetectIos()
	{
		if (($this->DetectIphoneOrIpod() == true) || ($this->DetectIpad() == true))
			return true;
		else
			return false;
	}


	/**
	 * Detects *any* Android OS-based device: phone, tablet, and multi-media player.
	 * Also detects Google TV.
	 */
	function DetectAndroid()
	{
		if ((stripos($this->useragent, $this->mobileStrings['deviceAndroid']) > -1) || ($this->DetectGoogleTV() == true))
			return true;
		//Special check for the HTC Flyer 7" tablet
		if ((stripos($this->useragent, $this->mobileStrings['deviceHtcFlyer']) > -1))
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is a (small-ish) Android OS-based device
	 * used for calling and/or multi-media (like a Samsung Galaxy Player).
	 * Google says these devices will have 'Android' AND 'mobile' in user agent.
	 * Ignores tablets (Honeycomb and later).
	 */
	function DetectAndroidPhone()
	{
		if (($this->DetectAndroid() == true) && (stripos($this->useragent, $this->mobileStrings['mobile']) > -1))
			return true;
		//Special check for Android phones with Opera Mobile. They should report here.
		if (($this->DetectOperaAndroidPhone() == true))
			return true;
		//Special check for the HTC Flyer 7" tablet. It should report here.
		if ((stripos($this->useragent, $this->mobileStrings['deviceHtcFlyer']) > -1))
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is a (self-reported) Android tablet.
	 * Google says these devices will have 'Android' and NOT 'mobile' in their user agent.
	 */
	function DetectAndroidTablet()
	{
		//First, let's make sure we're on an Android device.
		if ($this->DetectAndroid() == false)
			return false;

		//Special check for Opera Android Phones. They should NOT report here.
		if ($this->DetectOperaMobile() == true)
			return false;
		//Special check for the HTC Flyer 7" tablet. It should NOT report here.
		if ((stripos($this->useragent, $this->mobileStrings['deviceHtcFlyer']) > -1))
			return false;

		//Otherwise, if it's Android and does NOT have 'mobile' in it, Google says it's a tablet.
		if (stripos($this->useragent, $this->mobileStrings['mobile']) > -1)
			return false;
		else
			return true;
	}

	/**
	 * Detects if the current device is an Android OS-based device and
	 *   the browser is based on WebKit.
	 */
	function DetectAndroidWebKit()
	{
		if (($this->DetectAndroid() == true) && ($this->DetectWebkit() == true))
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is a GoogleTV.
	 */
	function DetectGoogleTV()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceGoogleTV']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is based on WebKit.
	 */
	function DetectWebkit()
	{
		if (stripos($this->useragent, $this->mobileStrings['engineWebKit']) > -1)
			return true;
		else
			return false;
	}


	/**
	 * Detects if the current browser is the Nokia S60 Open Source Browser.
	 */
	function DetectS60OssBrowser()
	{
		//First, test for WebKit, then make sure it's either Symbian or S60.
		if ($this->DetectWebkit() == true)
		{
			if (stripos($this->useragent, $this->mobileStrings['deviceSymbian']) > -1 || stripos($this->useragent, $this->mobileStrings['deviceS60']) > -1)
				return true;
			else
				return false;
		}
		else
				return false;
	}

	/**
	 * Detects if the current device is any Symbian OS-based device,
	 *   including older S60, Series 70, Series 80, Series 90, and UIQ,
	 *   or other browsers running on these devices.
	 */
	function DetectSymbianOS()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceSymbian']) > -1 ||
				stripos($this->useragent, $this->mobileStrings['deviceS60']) > -1 ||
				stripos($this->useragent, $this->mobileStrings['deviceS70']) > -1 ||
				stripos($this->useragent, $this->mobileStrings['deviceS80']) > -1 ||
				stripos($this->useragent, $this->mobileStrings['deviceS90']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is a
	 * Windows Phone 7 device.
	 */
	function DetectWindowsPhone7()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceWinPhone7']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is a Windows Mobile device.
	 * Excludes Windows Phone 7 devices.
	 * Focuses on Windows Mobile 6.xx and earlier.
	 */
	function DetectWindowsMobile()
	{
		if ($this->DetectWindowsPhone7() == true)
			return false;
		//Most devices use 'Windows CE', but some report 'iemobile'
		//  and some older ones report as 'PIE' for Pocket IE.
		if (stripos($this->useragent, $this->mobileStrings['deviceWinMob']) > -1 ||
				stripos($this->useragent, $this->mobileStrings['deviceIeMob']) > -1 ||
				stripos($this->useragent, $this->mobileStrings['enginePie']) > -1)
			return true;
		//Test for Windows Mobile PPC but not old Macintosh PowerPC.
		if (stripos($this->useragent, $this->mobileStrings['devicePpc']) > -1 && !(stripos($this->useragent, $this->mobileStrings['deviceMacPpc']) > 1))
			return true;
		//Test for certain Windwos Mobile-based HTC devices.
		if (stripos($this->useragent, $this->mobileStrings['manuHtc']) > -1 && stripos($this->useragent, $this->mobileStrings['deviceWindows']) > -1)
			return true;
		if ($this->DetectWapWml() == true && stripos($this->useragent, $this->mobileStrings['deviceWindows']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is any BlackBerry device.
	 * Includes the PlayBook.
	 */
	function DetectBlackBerry()
	{
		if ((stripos($this->useragent, $this->mobileStrings['deviceBB']) > -1) || (stripos($this->httpaccept, $this->vndRIM) > -1))
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is on a BlackBerry tablet device.
	 *    Examples: PlayBook
	 */
	function DetectBlackBerryTablet()
	{
		if ((stripos($this->useragent, $this->mobileStrings['deviceBBPlaybook']) > -1))
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is a BlackBerry phone device AND uses a
	 *    WebKit-based browser. These are signatures for the new BlackBerry OS 6.
	 *    Examples: Torch. Includes the Playbook.
	 */
	function DetectBlackBerryWebKit()
	{
		if (($this->DetectBlackBerry() == true) && ($this->DetectWebkit() == true))
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is a BlackBerry Touch phone
	 *    device, such as the Storm, Torch, and Bold Touch. Excludes the Playbook.
	 */
	function DetectBlackBerryTouch()
	{
			if ((stripos($this->useragent, $this->mobileStrings['deviceBBStorm']) > -1) ||
					(stripos($this->useragent, $this->mobileStrings['deviceBBTorch']) > -1) ||
					(stripos($this->useragent, $this->mobileStrings['deviceBBBoldTouch']) > -1))
				return true;
			else
				return false;
	}

	/**
	 * Detects if the current browser is a BlackBerry OS 5 device AND
	 *    has a more capable recent browser. Excludes the Playbook.
	 *    Examples, Storm, Bold, Tour, Curve2
	 *    Excludes the new BlackBerry OS 6 and 7 browser!!
	 */
	function DetectBlackBerryHigh()
	{
		//Disambiguate for BlackBerry OS 6 or 7 (WebKit) browser
		if ($this->DetectBlackBerryWebKit() == true)
			return false;
		if ($this->DetectBlackBerry() == true)
		{
			if (($this->DetectBlackBerryTouch() == true) ||
					stripos($this->useragent, $this->mobileStrings['deviceBBBold']) > -1 ||
					stripos($this->useragent, $this->mobileStrings['deviceBBTour']) > -1 ||
					stripos($this->useragent, $this->mobileStrings['deviceBBCurve']) > -1)
				return true;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Detects if the current browser is a BlackBerry device AND
	 *    has an older, less capable browser.
	 *    Examples: Pearl, 8800, Curve1.
	 */
	function DetectBlackBerryLow()
	{
		if ($this->DetectBlackBerry() == true)
		{
			//Assume that if it's not in the High tier, then it's Low.
			if (($this->DetectBlackBerryHigh() == true) || ($this->DetectBlackBerryWebKit() == true))
				return false;
			else
				return true;
		}
		else
			return false;
	}

	/**
	 * Detects if the current browser is on a PalmOS device.
	 */
	function DetectPalmOS()
	{
		//Most devices nowadays report as 'Palm', but some older ones reported as Blazer or Xiino.
		if (stripos($this->useragent, $this->mobileStrings['devicePalm']) > -1 ||
				stripos($this->useragent, $this->mobileStrings['engineBlazer']) > -1 ||
				stripos($this->useragent, $this->mobileStrings['engineXiino']) > -1)
		{
			//Make sure it's not WebOS first
			if ($this->DetectPalmWebOS() == true)
				return false;
			else
				return true;
		}
		else
			return false;
	}


	/**
	 * Detects if the current browser is on a Palm device
	 *   running the new WebOS.
	 */
	function DetectPalmWebOS()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceWebOS']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is on an HP tablet running WebOS.
	 */
	function DetectWebOSTablet()
	{
		if ((stripos($this->useragent, $this->mobileStrings['deviceWebOShp']) > -1) && (stripos($this->useragent, $this->mobileStrings['deviceTablet']) > -1))
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is a
	 *   Garmin Nuvifone.
	 */
	function DetectGarminNuvifone()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceNuvifone']) > -1)
			return true;
		else
			return false;
	}


	/**
	 * Check to see whether the device is any device
	 *   in the 'smartphone' category.
	 */
	function DetectSmartphone()
	{
		global $isIphone, $isAndroidPhone, $isTierIphone;

		if (($this->isIphone == true) ||
				($this->isAndroidPhone == true) ||
				($this->isTierIphone == true) ||
				($this->DetectS60OssBrowser() == true) ||
				($this->DetectSymbianOS() == true) ||
				($this->DetectWindowsMobile() == true) ||
				($this->DetectWindowsPhone7() == true) ||
				($this->DetectBlackBerry() == true) ||
				($this->DetectPalmWebOS() == true) ||
				($this->DetectPalmOS() == true) ||
				($this->DetectGarminNuvifone() == true))
			return true;
		else
			return false;
	}


	/**
	 * Detects whether the device is a Brew-powered device.
	 */
	function DetectBrewDevice()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceBrew']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects the Danger Hiptop device.
	 */
	function DetectDangerHiptop()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceDanger']) > -1 || stripos($this->useragent, $this->mobileStrings['deviceHiptop']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is Opera Mobile or Mini.
	 */
	function DetectOperaMobile()
	{
		if (stripos($this->useragent, $this->mobileStrings['engineOpera']) > -1)
		{
			if ((stripos($this->useragent, $this->mobileStrings['mini']) > -1) || (stripos($this->useragent, $this->mobileStrings['mobi']) > -1))
				return true;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Detects if the current browser is Opera Mobile
	 * running on an Android phone.
	 */
	function DetectOperaAndroidPhone()
	{
		if ((stripos($this->useragent, $this->mobileStrings['engineOpera']) > -1) &&
				(stripos($this->useragent, $this->mobileStrings['deviceAndroid']) > -1) &&
				(stripos($this->useragent, $this->mobileStrings['mobi']) > -1))
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is Opera Mobile
	 * running on an Android tablet.
	 */
	function DetectOperaAndroidTablet()
	{
		if ((stripos($this->useragent, $this->mobileStrings['engineOpera']) > -1) &&
				(stripos($this->useragent, $this->mobileStrings['deviceAndroid']) > -1) &&
				(stripos($this->useragent, $this->mobileStrings['deviceTablet']) > -1))
			return true;
		else
			return false;
	}

	/**
	 * Detects whether the device supports WAP or WML.
	 */
	function DetectWapWml()
	{
		if (stripos($this->httpaccept, $this->vndwap) > -1 || stripos($this->httpaccept, $this->wml) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is an Amazon Kindle (eInk devices only).
	 * Note: For the Kindle Fire, use the normal Android methods.
	 */
	function DetectKindle()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceKindle']) > -1 && DetectAndroid() == false)
			return true;
		else
			return false;
	}


	/**
	 * The quick way to detect for a mobile device.
	 *   Will probably detect most recent/current mid-tier Feature Phones
	 *   as well as smartphone-class devices. Excludes Apple iPads and other modern tablets.
	 */
	function DetectMobileQuick()
	{
		//Let's exclude tablets
		if ($this->isTierTablet == true)
			return false;

		//Most mobile browsing is done on smartphones
		if ($this->DetectSmartphone() == true)
			return true;

		if (($this->DetectWapWml() == true) ||
				($this->DetectBrewDevice() == true) ||
				($this->DetectOperaMobile() == true))
			return true;

		if ((stripos($this->useragent, $this->mobileStrings['engineNetfront']) > -1) ||
				(stripos($this->useragent, $this->mobileStrings['engineUpBrowser']) > -1) ||
				(stripos($this->useragent, $this->mobileStrings['engineOpenWeb']) > -1))
			return true;

		if (($this->DetectDangerHiptop() == true) ||
				($this->DetectMidpCapable() == true) ||
				($this->DetectMaemoTablet() == true) ||
				($this->DetectArchos() == true))
			return true;

		if ((stripos($this->useragent, $this->mobileStrings['devicePda']) > -1) && !(stripos($this->useragent, $this->mobileStrings['disUpdate']) > -1))
			return true;

		if (stripos($this->useragent, $this->mobileStrings['mobile']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is a Sony Playstation.
	 */
	function DetectSonyPlaystation()
	{
		if (stripos($this->useragent, $this->mobileStrings['devicePlaystation']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is a Nintendo game device.
	 */
	function DetectNintendo()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceNintendo']) > -1 ||
				stripos($this->useragent, $this->mobileStrings['deviceWii']) > -1 ||
				stripos($this->useragent, $this->mobileStrings['deviceNintendoDs']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is a Microsoft Xbox.
	 */
	function DetectXbox()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceXbox']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is an Internet-capable game console.
	 */
	function DetectGameConsole()
	{
		if ($this->DetectSonyPlaystation() == true)
			return true;
		elseif ($this->DetectNintendo() == true)
			return true;
		elseif ($this->DetectXbox() == true)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device supports MIDP, a mobile Java technology.
	 */
	function DetectMidpCapable()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceMidp']) > -1 || stripos($this->httpaccept, $this->deviceMidp) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is on one of the Maemo-based Nokia Internet Tablets.
	 */
	function DetectMaemoTablet()
	{
		if (stripos($this->useragent, $this->mobileStrings['maemo']) > -1)
			return true;
		//For Nokia N810, must be Linux + Tablet, or else it could be something else.
		if ((stripos($this->useragent, $this->mobileStrings['linux']) > -1) &&
				(stripos($this->useragent, $this->mobileStrings['deviceTablet']) > -1) &&
				($this->DetectWebOSTablet() == false) &&
				($this->DetectAndroid() == false))
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current device is an Archos media player/Internet tablet.
	 */
	function DetectArchos()
	{
		if (stripos($this->useragent, $this->mobileStrings['deviceArchos']) > -1)
			return true;
		else
			return false;
	}

	/**
	 * Detects if the current browser is a Sony Mylo device.
	 */
	function DetectSonyMylo()
	{
		if (stripos($this->useragent, $this->mobileStrings['manuSony']) > -1)
		{
			if ((stripos($this->useragent, $this->mobileStrings['qtembedded']) > -1) || (stripos($this->useragent, $this->mobileStrings['mylocom2']) > -1))
				return true;
			else
				return false;
		}
		else
			return false;
	}


	/**
	 * The longer and more thorough way to detect for a mobile device.
	 *   Will probably detect most feature phones,
	 *   smartphone-class devices, Internet Tablets,
	 *   Internet-enabled game consoles, etc.
	 *   This ought to catch a lot of the more obscure and older devices, also --
	 *   but no promises on thoroughness!
	 */
	function DetectMobileLong()
	{
		if ($this->DetectMobileQuick() == true)
			return true;
		if ($this->DetectGameConsole() == true)
			return true;
		if ($this->DetectSonyMylo() == true)
			return true;

		//Detect older phones from certain manufacturers and operators.
		if (stripos($this->useragent, $this->mobileStrings['uplink']) > -1)
			return true;
		if (stripos($this->useragent, $this->mobileStrings['manuSonyEricsson']) > -1)
			return true;
		if (stripos($this->useragent, $this->mobileStrings['manuericsson']) > -1)
			return true;

		if (stripos($this->useragent, $this->mobileStrings['manuSamsung1']) > -1)
			return true;
		if (stripos($this->useragent, $this->mobileStrings['svcDocomo']) > -1)
			return true;
		if (stripos($this->useragent, $this->mobileStrings['svcKddi']) > -1)
			return true;
		if (stripos($this->useragent, $this->mobileStrings['svcVodafone']) > -1)
			return true;
		else
			return false;
	}



/**
 * For Mobile Web Site Design
 */

	/**
	 * The quick way to detect for a tier of devices.
	 *   This method detects for the new generation of
	 *   HTML 5 capable, larger screen tablets.
	 *   Includes iPad, Android (e.g., Xoom), BB Playbook, WebOS, etc.
	 */
	function DetectTierTablet()
	{
		if (($this->DetectIpad() == true) ||
				($this->DetectAndroidTablet() == true) ||
				($this->DetectBlackBerryTablet() == true) ||
				($this->DetectWebOSTablet() == true))
			return true;
		else
			return false;
	}


	/**
	 * The quick way to detect for a tier of devices.
	 *   This method detects for devices which can
	 *   display iPhone-optimized web content.
	 *   Includes iPhone, iPod Touch, Android, Windows Phone 7, WebOS, etc.
	 */
	function DetectTierIphone()
	{
		if (($this->isIphone == true) || ($this->isAndroidPhone == true))
			return true;
		if (($this->DetectBlackBerryWebKit() == true) && ($this->DetectBlackBerryTouch() == true))
			return true;
		if ($this->DetectWindowsPhone7() == true)
			return true;
		if ($this->DetectPalmWebOS() == true)
			return true;
		if ($this->DetectGarminNuvifone() == true)
			return true;
		else
			return false;
	}

	/**
	 * The quick way to detect for a tier of devices.
	 *   This method detects for devices which are likely to be capable
	 *   of viewing CSS content optimized for the iPhone,
	 *   but may not necessarily support JavaScript.
	 *   Excludes all iPhone Tier devices.
	 */
	function DetectTierRichCss()
	{
		if ($this->DetectMobileQuick() == true)
		{
			if (($this->DetectTierIphone() == true))
				return false;

			//The following devices are explicitly ok.
			if ($this->DetectWebkit() == true) //Any WebKit
				return true;
			if ($this->DetectS60OssBrowser() == true)
				return true;

			//Note: 'High' BlackBerry devices ONLY
			if ($this->DetectBlackBerryHigh() == true)
				return true;

			//Older Windows 'Mobile' isn't good enough for iPhone Tier.
			if ($this->DetectWindowsMobile() == true)
				return true;
			if (stripos($this->useragent, $this->mobileStrings['engineTelecaQ']) > -1)
				return true;

			//default
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * The quick way to detect for a tier of devices.
	 *   This method detects for all other types of phones,
	 *   but excludes the iPhone and RichCSS Tier devices.
	 */
	function DetectTierOtherPhones()
	{
		//Exclude devices in the other 2 categories
		if (($this->DetectMobileLong() == true) &&
				($this->DetectTierIphone() == false) &&
				($this->DetectTierRichCss() == false))
			return true;
		else
			return false;
	}
}

?>
