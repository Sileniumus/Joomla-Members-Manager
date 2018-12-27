<?php
/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th July, 2018
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('JPATH_BASE') or die('Restricted access');


// set the image path
if (MembersmanagerHelper::checkString($displayData->profile_image) && $displayData->_USER->authorise('member.view.profile_image', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$displayData->profile_image_link = MembersmanagerHelper::getImageLink($displayData, 'profile_image', 'name', $displayData->_IMAGELINK, false);
}
else
{
	$displayData->profile_image_link = false;
}
// build Meta
$meta = array();
// check if the type is to be set
if (MembersmanagerHelper::checkString($displayData->type_name) && $displayData->_USER->authorise('member.view.type', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$meta[] = $displayData->type_name;
}
// set add profile link switch
$addProfileLink = false;
// if this is a sub account load main account details
if ((3 == $displayData->account || 4 == $displayData->account) && $displayData->main_member > 0)
{
	// get main member account type
	$displayData->main_account = MembersmanagerHelper::getVar('member', $displayData->main_member, 'id', 'account');
	if (1 == $displayData->main_account && isset($displayData->main_user_name))
	{
		$displayData->main_member_email = $displayData->main_user_email;
	}
	// now make sure we have these set
	if (isset($displayData->main_member_name) && MembersmanagerHelper::checkString($displayData->main_member_name))
	{
		$meta[] = JText::_('COM_MEMBERSMANAGER_MAIN_MEMBER') . ': <a href="' . $displayData->main_member_profile_link . '" alt="' . $displayData->main_member_name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_MAIN_MEMBER_PROFILE') . '">' . $displayData->main_member_name . '</a>';
	}
	// set add profile link switch
	$addProfileLink = true;
}
// check if the edit button is to be added
$editButton = MembersmanagerHelper::getEditButton($displayData, 'member', 'members', '&ref=profile&refid=' . $displayData->_REFID, 'com_membersmanager', null);
// set the header
$header = array();
$header[] = '<header class="uk-comment-header uk-grid-medium uk-flex-middle" uk-grid>';
if ($displayData->profile_image_link)
{
	// add link to profile image if loaded
	if ($addProfileLink)
	{
		$header[] = '<a href="' . $displayData->profile_link . '" alt="' . $displayData->name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_PROFILE') . '">';
	}
	$header[] = '<div class="uk-width-auto"><img class="uk-comment-avatar uk-box-shadow-large" src="' . $displayData->profile_image_link . '" alt="' . $displayData->name . '"></div>';
	// close link if added
	if ($addProfileLink)
	{
		$header[] = '</a>';
	}
}
$header[] = '<div class="uk-width-expand">';
$header[] = '<h4 class="uk-comment-title uk-margin-remove">';
// add link to member name if set
if ($addProfileLink)
{
	$header[] = '<a href="' . $displayData->profile_link . '" alt="' . $displayData->name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_PROFILE') . '">';
}
// add member name
$header[] = $displayData->name;
// close link if added
if ($addProfileLink)
{
	$header[] = '</a>&nbsp;&nbsp;';
}
$header[] = $editButton . '</h4>';
$header[] = '<ul class="uk-comment-meta uk-subnav uk-subnav-divider uk-margin-remove-top">';
$header[] = '<li>' . implode('</li><li>', $meta) . '</li>';
$header[] = '</ul></div>';
$header[] = '</header>';

?>
<?php echo implode(PHP_EOL, $header); ?>
