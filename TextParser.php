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
use \OpenFlame\Framework\Dependency\Injector;

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
	const STACK_INJECTOR_PREFIX = 'tungsten.stack.';

	/**
	 * @var array - Array of slug parser objects to use in the tungsten stack.
	 */
	protected $stack = array();

	/**
	 * @var array - Array of parser names that are currently loaded in the tungsten.
	 */
	protected $stacks_loaded = array();

	public function __construct()
	{
		$injector = Injector::getInstance();

		$injector->setInjector(self::STACK_INJECTOR_PREFIX . 'Image', function() {
			return new \Codebite\Tungsten\Stack\Image();
		});

		$injector->setInjector(self::STACK_INJECTOR_PREFIX . 'Link', function() {
			return new \Codebite\Tungsten\Stack\Link();
		});

		$injector->setInjector(self::STACK_INJECTOR_PREFIX . 'LinkedImage', function() {
			return new \Codebite\Tungsten\Stack\LinkedImage();
		});

		$injector->setInjector(self::STACK_INJECTOR_PREFIX . 'Youtube', function() {
			return new \Codebite\Tungsten\Stack\Youtube();
		});

		$injector->setInjector(self::STACK_INJECTOR_PREFIX . 'UniqueSelector', function() {
			return new \Codebite\Tungsten\Stack\UniqueSelector();
		});

		$injector->setInjector(self::STACK_INJECTOR_PREFIX . 'TokenRemover', function() {
			return new \Codebite\Tungsten\Stack\TokenRemover();
		});
	}

	/**
	 * Load a new slug parser object into the tungsten stack if it isn't loaded already.
	 * @param string $stack_name - The name of the slug parser object to load onto the tungsten stack.
	 * @return \Codebite\Tungsten\TextParser - Provides a fluent interface.
	 */
	public function loadStack($stack_name)
	{
		$injector = Injector::getInstance();
		if(!isset($this->stack[$stack_name]))
		{
			$this->addStack($injector->get(self::STACK_INJECTOR_PREFIX . $stack_name));
		}

		return $this;
	}

	/**
	 * Get a specific parser that's already been loaded.
	 * @param string $stack_name - The name of the stack parser to grab.
	 * @return NULL|\Codebite\Tungsten\Stack\StackInterface - The stack parser we wanted, or NULL if not yet loaded.
	 */
	public function getStack($stack_name)
	{
		if(!isset($this->stack[$stack_name]))
		{
			return NULL;
		}

		return $this->stack[$stack_name];
	}

	/**
	 * @ignore
	 */
	protected function addStack(\Codebite\Tungsten\Stack\StackInterface $stack)
	{
		$this->stack[$stack::STACK_NAME] = $stack;
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
			if(!$stack->getEnabled())
			{
				continue;
			}

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
	 * Parse text for display to the user in an editable format by replacing previously inserted slugs with what was probably the text previously entered.
	 * @param string &$text - The text to run by the slug parser objects loaded.
	 * @param string &$bitfield - A randomly generated alphanumeric bitfield string, used to distinguish the slugs in the text.  The bitfield must be stored and provided upon page display to ensure proper slug extraction.
	 * @return integer - The number of slugs replaced in the text.
	 * @note This escapes all HTML output for the text itself; no htmlspecialchars on the text is necessary after running it through the display parser.
	 */
	public function parseForEdit(&$text, &$bitfield)
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
			if(!$stack->getEnabled())
			{
				continue;
			}

			$search = $replace = array();
			$count += $stack->parseForEdit($text, $bitfield, $search, $replace);
			$text = preg_replace($search, $replace, $text, 1);
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
			if(!$stack->getEnabled())
			{
				continue;
			}

			$search = $replace = array();
			$count += $stack->parseForDisplay($text, $bitfield, $search, $replace);
			$text = preg_replace($search, $replace, $text, 1);
		}

		return $count;
	}
}
