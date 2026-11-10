<?php

declare(strict_types=1);

namespace Cbaur\CmpMediarenderer\Resource\Rendering;

/***
 *
 * This file is part of the "CMP Media Renderer" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 ***/

use Cbaur\CmpMediarenderer\Services\JavaScriptService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

trait CookiebotAwareVideoRenderer
{
    public function getPriority(): int
    {
        return 25;
    }

    /**
     * @param int|string $width TYPO3 known format; examples: 220, 200m or 200c
     * @param int|string $height TYPO3 known format; examples: 220, 200m or 200c
     */
    public function render(FileInterface $file, $width, $height, array $options = []): string
    {
        $iframe = parent::render($file, $width, $height, $options);
        if ($iframe === '') {
            return '';
        }

        $request = $this->getRequest();
        GeneralUtility::makeInstance(JavaScriptService::class)->injectJs('', [], $request);

        $blockText = LocalizationUtility::translate(
            $this->getCookieblockTextTranslationKey(),
            'CmpMediarenderer',
            $this->getCookieblockTextArguments(),
            null,
            $request
        ) ?? '';
        $previewImage = $this->getPreviewImage($file, $width, $height, $request) ?? '';

        return (string)preg_replace(
            '| src="|',
            ' data-cookieconsent="marketing"'
            . ' data-cookieblock-text="' . htmlspecialchars($blockText, ENT_QUOTES | ENT_HTML5) . '"'
            . ' data-preview-image="' . htmlspecialchars($previewImage, ENT_QUOTES | ENT_HTML5) . '"'
            . ' data-cookieblock-src="',
            $iframe,
            1
        );
    }

    /**
     * @param int|float|string $width the media gallery processor may pass floats (e.g. 500.0)
     * @param int|float|string $height the media gallery processor may pass floats (e.g. 281.25)
     */
    protected function getPreviewImage(FileInterface $file, int|float|string $width = 0, int|float|string $height = 0, ?ServerRequestInterface $request = null): ?string
    {
        $orgFile = $file instanceof FileReference ? $file->getOriginalFile() : $file;
        $onlineMediaHelper = $this->getOnlineMediaHelper($file);
        if (!$orgFile instanceof File || $onlineMediaHelper === false || $onlineMediaHelper === null) {
            return null;
        }

        $previewImage = $onlineMediaHelper->getPreviewImage($orgFile);

        /* Make sure the preview image is always publicly available */
        $publicPreviewImage = Environment::getPublicPath() . '/typo3temp/assets/images/' . basename($previewImage);
        if (
            is_file($previewImage) && (
                !is_file($publicPreviewImage) ||
                filemtime($previewImage) > filemtime($publicPreviewImage)
            )
        ) {
            GeneralUtility::writeFileToTypo3tempDir($publicPreviewImage, (string)@file_get_contents($previewImage));
        }

        if (!is_file($publicPreviewImage)) {
            return null;
        }

        $conf = [
            'file' => $publicPreviewImage,
            'file.' => [],
        ];
        if ((int)$width > 0) {
            $conf['file.']['width'] = (int)$width . 'c';
        }
        if ((int)$height > 0) {
            $conf['file.']['height'] = (int)$height . 'c';
        }

        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        if ($request !== null) {
            $contentObjectRenderer->setRequest($request);
        }
        $imageUrl = $contentObjectRenderer->cObjGetSingle('IMG_RESOURCE', $conf);

        return $imageUrl !== '' ? '/' . ltrim($imageUrl, '/') : null;
    }

    protected function getRequest(): ?ServerRequestInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        return $request instanceof ServerRequestInterface ? $request : null;
    }

    protected function getCookieblockTextTranslationKey(): string
    {
        return 'cookiebot.blockingMessage';
    }

    protected function getCookieblockTextArguments(): array
    {
        // The label contains a "%1$s" placeholder for the cookie category
        return ['Marketing'];
    }
}
