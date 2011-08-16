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
class UniqueSelector implements \Codebite\Tungsten\Stack\StackInterface
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
		return 'UniqueSelector';
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
		// spoiler parsing, woo!
		$regexp = '#((?:~~\@)([^(\@~~)]+)(?:\@~~))#s';
		$count = preg_match_all($regexp, $text, $matches);
		if($count > 0)
		{
			for($i = 0, $size = sizeof($matches[0]); $i < $size; $i++)
			{
				$search[] = '#' . preg_quote($matches[0][$i], '#') . '#';
				$rand = mt_rand();
				$identifier = substr(hash('md5', $bitfield . $rand), 0, 5) . '-' . $rand;
				$replace[] = sprintf('~{tungsten::%1$s::uniquestart::%3$s}~%2$s~{tungsten::%1$s::uniqueend::%3$s}~', $bitfield, $matches[2][$i], $identifier);
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
		$regexp = '#~\{tungsten::([\w]+)::uniquestart::([a-f0-9]{5}\-[0-9]+)\}~(.*?)~\{tungsten::([\w]+)::uniqueend::([a-f0-9]{5}\-[0-9]+)\}~#Ss';
		$count = preg_match_all($regexp, $text, $matches);
		if($count > 0)
		{
			for($i = 0, $size = sizeof($matches[0]); $i < $size; $i++)
			{
				// ensure that BOTH bitfields are valid
				if($matches[1][$i] != $bitfield || $matches[4][$i] != $bitfield)
				{
					continue;
				}
				// ensure that both unique id's match
				if($matches[2][$i] != $matches[5][$i])
				{
					continue;
				}

				$search[] = '#' . preg_quote($matches[0][$i], '#') . '#';
				$format = '<div id="%1$s" class="tungsten-uniqueid">%2$s</div><br />';
				$id = 'tungsten-id_' . $matches[2][$i];
				$replace[] = sprintf($format, $id, $matches[3][$i]);
			}
		}

		return sizeof($search);
	}
}
