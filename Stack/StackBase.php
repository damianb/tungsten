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
 * Tungsten - Slug parser object base,
 * 		Provides common methods to the stack parsers for ease of use.
 *
 *
 * @category    tungsten
 * @package     tungsten
 * @author      Damian Bushong
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/damianb/tungsten
 */
abstract class StackBase
{
	/**
	 * @var array - Array of options for this stack.
	 */
	protected $options = array();

	/**
	 * @var array - Array of HTML attributes to set on the elements.
	 */
	protected $attributes = array();

	/**
	 * Set an option.
	 * @param string $option - The option to set.
	 * @param string $value - The value to set.
	 * @return self - Provides a fluent interface.
	 */
	public function setOption($option, $value)
	{
		$this->options[(string) $option] = $value;

		return $this;
	}

	/**
	 * Get an option's current value
	 * @param string $option - The option to look up.
	 * @return NULL|mixed - NULL if no such option, or the value if found.
	 */
	public function getOption($option)
	{
		if(!isset($this->options[(string) $option]))
		{
			return NULL;
		}

		return $this->options[(string) $option];
	}

	/**
	 * Set an HTML attribute on the specified element.
	 * @param string $attribute - The attribute to set.
	 * @param string $value - The value to set.
	 * @return self - Provides a fluent interface.
	 */
	public function setAttribute($element, $attribute, $value)
	{
		$this->attributes[(string) $element][(string) $attribute] = $value;

		return $this;
	}

	/**
	 * Get an element's attribute value
	 * @param string $element - The element to look up.
	 * @param string $attribute - The attribute to look up.
	 * @return NULL|string - NULL if no such attribute/element, or the value if found.
	 */
	public function getAttribute($element, $attribute)
	{
		if(!isset($this->attributes[(string) $element][(string) $attribute]))
		{
			return NULL;
		}

		return $this->attributes[(string) $element][(string) $attribute];
	}

	/**
	 * Get all attributes for an element and format them for direct insertion into HTML.
	 * @param string - The element to dump.
	 * @return string - The attribute string.
	 */
	public function dumpAttributes($element)
	{
		if(!isset($this->attributes[(string) $element]))
		{
			return '';
		}

		$html = array();
		foreach($this->attributes[(string) $element] as $attribute => $value)
		{
			$html[] = $attribute . '="' . $value . '"';
		}
		return join(' ', $html);
	}
}
