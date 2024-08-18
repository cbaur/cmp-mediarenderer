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

## Installation

Just install as usual with composer:

```bash
composer require cbaur/cmp-mediarenderer
```

The extension will block YouTube/Vimeo content out of the box, but the content blocker poster may look unstyled at this point.

For a quickstart, we do provide a basic stylesheet that can be added by including the static typoscript in your typoscript template. This step is optional as you can also provide your own stylesheet and/or adapt the css classes to your needs (see configuration section). 

## Configuration

### Text & translations

You can adapt the messages being output on the content blocking poster by overriding its translations:

```

```

If you like it better, this can of course also be achieved with XLIFF overrides.

### Styling

At the moment, it is not possible to change the content blocker poster's HTML by fluid overrides due to the technical design of the TYPO3 media renderers. Nonetheless, we tried hard to provide the most flexible markup and CSS for all your customization needs.

- all parts of the poster use semantic classes
- the video's poster image url is provided as a CSS variable: `--cookieblock-poster-image`
- you can extend the classes being set on the elements with Tailwind or other framework classes
- many style properties of the default stylesheet can be overridden by resetting CSS variables


## Developers corner

While we do not yet provide a fluid template for the output, the extension can still be overridden or extended to customize it even further.

### The `CookiebotAwareVideoRenderer` trait

As wide parts of the code for the YouTube and Vimeo renderer are the same, we are using a PHP trait to inject this code into the respective classes. This means that if you for example have extended the TYPO3 video providers and added a renderer for content from X (Twitter), you could also use the extension's trait in your class and just override/implement the functions that need adjustments.