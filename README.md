This is a draft MediaWiki extension that allows you to use
[OOJS-UI](https://www.mediawiki.org/wiki/OOjs_UI) widgets in wiki pages.

It's intended to be used to generate a living style guide for MediaWiki.


## Installation

Run composer to get dependencies and get update git submodules

```
composer install
git submodule update --init --recursive
```

Add the following line to `LocalSettings.php` and navigate to Special:Version to confirm installation.

```php
require_once( "$IP/extensions/OOUIPlayground/OOUIPlayground.php" )
```


## Example

Add the following code to a page to see an example

```html
<ooui-demo type="button" label="Disabled button" disabled="true" />
```