<?php
/**
 *
 *===================================================================
 *
 *  Tungsten
 *-------------------------------------------------------------------
 * @category    tungsten
 * @package     tungsten
 * @author      Damian Bushong
 * @copyright   (c) 2011 Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/tungsten
 *
 *===================================================================
 *
 */

namespace Codebite\Tungsten\Stack;

/**
 * Tungsten - Slug parser object,
 * 		Slugifies text for storage in a database and renders out the HTML for display using slugified text.
 *
 *
 * @category    tungsten
 * @package     tungsten
 * @author      Damian Bushong
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/damianb/tungsten
 */
class Youtube extends StackBase implements StackInterface
{
	const STACK_NAME = 'Youtube';

	/**
	 * @var array - Array of options for this stack.
	 */
	protected $options = array(
		'width'		=> 640,
		'height'	=> 390,

		'hd_width'	=> 853,
		'hd_height'	=> 510,
	);

	/**
	 * Parse the provided text for storage in the database by slugifying recognizable, transformable data.
	 * @param string &$text - The text to slugify.
	 * @param string &$bitfield - The random bitfield string to use for slugification.
	 * @param array &$search - The array of text chunks to search for in the text (for slugification)
	 * @param array &$replace - The array of slugs to replace the chunks of text (specified in &$search) with.
	 * @return integer - The number of sluggable text chunks found in the provided text.
	 */
	public function parseForStorage($text, &$bitfield, array &$search, array &$replace)
	{
		// parse out youtube magic video URLs here
		// (i love making sam's eyes bleed)
		$regexp = '#(?:http://(?:www\.youtube\.com|youtu\.be)(?:/watch\?v=|/)([\w\-]+)(?:[\?\&](?:(?!hd)[\w\-]+)=[\w_\-]+)*(?:[\&\?](hd=1))?(?:[\?\&](?:(?!hd)[\w\-]+)=[\w\-]+)*)#';
		$count = preg_match_all($regexp, $text, $matches);
		if($count > 0)
		{
			for($i = 0, $size = sizeof($matches[0]); $i < $size; $i++)
			{
				$search[] = '#' . preg_quote($matches[0][$i], '#') . '#';
				$replace[] = sprintf('~{tungsten::%1$s::youtube%3$s::%2$s}~', $bitfield, $matches[1][$i], ((!empty($matches[2][$i]) === true) ? 'hd' : ''));
			}
		}

		return $count;
	}

	/**
	 * Replace previously generated slugs with what was probably the plain text before parsing for editing by the end user.
	 * @param string &$text - The text to prepare for display.
	 * @param string &$bitfield - The random bitfield string to use for deslugification.
	 * @param array &$search - The array of slugs to search for in the text (for deslugification)
	 * @param array &$replace - The array of HTML chunks to replace the slugs (specified in &$search) with.
	 * @return integer - The number of slugs found in the provided text.
	 */
	public function parseForEdit($text, &$bitfield, array &$search, array &$replace)
	{
		$regexp = '#(?:~\{tungsten::([\w]+)::(youtube|youtubehd)::([\w\-]+)\}~)#S';
		$count = preg_match_all($regexp, $text, $matches);
		if($count > 0)
		{
			for($i = 0, $size = sizeof($matches[0]); $i < $size; $i++)
			{
				if($matches[1][$i] != $bitfield)
				{
					continue;
				}
				$is_hd = ($matches[2][$i] === 'youtubehd') ? true : false;
				$search[] = '#' . preg_quote($matches[0][$i], '#') . '#';
				$format = 'http://www.youtube.com/watch?v=%1$s%2$s';
				if($is_hd)
				{
					$hd = '&amp;hd=1';
				}
				else
				{
					$hd = '';
				}
				$replace[] = sprintf($format, $matches[3][$i], $hd);
			}
		}

		return sizeof($search);
	}

	/**
	 * Replace previously generated slugs with their HTML counterparts for display to the end user.
	 * @param string &$text - The text to prepare for display.
	 * @param string &$bitfield - The random bitfield string to use for deslugification.
	 * @param array &$search - The array of slugs to search for in the text (for deslugification)
	 * @param array &$replace - The array of HTML chunks to replace the slugs (specified in &$search) with.
	 * @return integer - The number of slugs found in the provided text.
	 */
	public function parseForDisplay($text, &$bitfield, array &$search, array &$replace)
	{
		$regexp = '#(?:~\{tungsten::([\w]+)::(youtube|youtubehd)::([\w\-]+)\}~)#S';
		$count = preg_match_all($regexp, $text, $matches);
		if($count > 0)
		{
			for($i = 0, $size = sizeof($matches[0]); $i < $size; $i++)
			{
				if($matches[1][$i] != $bitfield)
				{
					continue;
				}
				$is_hd = ($matches[2][$i] === 'youtubehd') ? true : false;
				$search[] = '#' . preg_quote($matches[0][$i], '#') . '#';
				$format = '<object width="%2$s" height="%3$s" class="tungsten_youtube" data="http://www.youtube.com/v/%1$s?fs=1&amp;hl=en_US&amp;rel=0%4$s" type="application/x-shockwave-flash"><param name="movie" value="http://www.youtube.com/v/%1$s?fs=1&amp;hl=en_US&amp;rel=0%4$s" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="transparent" /><embed src="http://www.youtube.com/v/%1$s?fs=1&amp;hl=en_US&amp;rel=0%4$s" type="application/x-shockwave-flash" width="%2$s" height="%3$s" allowscriptaccess="always" allowfullscreen="true" wmode="transparent" /></object><br />';
				if($is_hd)
				{
					$width = $this->getOption('hd_width');
					$height = $this->getOption('hd_height');
					$hd = '&amp;hd=1';
				}
				else
				{
					$width = $this->getOption('width');
					$height = $this->getOption('height');
					$hd = '';
				}
				$replace[] = sprintf($format, $matches[3][$i], $width, $height, $hd);
			}
		}

		return sizeof($search);
	}
}
