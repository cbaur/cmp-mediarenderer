<?php
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
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

trait CookiebotAwareVideoRenderer
{
    /**
     * @return int
     */
    public function getPriority()
    {
        return 25;
    }

    /**
     * @param FileInterface $file
     * @param int|string $width
     * @param int|string $height
     * @param array $options
     * @param bool $usedPathsRelativeToCurrentScript
     * @return string
     */
    public function render(FileInterface $file, $width, $height, array $options = [], $usedPathsRelativeToCurrentScript = false)
    {
        GeneralUtility::makeInstance(JavaScriptService::class)->injectJs();
     
        $options = $this->collectOptions($options, $file);

        $previewImage = $this->getPreviewImage($file, $width, $height) ?? null;

        $iframe = preg_replace(
            [
                '| src="|',
            ],
            [
                ' data-cookieconsent="marketing" data-cookieblock-text="'. LocalizationUtility::translate($this->getCookieblockTextTranslationKey(), 'CmpMediarenderer', $this->getCookieblockTextArguments()) .'" data-preview-image="' . $previewImage . '" data-cookieblock-src="',
            ],
            parent::render($file, $width, $height, $options, $usedPathsRelativeToCurrentScript)
        );
        

        

        return '' . $iframe . '';
    }




    protected function getPreviewImage($file, $width = 0, $height = 0)
    {
        $previewImageWebPath = null;
        $orgFile = $file instanceof FileReference ? $file->getOriginalFile() : $file;

        $previewImage = $this->onlineMediaHelper->getPreviewImage($orgFile);

        /* Make sure the preview image is always publicly available */
        $publicPreviewImage = Environment::getPublicPath() . '/typo3temp/assets/images/' . basename($previewImage);
        if (
            file_exists($previewImage) && (
                !file_exists($publicPreviewImage) ||
                filemtime($previewImage) > filemtime($publicPreviewImage)
            )
        ) {
            GeneralUtility::writeFileToTypo3tempDir($publicPreviewImage, @file_get_contents($previewImage));
        }

        if (file_exists($publicPreviewImage)) {
            $conf = [
                'file' => $publicPreviewImage,
                'file.' => [
                    //'maxW' => ($placeholderContentSetup['previewMaxWidth'] ?? ''),
                    //'maxH' => ($placeholderContentSetup['previewMaxHeight'] ?? ''),
                ],
            ];

            if ((int)$width > 0) {
                $conf['file.']['width'] = (int)$width . 'c';
            }

            if ((int)$height > 0) {
                $conf['file.']['height'] = (int)$height . 'c';
            }

             /** @var ContentObjectRenderer $contentObjectRenderer */
            $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class, $GLOBALS['TSFE']);
            $previewImageWebPath = '/' . ltrim($contentObjectRenderer->cObjGetSingle('IMG_RESOURCE', $conf), '/');

            
        }
        return $previewImageWebPath;
    }

    protected function getCookieblockTextTranslationKey(): string
    {
        return 'cookiebot.blockingMessage';
    }

    protected function getCookieblockTextArguments(): array
    {
        return [];
    }
}
