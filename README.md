# CMP Mediarenderer - TYPO3 extension for Cookiebot powered GDPR video blocking
TYPO3 extension overriding the default YouTube &amp; Vimeo output with markup supporting Cookiebot CMP.

## Who needs this extension?

This TYPO3 extension is for you if

- you added Cookiebot CMP to yourt TYPO3 website
- you want to use the default text media element to include YouTube/Vimeo videos on your site
- and/or such videos in extension generated content using the `<f:media>` view helper

The extension can also be a (partial) replacement for the amazing `media2click` extension.

## What does it do?

The extension overrides the default TYPO3 renderers for YouTube and Vimeo videos and adds markup to block this content until the corresponding cookies are accepted in Cookiebot CMP.

## Requirements

| Extension version | TYPO3        | PHP       |
|-------------------|--------------|-----------|
| 1.1.x (`main`)    | 13.4, 14.3   | 8.2 – 8.5 |
| 1.0.x             | 10.4 – 13.4  | 8.1+      |

Version 1.1.0 adds TYPO3 14.3 support and keeps working in TYPO3 13.4 with the same release. Projects on TYPO3 10.4 – 12.4 stay on 1.0.x.

Changes in 1.1.0:

- Renderers use the `render()` signature without `$usedPathsRelativeToCurrentScript` and typed return values
- The request is passed explicitly (no `$GLOBALS['TSFE']`), settings are read from the frontend TypoScript of the request
- `JavaScriptService` uses constructor injection and is marked `#[AsAllowedCallable]` for the TypoScript `USER` call
- The placeholder text now shows the cookie category ("Marketing") instead of the raw `%1$s` placeholder
- Preview image and blocking text are HTML-escaped in the iframe attributes

## Installation

Just install as usual with composer:

```bash
# TYPO3 13.4 / 14.3
composer require cbaur/cmp-mediarenderer:^1.1

# TYPO3 10.4 – 12.4
composer require cbaur/cmp-mediarenderer:~1.0.0
```

The package is not on Packagist, add the repository to your `composer.json` first:

```json
{
  "type": "vcs",
  "url": "git@github.com:cbaur/cmp-mediarenderer.git"
}
```

The extension will block YouTube/Vimeo content out of the box, but the content blocker poster may look unstyled at this point.

### TypoScript

Include the extension's TypoScript in your site package. It registers the services (see below), the default
stylesheet and the JavaScript that renders the content blocker poster:

```
# constants
@import 'EXT:cmp_mediarenderer/Configuration/TypoScript/constants.typoscript'

# setup
@import 'EXT:cmp_mediarenderer/Configuration/TypoScript/setup.typoscript'
```

Available constants:

| Constant                                                | Default | Description                                                         |
|---------------------------------------------------------|---------|---------------------------------------------------------------------|
| `plugin.tx_cmpmediarenderer.settings.loadDefaultStyles` | `1`     | Include the basic stylesheet `Resources/Public/Css/mediarenderer.css` |
| `plugin.tx_cmpmediarenderer.settings.loadJsGlobally`    | `1`     | Add the poster JavaScript on every page (needed for custom iframes) |

Using the default stylesheet is optional, you can also provide your own stylesheet and/or adapt the CSS classes to
your needs (see configuration section).

## Configuration

### Text & translations

You can adapt the messages being output on the content blocking poster by overriding its translations.
`%1$s` is replaced with the cookie category (e.g. "Marketing"):

| Key                                   | Default (en)                                                      |
|---------------------------------------|-------------------------------------------------------------------|
| `cookiebot.blockingMessage`           | Please accept %1$s cookies in order to view this content          |
| `cookiebot.blockingMessage.youtube`   | Please accept %1$s cookies in order to view content from YouTube  |

For the poster text, a service specific key is built from the service name (`cookiebot.blockingMessage.<service>`,
the name is lowercased, underscores/spaces become camel case), e.g. `GoogleMaps` → `cookiebot.blockingMessage.googlemaps`.
If it does not exist, `cookiebot.blockingMessage` is used.

```
plugin.tx_cmpmediarenderer._LOCAL_LANG {
    de {
        cookiebot\.blockingMessage = Bitte akzeptieren Sie %1$s-Cookies, um diesen Inhalt zu sehen
    }
}
```

If you like it better, this can of course also be achieved with XLIFF overrides.

### Styling

At the moment, it is not possible to change the content blocker poster's HTML by fluid overrides due to the technical design of the TYPO3 media renderers. Nonetheless, we tried hard to provide the most flexible markup and CSS for all your customization needs.

- all parts of the poster use semantic classes
- the video's poster image url is provided as a CSS variable: `--cookiebot-placeholder-preview`
- the iframe's width and height are provided as `--cookiebot-placeholder-width` and `--cookiebot-placeholder-height`
- you can extend the classes being set on the elements with Tailwind or other framework classes
- many style properties of the default stylesheet can be overridden by resetting CSS variables

## Additional Services & custom iframes

You can use this extension to block any iframe until a specific consent category is accepted. To do so, insert the iframe into your website, e.g. using an HTML content element in the TYPO3 backend:

```html
<iframe
  src="https://..."
  ...
></iframe>
```

Afterwards, rename the `src` attribute of the iframe to `data-cookieblock-src`:

```html
<iframe
  data-cookieblock-src="https://..."
  ...
></iframe>
```

We refer to the iframe src as a "Service" that should be loaded. Each of these services has to be registered in the extension's TypoScript config:

```
plugin.tx_cmpmediarenderer {
    settings {
        services {
            GoogleMaps {
                regex = google.com/maps/embed
                category = Marketing
            }
            YouTube {
                regex = youtube(-nocookie)?.com/embed/
                category = Marketing
            }
            Vimeo {
                regex = player.vimeo.com/video/
                category = Marketing
            }
            # add any other service here
        }
    }
}
```


## Developers corner

While we do not yet provide a fluid template for the output, the extension can still be overridden or extended to customize it even further.

### The `CookiebotAwareVideoRenderer` trait

As wide parts of the code for the YouTube and Vimeo renderer are the same, we are using a PHP trait to inject this code into the respective classes. This means that if you for example have extended the TYPO3 video providers and added a renderer for content from X (Twitter), you could also use the extension's trait in your class and just override/implement the functions that need adjustments.

Methods of the trait you can override (signatures as of 1.1.0):

```php
protected function getPreviewImage(FileInterface $file, int|float|string $width = 0, int|float|string $height = 0, ?ServerRequestInterface $request = null): ?string;
protected function getCookieblockTextTranslationKey(): string;
protected function getCookieblockTextArguments(): array;
```

If you extended the trait in 1.0.x, adapt your overrides to these signatures when upgrading to 1.1.
