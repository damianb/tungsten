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

namespace Codebite\Tungsten;

/**
 * Tungsten - Text parser object,
 * 		Handles the loading of parser stacks.
 *
 *
 * @category    tungsten
 * @package     tungsten
 * @author      Damian Bushong
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/damianb/tungsten
 */
class TextParser
{
	/**
	 * @var array - Array of slug parser objects to use in the tungsten stack.
	 */
	protected $stack = array();

	/**
	 * @var array - Array of parser names that are currently loaded in the tungsten.
	 */
	protected $stacks_loaded = array();

	/**
	 * Load a new slug parser object into the tungsten stack if it isn't loaded already.
	 * @param string $stack_name - The name of the slug parser object to load onto the tungsten stack.
	 * @return \Codebite\Tungsten\TextParser - Provides a fluent interface.
	 */
	public function loadStack($stack_name)
	{
		if(!isset($this->stacks_loaded[$stack_name]))
		{
			$stack_class = "\\Codebite\\Tungsten\\Stack\\$stack_name";
			$this->addStack($stack_class::newInstance());
		}

		return $this;
	}

	/**
	 * @ignore
	 */
	protected function addStack(\Codebite\Tungsten\Stack\StackInterface $stack)
	{
		$this->stack[] = $stack;
		$this->stacks_loaded[$stack->getStackName()] = true;
	}

	/**
	 * Parse text for storage in the database by slugifying parsable data.
	 * @param string &$text - The text to run by the slug parser objects loaded.
	 * @param string &$bitfield - A randomly generated alphanumeric bitfield string, used to distinguish the slugs in the text.  The bitfield must be stored and provided upon page display to ensure proper slug extraction.
	 * @return integer - The number of slugs inserted into the text.
	 * @note THIS DOES NOT SANITIZE THE TEXT.
	 */
	public function parseForStorage(&$text, &$bitfield)
	{
		$count = 0; // count will store how many tokens have been inserted into the parsed text

		// Run through the parsing stack and
		foreach($this->stack as $stack)
		{
			$search = $replace = array();
			$count += $stack->parseForStorage($text, $bitfield, $search, $replace);
			$text = preg_replace($search, $replace, $text, 1);
		}

		// If no tokens were inserted, we'll clear out the bitfield for a later shortcut when parsing for display.
		if($count === 0)
		{
			$bitfield = '';
		}

		return $count;
	}

	/**
	 * Parse text for display to the user by replacing previously inserted slugs with HTML.
	 * @param string &$text - The text to run by the slug parser objects loaded.
	 * @param string &$bitfield - A randomly generated alphanumeric bitfield string, used to distinguish the slugs in the text.  The bitfield must be stored and provided upon page display to ensure proper slug extraction.
	 * @return integer - The number of slugs replaced in the text.
	 * @note This escapes all HTML output for the text itself; no htmlspecialchars on the text is necessary after running it through the display parser.
	 */
	public function parseForDisplay(&$text, &$bitfield)
	{
		$count = 0;

		// Escape output so it can be displayed safely (while the tokens are still unparsed, so they aren't accidentally modified).
		$text = nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'), true);

		// If the bitfield is an empty string, there were no tokens inserted into the text when preparing it for storage.
		// We'll bail out to save some processing here.
		if(empty($bitfield))
		{
			return $count;
		}

		// Run through the parsing stack and
		foreach($this->stack as $stack)
		{
			$search = $replace = array();
			$count += $stack->parseForDisplay($text, $bitfield, $search, $replace);
			$text = preg_replace($search, $replace, $text, 1);
		}

		return $count;
	}
}
