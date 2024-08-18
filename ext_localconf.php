<?php
declare(strict_types=1);

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;

defined('TYPO3') or die();

call_user_func(function () {

    /** @var RendererRegistry $rendererRegistry */
    $rendererRegistry = GeneralUtility::makeInstance(RendererRegistry::class);
    $rendererRegistry->registerRendererClass(\Cbaur\CmpMediarenderer\Resource\Rendering\YouTubeRenderer::class);
    $rendererRegistry->registerRendererClass(\Cbaur\CmpMediarenderer\Resource\Rendering\VimeoRenderer::class);
});