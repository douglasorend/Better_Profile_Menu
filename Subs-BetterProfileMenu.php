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
	// Skip this if we are not requesting the layout of the profile CPL:
	return array( (isset($_GET['action'], $_GET['area'], $_GET['u']) && $_GET['action'] == 'profile' && $_GET['area'] == 'betterprofile_ucp') ? (int) $_GET['u'] : 0);
}

function BetterProfile_Load_Theme()
{
	// This admin hook must be last hook executed!
	add_integration_function('integrate_profile_areas', 'BetterProfile_Profile_Hook', false);
	add_integration_function('integrate_menu_buttons', 'BetterProfile_Menu_Buttons', false);
}

function BetterProfile_Profile_Hook(&$profile_areas)
{
	global $user_info, $scripturl, $txt;

	// Skip this if we are not requesting the layout of the moderator CPL:
	if (!isset($_GET['area']) || !isset($_GET['u']) || $_GET['area'] != 'betterprofile_ucp')
		return;

	// Rebuild the profile menu:
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

			$link = isset($area2['custom_url']) ? $area2['custom_url'] : $scripturl . '?action=profile;area=' . $id2;
			$show = (!isset($area2['enabled']) || $area2['enabled']) && !empty($area2['permission']['own']) && allowedTo($area2['permission']['own']);
			$cached[$id1]['sub_buttons'][$last = $id2] = array(
				'title' => $area2['label'],
				'href' => $link,
				'show' => $show,
			);

			if ($id2 == 'showposts')
				$cached[$id1]['sub_buttons'][$last = 'showtopics'] = array(
					'title' => $area2['label'] . ': ' . $txt['topics'],
					'href' => $link . ';sa=topics',
					'show' => $show,
				);
		}
		$cached[$id1]['sub_buttons'][$last]['is_last'] = true;
	}

	cache_put_data('betterprofile_' . $user_info['id'], $cached, 86400);
	exit;
}

function BetterProfile_Menu_Buttons(&$areas)
{
	global $txt, $scripturl, $user_info, $sourcedir;

	// Gotta prevent an infinite loop here:
	if (isset($_GET['action'], $_GET['area']) && $_GET['action'] == 'profile' && $_GET['area'] == 'betterprofile_ucp')
		return;

	// Can't see the moderation menu?  Then why bother with it?
	if (empty($areas['profile']['show']))
		return;

	// Attempt to get the cached moderator menu:
	$profile = &$areas['profile'];
	$profile['old_buttons'] = $profile['sub_buttons'];
	if (($cached = cache_get_data('betterprofile_' . $user_info['id'], 86400)) == null)
	{
		// Force the moderation code to build our new moderation menu:
		@file_get_contents($scripturl . '?action=profile;area=betterprofile_ucp;u=' . $user_info['id']);
		$cached = cache_get_data('betterprofile_' . $user_info['id'], 86400);
	}
	if (is_array($cached))
		$profile['sub_buttons'] = $cached;

	// Define the rest of the Profile menu:
	if (file_exists($sourcedir . '/Bookmarks.php'))
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

?>