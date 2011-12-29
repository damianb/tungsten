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
class Image implements \Codebite\Tungsten\Stack\StackInterface
{
	const STACK_NAME = 'Image';

	/**
	 * @var array - Array of options for this stack.
	 */
	protected $options = array(
		'prefix'	=> '!',
	);

	/**
	 * @var array - Array of HTML attributes to set on the elements.
	 */
	protected $attributes = array(
		'img'		=> array(),
	);

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->setAttribute('img', 'alt', 'user-supplied image')
			->setAttribute('img', 'class', 'tungsten_img');
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
		// parse out magic image embed URLs here
		// (i love making sam's eyes bleed)
		$regexp = '#' . preg_quote($this->getOption('prefix'), '#') . '(((https?)://(?:(?:[a-zA-Z0-9]+\.?){2,}))(((?:/?[\w\-\+\~_ ]+)*)/(?:([\w\-\+\~_ ]+\.[\w]{2,})(\?[\w\-\+\~\&\=_ ]+)?(\#[\w\-\=\+_]+)?)))#';
		$count = preg_match_all($regexp, $text, $matches);
		if($count > 0)
		{
			for($i = 0, $size = sizeof($matches[0]); $i < $size; $i++)
			{
				$search[] = '#' . preg_quote($matches[0][$i], '#') . '#';
				$replace[] = sprintf('~{tungsten::%1$s::image::%2$s}~', $bitfield, base64_encode($matches[1][$i]));
			}
		}

		return $count;
	}

	public function parseForEdit($text, &$bitfield, array &$search, array &$replace)
	{
		$regexp = '#~\{tungsten::([\w]+)::image::((?:[A-Za-z0-9\+\/]{4})*(?:[A-Za-z0-9\+\/]{2}\=\=|[A-Za-z0-9\+\/]{3}\=)?)\}~#S';
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
				$format = $this->getOption('prefix') . '%1$s';
				$image = htmlspecialchars(base64_decode($matches[2][$i]), ENT_QUOTES, 'UTF-8');
				$replace[] = sprintf($format, $image);
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
		$regexp = '#~\{tungsten::([\w]+)::image::((?:[A-Za-z0-9\+\/]{4})*(?:[A-Za-z0-9\+\/]{2}\=\=|[A-Za-z0-9\+\/]{3}\=)?)\}~#S';
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
				$format = '<img src="%1$s" %2$s />';
				$image = htmlspecialchars(base64_decode($matches[2][$i]), ENT_QUOTES, 'UTF-8');
				$replace[] = sprintf($format, $image, $this->dumpAttributes('img'));
			}
		}

		return sizeof($search);
	}
}
