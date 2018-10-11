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
function BetterProfile_Load_Theme()
{
	// This admin hook must be last hook executed!
	if (isset($_GET['action'], $_GET['area']) && $_GET['action'] == 'profile' && $_GET['area'] == 'betterprofile_ucp')
		add_integration_function('integrate_profile_areas', 'BetterProfile_Profile_Hook', false);
	else
		add_integration_function('integrate_menu_buttons', 'BetterProfile_Menu_Buttons', false);
}

function BetterProfile_Profile_Hook(&$areas)
{
	echo var_export($areas);
	exit;
}

function BetterProfile_Menu_Buttons(&$areas)
{
	global $txt, $scripturl, $context, $sourcedir, $boarddir, $user_info;

	// Retrieve the admin area menu, either from cache or the Admin.php script...
	$profile = &$areas['profile'];
	if (($cached = cache_get_data('betterprofile_' . $user_info['id'], 86400)) == null)
	{
		// Get the current admin menu.  Failure to do so means aborting the menu!
		$contents = @file_get_contents($scripturl . '?action=profile;area=betterprofile_ucp;u=' . $user_info['id']);
		if (substr($contents, 0, 7) != 'array (')
		{
			if (!empty($modSettings['cache_enable']))
				cache_put_data('betterprofile_' . $user_info['id'], 1, 86400);
			return;
		}
		$convert_to_array = create_function('', 'return ' . $contents . ';');
		$profile_areas = $convert_to_array();
		if (!is_array($profile_areas))
		{
			if (!empty($modSettings['cache_enable']))
				cache_put_data('betterprofile_' . $user_info['id'], 1, 86400);
			return;
		}

		// Rebuild the admin menu:
		$cached = array();
		$last = false;
		foreach ($profile_areas as $id1 => $area1)
		{
			// Build first level menu:
			$cached[$id1] = array(
				'title' => $area1['title'],
				'show' => true,
				'sub_buttons' => array(),
			);
				
			// Build second level menus:
			$first = true;
			if (isset($area1['custom_url']) && !empty($area1['custom_url']))
			{
				$cached[$id1]['href'] = $area1['custom_url'];
				$first = false;
			}
			$last = false;
			foreach ($area1['areas'] as $id2 => $area2)
			{
				if (empty($area2['label']))
					continue;
				if ($first)
				{
					$cached[$id1]['href'] = $scripturl . '?action=profile;area=' . $id2;
					$first = false;
				}
				$cached[$id1]['sub_buttons'][$last = $id2] = array(
					'title' => $area2['label'],
					'href' => $scripturl . '?action=profile;area=' . $id2,
					'show' => (!isset($area2['enabled']) || $area2['enabled']) && !empty($area2['permission']['own']) && allowedTo($area2['permission']['own']),
				);

				if ($id2 == 'showposts')
					$cached[$id1]['sub_buttons'][$last = 'showtopics'] = array(
						'title' => $area2['label'] . ': ' . $txt['topics'],
						'href' => $scripturl . '?action=profile;area=showposts;sa=topics',
						'show' => (!isset($area2['enabled']) || $area2['enabled']) && !empty($area2['permission']['own']) && allowedTo($area2['permission']['own']),
					);
			}
			$cached[$id1]['sub_buttons'][$last]['is_last'] = true;
		}

		if (!empty($modSettings['cache_enable']))
			cache_put_data('betterprofile_' . $user_info['id'], $cached, 86400);
		$areas['profile']['sub_buttons'] = $cached;
	}
	elseif (is_array($cached))
		$areas['profile']['sub_buttons'] = $cached;

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

?>