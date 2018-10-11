<?php
/**********************************************************************************
* Subs-BetterProfile.php - Subs of the Lazy Admin Menu mod
*********************************************************************************
* This program is distributed in the hope that it is and will be useful, but
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE,
**********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

/**********************************************************************************
* Better Profile Menu hook
**********************************************************************************/
function BetterProfile_Verify_User()
{
	if (isset($_GET['action']) && $_GET['action'] == 'profile' && isset($_GET['area']) && $_GET['area'] == 'betterprofile_ucp')
		return isset($_GET['u']) ? (int) $_GET['u'] : 0;
}

function BetterProfile_Load_Theme()
{
	// This admin hook must be last hook executed!
	add_integration_function('integrate_profile_areas', 'BetterProfile_Profile_Hook', false);
	add_integration_function('integrate_menu_buttons', 'BetterProfile_Menu_Buttons', false);
}

function BetterProfile_Profile_Hook(&$profile_areas)
{
	global $user_info, $scripturl, $txt, $context;

	// Skip this if we are not requesting the layout of the moderator CPL:
	if (empty($user_info['id']) || !isset($_GET['area']) || !isset($_GET['u']) || $_GET['area'] != 'betterprofile_ucp')
		return;

	// Keep from triggering the Forum Hard Hit mod:
	if (!empty($context['HHP_time']))
		unset($_SESSION['HHP_Visits'][$context['HHP_time']]);
			
	// Rebuild the Profile menu:
	$cached = array();
	foreach ($profile_areas as $id1 => $area1)
	{
		// Build first level menu:
		$cached[$id1] = array(
			'title' => $area1['title'],
			'show' => true,
			'sub_buttons' => array(),
		);
		$first = $last = false;
		if (isset($area1['custom_url']) && !empty($area1['custom_url']))
			$first = $cached[$id1]['href'] = $area1['custom_url'];
			
		// Build second level menus:
		foreach ($area1['areas'] as $id2 => $area2)
		{
			if (empty($area2['label']))
				continue;
			if (!$first)
				$first = $cached[$id1]['href'] = $scripturl . '?action=profile;area=' . $id2;

			// Add the entry into the custom menu we're building:
			$link = isset($area2['custom_url']) ? $area2['custom_url'] : $scripturl . '?action=profile;area=' . $id2;
			$show = (!isset($area2['enabled']) || $area2['enabled']) && (empty($area2['permission']['own']) || (!empty($area2['permission']['own']) && allowedTo($area2['permission']['own'])));
			$cached[$id1]['sub_buttons'][$last = $id2] = array(
				'title' => $area2['label'],
				'href' => $link,
				'show' => $show,
			);

			// Let's add the "Show Posts" area to the menu under "Show Topics":
			if ($id2 == 'showposts')
				$cached[$id1]['sub_buttons'][$last = 'showtopics'] = array(
					'title' => $area2['label'] . ': ' . $txt['topics'],
					'href' => $link . ';sa=topics',
					'show' => $show,
				);
		}
		$cached[$id1]['sub_buttons'][$last]['is_last'] = true;
	}

	// Cache the menu we just built for the calling user:
	$func = function_exists('safe_unserialize') ? 'safe_serialize' : 'serialize';
	echo $func($cached);
	exit;
}

function BetterProfile_Menu_Buttons(&$areas)
{
	global $txt, $scripturl, $user_info;

	// Gotta prevent an infinite loop here:
	if (isset($_GET['action']) && $_GET['action'] == 'profile' && isset($_GET['area']) && $_GET['area'] == 'betterprofile_ucp')
		return;

	// Are you a guest, can't view profile, or mod turned off?  Then why bother?
	if (empty($user_info['id']) || empty($areas['profile']['show']) || empty($user_info['bpm_mode']))
		return;

	// Attempt to get the cached Profile menu:
	$Profile = &$areas['profile'];
	if (($cached = cache_get_data('betterprofile_' . $user_info['id'], 86400)) == null || !is_array($cached))
	{
		// Force the profile code to build our new Profile menu:
		$contents = @file_get_contents($scripturl . '?action=profile;area=betterprofile_ucp;u=' . $user_info['id']);
		$func = function_exists('safe_unserialize') ? 'safe_unserialize' : 'unserialize';
		$cached = @$func($contents);
		cache_put_data('betterprofile_' . $user_info['id'], $cached, 86400);
	}
	if (is_array($cached))
	{
		if ($user_info['bpm_mode'] == 2)
			$Profile['href'] = '#" onclick="return false;';
		$Profile['sub_buttons'] = $cached;
	}

	// Define the rest of the Profile menu:
	if (isset($areas['bookmarks']))
	{
		unset($areas['bookmarks']);
		$areas['profile']['sub_buttons']['bookmarks'] = array(
			'title' => $txt['bookmarks'],
			'href' => $scripturl . '?action=bookmarks',
			'show' => allowedTo('make_bookmarks'),
		);
	}
}

function BetterProfile_CoreFeatures(&$core_features)
{
	global $cachedir;
	if (isset($_POST['save']))
		array_map('unlink', glob($cachedir . '/data_*-SMF-betterprofile_*'));
}

function BetterProfile_Profile(&$profile_fields)
{
	global $txt, $user_info;

	$profile_fields['bpm_mode'] = array(
		'type' => 'select',
		'label' => $txt['bpm_mode'],
		'options' => 'global $txt; return array(0 => $txt["bpm_disabled"], 1 => $txt["bpm_enabled"], 2 => $txt["bpm_enabled_no_click"]);',
		'permission' => 'profile_extra',
		'value' => $user_info['bpm_mode'],
	);
}

?>