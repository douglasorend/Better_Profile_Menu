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
	$areas['profile'] = array(
		'title' => $txt['profile'],
		'href' => $scripturl . '?action=profile',
		'show' => $context['allow_edit_profile'],
		'sub_buttons' => array(
			'summary' => array(
				'title' => $txt['summary'],
				'href' => $scripturl . '?action=profile',
				'show' => true,
			),
			'account' => array(
				'title' => $txt['account'],
				'href' => $scripturl . '?action=profile;area=account',
				'show' => allowedTo(array('profile_identity_any', 'profile_identity_own', 'manage_membergroups')),
			),
			'profile' => array(
				'title' => $txt['forumprofile'],
				'href' => $scripturl . '?action=profile;area=forumprofile',
				'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
			),
		),
	);
	if (file_exists($sourcedir . '/Buddies.php'))
	{
		loadLanguage('UltimateProfile');
		$areas['profile']['sub_buttons']['ultimate'] = array(
			'title' => $txt['profile_customized'],
			'href' => $scripturl . '?action=profile;area=customized',
			'show' => true,
		);
	}
	$areas['profile']['sub_buttons'] += array(		
		'posts' => array(
			'title' => $txt['showPosts'],
			'href' => $scripturl . '?action=profile;area=showposts',
			'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
		),
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
		'theme' => array(
			'title' => $txt['theme'],
			'href' => $scripturl . '?action=profile;area=theme',
			'show' => allowedTo(array('profile_extra_any', 'profile_extra_own')),
		),
	);
	
	// Restore the language strings:
	$txt = $old_txt;
}

?>