# tungsten

tungsten is a lightweight stack slug text parser written in PHP 5.3 OOP, making it safe (and easy) for users to just dump in text and have it "just work".

tungsten can take you from user input to storage (do remember to escape the data before insert though!), and then from storage to display (it'll handle html escaping on its own already), and all without missing a beat.

## how it works

provided with it are several stacks for the parser:

* Image
* Link
* LinkedImage (for use with javascript thumbnail stuff)
* UniqueSelector (great for "spoiler" content)
* TokenRemover (hides erroneous slug data).

the parser is intended to be run on pure plain text with extremely minimal formatting.

image embeds require just an exclamation mark (!) before the image URL to embed them.

youtube URLs are automatically detected and embedded (even the short youtu.be URLs), and the currently generated HD video URLs are embedded with a larger display area.

any remaining URLs are magically converted to links.

## copyright

(c) 2011 Damian Bushong

## license

This library is licensed under the MIT license; you can find a full copy of the license itself in the file /LICENSE

## requirements

* PHP 5.3.0 or newer
* OpenFlame Framework 1.2.1 or newer

## usage

this will come later; haven't had the chance to write up docs yet
