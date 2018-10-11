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
	$old_txt = $txt;
	loadLanguage('Profile');
	
	// Redefine the Profiles menu:
	$areas['profile']['sub_buttons']['theme'] = array(
		'title' => $txt['theme'],
		'href' => $scripturl . '?action=profile;area=theme',
		'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
	);
	if (empty($areas['profiles']['sub_buttons']['myposts']))
	{
		$areas['profile']['sub_buttons']['posts'] = array(
			'title' => $txt['showPosts'],
			'href' => $scripturl . '?action=profile;area=showposts',
			'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
		);
	}
	if (file_exists($sourcedir . '/Bookmarks.php'))
	{
		// [Bookmarks] button
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
}

?>