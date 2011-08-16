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
class SimpleYoutube implements \Codebite\Tungsten\Stack\StackInterface
{
	/**
	 * Get a new instance of this slug parser object.
	 * @return \Codebite\Tungsten\Stack\Youtube - The newly created instance.
	 */
	public static function newInstance()
	{
		return new self();
	}

	/**
	 * Get the name for this slug parser object.
	 * @return string - The name of this slug parser object.
	 */
	public function getStackName()
	{
		return 'SimpleYoutube';
	}

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
		// Difference from youtube: This does NOT do HD embeds.  Just embeds all videos as normal videos.
		$regexp = '#(?:http://(?:www\.youtube\.com|youtu\.be)(?:/watch\?v=|/)([\w\-]+)(?:[\?\&](?:[\w\-]+)=[\w_\-]+)*)#';
		$count = preg_match_all($regexp, $text, $matches);
		if($count > 0)
		{
			for($i = 0, $size = sizeof($matches[0]); $i < $size; $i++)
			{
				$search[] = '#' . preg_quote($matches[0][$i], '#') . '#';
				$replace[] = sprintf('~{tungsten::%1$s::youtube::%2$s}~', $bitfield, $matches[1][$i]);
			}
		}

		return $count;
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
		$regexp = '#(?:~\{tungsten::([\w]+)::youtube::([\w\-]+)\}~)#S';
		$count = preg_match_all($regexp, $text, $matches);
		if($count > 0)
		{
			for($i = 0, $size = sizeof($matches[0]); $i < $size; $i++)
			{
				if($matches[1][$i] != $bitfield)
				{
					continue;
				}
				$search[] = '#' . preg_quote($matches[0][$i], '#') . '#';
				$format = '<object width="%2$s" height="%3$s" class="tungsten_youtube"><param name="movie" value="http://www.youtube.com/v/%1$s?fs=1&amp;hl=en_US&amp;rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/%1$s?fs=1&amp;hl=en_US&amp;rel=0" type="application/x-shockwave-flash" width="%2$s" height="%3$s" allowscriptaccess="always" allowfullscreen="true" wmode="transparent"></embed></object><br />';
				$width = 480;
				$height = 303;
				$replace[] = sprintf($format, $matches[2][$i], $width, $height);
			}
		}

		return sizeof($search);
	}
}
