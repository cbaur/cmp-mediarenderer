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

class VimeoRenderer extends \TYPO3\CMS\Core\Resource\Rendering\VimeoRenderer
{
    use CookiebotAwareVideoRenderer;
}
