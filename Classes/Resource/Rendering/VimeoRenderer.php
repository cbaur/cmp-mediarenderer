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

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class VimeoRenderer extends \TYPO3\CMS\Core\Resource\Rendering\VimeoRenderer
{
    use CookiebotAwareVideoRenderer;
}