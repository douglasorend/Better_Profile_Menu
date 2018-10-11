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
function BetterProfile_Menu_Buttons(&$areas)
{
	global $txt, $scripturl, $context, $sourcedir;
	
	// DO NOT RUN if $_GET['xml'] is defined!
	if (isset($_GET['xml']))
		return;
		
	// Load the Profile language, preserving the "time_format" string:
	$loaded = LoadLanguage('', '', false, false, true);
	$old_txt = $txt;
	loadLanguage('Profile');
	
	// Remove the is_last item
	foreach ($areas['profile']['sub_buttons'] as $key => $value)
	{
		if (!empty($value['is_last']))
			unset($areas['profile']['sub_buttons'][$key]['is_last']);
	}

	// Add the "Theme" link to the Profile menu:
	$areas['profile']['sub_buttons']['theme'] = array(
		'title' => $txt['theme'],
		'href' => $scripturl . '?action=profile;area=theme',
		'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
	);
	
	// If "myposts" element isn't defined, define it, then add the "Show Topics" link underneath it:
	if (empty($areas['profiles']['sub_buttons']['myposts']))
	{
		$areas['profile']['sub_buttons'] += array(
			'myposts' => array(
				'title' => $txt['showPosts'],
				'href' => $scripturl . '?action=profile;area=showposts',
				'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
			),
			'mytopics' => array(
				'title' => $txt['showTopics'],
				'href' => $scripturl . '?action=profile;area=showposts;sa=topics',
				'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
			),
		);
	}
	else
	{
		$new = array();
		foreach ($areas['profile']['sub_buttons'] as $id => $area)
		{
			$new[$id] = $area;
			if ($id == 'myposts')
				$new['mytopics'] = array(
					'title' => $txt['showTopics'],
					'href' => $scripturl . '?action=profile;area=showposts;sa=topics',
					'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
				);
		}
		$areas['profile']['sub_buttons'] = $new;
	}
	
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
	if (file_exists($sourcedir . '/Buddies.php'))
	{
		loadLanguage('UltimateProfile');
		$areas['profile']['sub_buttons']['ultimate'] = array(
			'title' => $txt['profile_customized'],
			'href' => $scripturl . '?action=profile;area=customized',
			'show' => allowedTo(array('edit_ultimate_profile_own', 'edit_ultimate_profile_any')),
		);
	}
	if (file_exists($sourcedir . '/Drafts.php'))
	{
		loadLanguage('Drafts');
		$areas['profile']['sub_buttons']['ultimate'] = array(
			'title' => $txt['permissiongroup_drafts'],
			'href' => $scripturl . '?action=profile;area=show_drafts',
			'show' => allowedTo(array('profile_view_own', 'profile_view_any')),
		);
	}
	$areas['profile']['sub_buttons'] += array(
		'notification' => array(
			'title' => $txt['notification'],
			'href' => $scripturl . '?action=profile;area=notification',
			'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
		),
		'ignore' => array(
			'title' => $txt['editBuddyIgnoreLists'],
			'href' => $scripturl . '?action=profile;area=lists;sa=ignore',
			'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
			'is_last' => true,
		),
	);

	// Restore the language strings:
	$txt = $old_txt;
	LoadLanguage('', '', false, false, $loaded);
}

?>