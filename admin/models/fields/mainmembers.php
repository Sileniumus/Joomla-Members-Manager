<?php
/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th September, 2015
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Mainmembers Form Field class for the Membersmanager component
 */
class JFormFieldMainmembers extends JFormFieldList
{
	/**
	 * The mainmembers field type.
	 *
	 * @var		string
	 */
	public $type = 'mainmembers';

	/**
	 * Override to add new button
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		// see if we should add buttons
		$setButton = $this->getAttribute('button');
		// get html
		$html = parent::getInput();
		// if true set button
		if ($setButton === 'true')
		{
			$button = array();
			$script = array();
			$buttonName = $this->getAttribute('name');
			// get the input from url
			$app = JFactory::getApplication();
			$jinput = $app->input;
			// get the view name & id
			$values = $jinput->getArray(array(
				'id' => 'int',
				'view' => 'word'
			));
			// check if new item
			$ref = '';
			$refJ = '';
			if (!is_null($values['id']) && strlen($values['view']))
			{
				// only load referral if not new item.
				$ref = '&amp;ref=' . $values['view'] . '&amp;refid=' . $values['id'];
				$refJ = '&ref=' . $values['view'] . '&refid=' . $values['id'];
				// get the return value.
				$_uri = (string) JUri::getInstance();
				$_return = urlencode(base64_encode($_uri));
				// load return value.
				$ref .= '&amp;return=' . $_return;
				$refJ .= '&return=' . $_return;
			}
			$user = JFactory::getUser();
			// only add if user allowed to create member
			if ($user->authorise('member.create', 'com_membersmanager') && $app->isAdmin()) // TODO for now only in admin area.
			{
				// build Create button
				$buttonNamee = trim($buttonName);
				$buttonNamee = preg_replace('/_+/', ' ', $buttonNamee);
				$buttonNamee = preg_replace('/\s+/', ' ', $buttonNamee);
				$buttonNamee = preg_replace("/[^A-Za-z ]/", '', $buttonNamee);
				$buttonNamee = ucfirst(strtolower($buttonNamee));
				$button[] = '<a id="'.$buttonName.'Create" class="btn btn-small btn-success hasTooltip" title="'.JText::sprintf('COM_MEMBERSMANAGER_CREATE_NEW_S', $buttonNamee).'" style="border-radius: 0px 4px 4px 0px; padding: 4px 4px 4px 7px;"
					href="index.php?option=com_membersmanager&amp;view=member&amp;layout=edit'.$ref.'" >
					<span class="icon-new icon-white"></span></a>';
			}
			// only add if user allowed to edit member
			if (($buttonName === 'member' || $buttonName === 'members') && $user->authorise('member.edit', 'com_membersmanager') && $app->isAdmin()) // TODO for now only in admin area.
			{
				// build edit button
				$buttonNamee = trim($buttonName);
				$buttonNamee = preg_replace('/_+/', ' ', $buttonNamee);
				$buttonNamee = preg_replace('/\s+/', ' ', $buttonNamee);
				$buttonNamee = preg_replace("/[^A-Za-z ]/", '', $buttonNamee);
				$buttonNamee = ucfirst(strtolower($buttonNamee));
				$button[] = '<a id="'.$buttonName.'Edit" class="btn btn-small hasTooltip" title="'.JText::sprintf('COM_MEMBERSMANAGER_EDIT_S', $buttonNamee).'" style="display: none; padding: 4px 4px 4px 7px;" href="#" >
					<span class="icon-edit"></span></a>';
				// build script
				$script[] = "
					jQuery(document).ready(function() {
						jQuery('#adminForm').on('change', '#jform_".$buttonName."',function (e) {
							e.preventDefault();
							var ".$buttonName."Value = jQuery('#jform_".$buttonName."').val();
							".$buttonName."Button(".$buttonName."Value);
						});
						var ".$buttonName."Value = jQuery('#jform_".$buttonName."').val();
						".$buttonName."Button(".$buttonName."Value);
					});
					function ".$buttonName."Button(value) {
						if (value > 0) {
							// hide the create button
							jQuery('#".$buttonName."Create').hide();
							// show edit button
							jQuery('#".$buttonName."Edit').show();
							var url = 'index.php?option=com_membersmanager&view=members&task=member.edit&id='+value+'".$refJ."';
							jQuery('#".$buttonName."Edit').attr('href', url);
						} else {
							// show the create button
							jQuery('#".$buttonName."Create').show();
							// hide edit button
							jQuery('#".$buttonName."Edit').hide();
						}
					}";
			}
			// check if button was created for member field.
			if (is_array($button) && count($button) > 0)
			{
				// Load the needed script.
				$document = JFactory::getDocument();
				$document->addScriptDeclaration(implode(' ',$script));
				// return the button attached to input field.
				return '<div class="input-append">' .$html . implode('',$button).'</div>';
			}
		}
		return $html;
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
	{
				// load the db opbject
		$db = JFactory::getDBO();
		// get the input from url
		$jinput = JFactory::getApplication()->input;
		// get the id
		$id = $jinput->getInt('id', 0);
		if ($id > 0)
		{
			$main_member = MembersmanagerHelper::getVar('member', $id, 'id', 'main_member');
		}
		// get the user
		$my = JFactory::getUser();
		// start the query
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.id','a.user','a.account','a.name','a.email','a.token'),array('id','main_member_user','account','name','email','token')));
		$query->from($db->quoteName('#__membersmanager_member', 'a'));
		$query->where($db->quoteName('a.published') . ' >= 1');
		$query->where($db->quoteName('a.account') . ' = 1 OR ' . $db->quoteName('a.account') . ' = 2');
		// check if current user is a supper admin
		if (!$my->authorise('core.admin'))
		{
			// get user access groups
			$user_access_types =  MembersmanagerHelper::getAccess($my);
			// user must have access
			if (isset($user_access_types) && MembersmanagerHelper::checkArray($user_access_types))
			{
				// only get members of the type this user has access to
				$query->where('a.type IN (' . implode(',', $user_access_types) . ')');
				// get current member type
				if (($type=  MembersmanagerHelper::getVar('member', $id, 'id', 'type')) !== false)
				{
					// check if this member is in the user access types
					if (in_array($type, $user_access_types))
					{
						// no need to load this member
						$main_member = 0;
					}
				}
			}
			elseif (isset($main_member) && $main_member > 0)
			{
				// load this main member only
				$query->where($db->quoteName('a.id') . ' = ' . (int) $main_member);
			}
			else
			{
				return false;
			}
		}
		$query->order('a.user ASC');
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = array();
		if ($items)
		{
			// only add if more then one value found
			if (count( (array) $items) > 1)
			{
				$options[] = JHtml::_('select.option', '', 'Select a main member');
			}
			foreach($items as $item)
			{
				// check if we current member
				if (isset($main_member) && $main_member == $item->id)
				{
					// remove ID
					$main_member = 0;
				}
				if ($item->account == 1)
				{
					$options[] = JHtml::_('select.option', $item->id, JFactory::getUser((int) $item->main_member_user)->name . ' ' . JFactory::getUser((int) $item->main_member_user)->email . ' ( ' . $item->token . ' )');
				}
				else
				{
					$options[] = JHtml::_('select.option', $item->id, $item->name . ' ' . $item->email . ' ( ' . $item->token . ' )');
				}
			}
		}
		// add the current user (TODO this is not suppose to happen)
		if (isset($main_member) && $main_member > 0)
		{
			// load the current member manual
			$options[] = JHtml::_('select.option', (int) $main_member, MembersmanagerHelper::getMemberName($main_member));
		}
		return $options;
	}
}
