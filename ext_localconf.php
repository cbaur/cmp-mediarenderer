<?php

declare(strict_types=1);

use Cbaur\CmpMediarenderer\Resource\Rendering\VimeoRenderer;
use Cbaur\CmpMediarenderer\Resource\Rendering\YouTubeRenderer;
use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

$rendererRegistry = GeneralUtility::makeInstance(RendererRegistry::class);
$rendererRegistry->registerRendererClass(YouTubeRenderer::class);
$rendererRegistry->registerRendererClass(VimeoRenderer::class);
