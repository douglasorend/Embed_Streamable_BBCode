<?php
/**********************************************************************************
* Subs-BBCode-streamable.php
***********************************************************************************
* This mod is licensed under the 2-clause BSD License, which can be found here:
*	http://opensource.org/licenses/BSD-2-Clause
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/
if (!defined('SMF')) 
	die('Hacking attempt...');

function BBCode_Streamable(&$bbc)
{
	// Format: [streamable width=x height=x frameborder=x]{streamable ID}[/streamable]
	$bbc[] = array(
		'tag' => 'streamable',
		'type' => 'unparsed_content',
		'parameters' => array(
			'width' => array('match' => '(\d+)'),
			'height' => array('optional' => true, 'match' => '(\d+)'),
			'frameborder' => array('optional' => true, 'match' => '(\d+)'),
		),
		'validate' => 'BBCode_Streamable_Validate',
		'content' => '{width}|{height}|{frameborder}',
		'disabled_content' => '$1',
	);

	// Format: [streamable width=x height=x frameborder=x]{streamable ID}[/streamable]
	$bbc[] = array(
		'tag' => 'streamable',
		'type' => 'unparsed_content',
		'parameters' => array(
			'frameborder' => array('match' => '(\d+)'),
		),
		'validate' => 'BBCode_Streamable_Validate',
		'content' => '0|0|{frameborder}',
		'disabled_content' => '$1',
	);

	// Format: [streamable]{streamable ID}[/streamable]
	$bbc[] = array(
		'tag' => 'streamable',
		'type' => 'unparsed_content',
		'validate' => 'BBCode_Streamable_Validate',
		'content' => '0|0|0',
		'disabled_content' => '$1',
	);
}

function BBCode_Streamable_Button(&$buttons)
{
	$buttons[count($buttons) - 1][] = array(
		'image' => 'streamable',
		'code' => 'streamable',
		'description' => 'streamable',
		'before' => '[streamable]',
		'after' => '[/streamable]',
	);
}

function BBCode_Streamable_Validate(&$tag, &$data, &$disabled)
{
	global $txt, $modSettings;
	
	if (empty($data))
		return ($tag['content'] = $txt['streamable_no_post_id']);
	$data = strtr(trim($data), array('<br />' => ''));
	if (strpos($data, 'http://') !== 0 && strpos($data, 'https://') !== 0)
		$data = 'http://' . $data;
	$pattern = '#(http|https)://(|(.+?).)streamable.com/(?:e/)?([\w\d]+)#i';
	if (!preg_match($pattern, $data, $parts))
		return ($tag['content'] = $txt['streamable_no_post_id']);
	$data = $parts[4];

	list($width, $height, $frameborder) = explode('|', $tag['content']);
	if (empty($width) && !empty($modSettings['streamable_default_width']))
		$width = $modSettings['streamable_default_width'];
	if (empty($height) && !empty($modSettings['streamable_default_height']))
		$height = $modSettings['streamable_default_height'];
	$tag['content'] = '<div style="' . (empty($width) ? '' : 'max-width: ' . $width . 'px;') . (empty($height) ? '' : 'max-height: ' . $height . 'px;') . '"><div class="streamable-wrapper">' .
		'<iframe src="https://streamable.com/e/' . $data .'" scrolling="no" frameborder="' . $frameborder . '"></iframe></div></div>';
}

function BBCode_Streamable_LoadTheme()
{
	global $context, $settings;
	$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/BBCode-Streamable.css" />';
	$context['allowed_html_tags'][] = '<iframe>';
}

function BBCode_Streamable_Settings(&$config_vars)
{
	$config_vars[] = array('int', 'streamable_default_width');
	$config_vars[] = array('int', 'streamable_default_height');
}

function BBCode_Streamable_Embed(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	$replace = (strpos($cache_id, 'sig') !== false ? '[url]$0[/url]' : '[streamable]$0[/streamable]');
	$pattern = '~(?<=[\s>\.(;\'"]|^)(?:https?\:\/\/)?(?:www\.)?streamable.com\/(?:e\/)?([\w\d]+)+\??[/\w\-_\~%@\?;=#}\\\\]?~';
	$message = preg_replace($pattern, $replace, $message);
	if (strpos($cache_id, 'sig') !== false)
		$message = preg_replace('#\[streamable.*\](.*)\[\/streamable\]#i', '[url]$1[/url]', $message);
}

?>