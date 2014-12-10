This is a draft MediaWiki extension that allows you to use
[OOJS-UI](https://www.mediawiki.org/wiki/OOjs_UI) widgets in wiki pages.

It's intended to be used to generate a living style guide for MediaWiki.


## Installation

Run composer to get dependencies.

```
composer install
```

Add the following line to `LocalSettings.php` and navigate to Special:Version
to confirm installation.

```php
require_once( "$IP/extensions/OOUIPlayground/OOUIPlayground.php" )
```


## What this extension does

This extension adds two tags to MediaWiki:

### &lt;ooui-demo&gt;

This tag accepts the parameters for the OOUI Widget, either as attributes,
or as JSON contents. The two options may be mixed.

The special attribute 'type' specifies what type of widget it is.
This is generally the name of the widget class, minus the 'Widget' suffix.

Example:
```
<ooui-demo type="button">
{
	"disabled" : "true",
	"label" : "Button"
}
</ooui-demo>
```

The tag will display the widget, and on the right-hand-side will display the
code used to create such a widget.

### &lt;ooui-doc&gt;

This tag accepts the type of OOUI Widget (in the 'type' attribute), and
displays a table of parameters accepted for that widget.

Example:
```
<ooui-doc type="button" />
```