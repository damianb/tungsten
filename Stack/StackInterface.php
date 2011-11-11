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
 * Tungsten - Slug parser object interface,
 * 		Provides the method prototypes that must be defined in all slug parser objects.
 *
 *
 * @category    tungsten
 * @package     tungsten
 * @author      Damian Bushong
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/damianb/tungsten
 */
interface StackInterface
{
	public function parseForStorage($text, &$bitfield, array &$search, array &$replace);
	public function parseForPlaintext($text, &$bitfield, array &$search, array &$replace);
	public function parseForDisplay($text, &$bitfield, array &$search, array &$replace);
}
