<?php

declare(strict_types=1);

namespace Cbaur\CmpMediarenderer\Services;

/***
 *
 * This file is part of the "CMP Media Renderer" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 *
 ***/

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsAllowedCallable;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class JavaScriptService
{
    public function __construct(
        private readonly AssetCollector $assetCollector,
        private readonly TypoScriptService $typoScriptService,
    ) {}

    /**
     * Also used as TypoScript USER userFunc, hence the ($content, $conf, $request) signature.
     */
    #[AsAllowedCallable]
    public function injectJs(string $content = '', array $conf = [], ?ServerRequestInterface $request = null): string
    {
        $request ??= $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return '';
        }

        $services = $this->getSettings($request)['services'] ?? [];

        foreach (array_keys($services) as $service) {
            $l10nParams = [
                $services[$service]['category'] ?? 'Marketing',
                $service,
            ];
            $serviceLcc = GeneralUtility::underscoredToLowerCamelCase(
                (string)preg_replace('/\s+/', '_', trim((string)$service))
            );
            $services[$service]['message'] = LocalizationUtility::translate(
                'cookiebot.blockingMessage.' . $serviceLcc,
                'CmpMediarenderer',
                $l10nParams,
                null,
                $request
            ) ?? LocalizationUtility::translate('cookiebot.blockingMessage', 'CmpMediarenderer', $l10nParams, null, $request);
            $services[$service]['category'] ??= 'Marketing';
            $services[$service]['lcc'] = $serviceLcc;
        }

        $servicesJson = json_encode(
            $services,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        $this->assetCollector->addInlineJavaScript('cookiebot-generate-placeholder', "
        ((d,i)=>{
            const c=" . $servicesJson . ";
            const ce=e=>d.createElement(e);
            d.querySelectorAll(i).forEach(e=>{
                const div=ce('div'),
                    s=e.dataset.cookieblockSrc;
                let m='',
                    cat='marketing', sp=undefined;
                for(const se in c){
                    const regex = new RegExp(c[se]['regex']);
                    if(regex.test(s)){
                        sp=c[se]['lcc'];
                        m=c[se]['message'];
                        cat=c[se]['category'].toLowerCase();
                        break;
                    }
                }
                if(!sp)return;
                div.innerHTML=`<div class=\"cookiebot-placeholder--inner cookiebot-placeholder--\${sp}\">`
                 +`<a href=\"javascript:Cookiebot.renew()\" class=\"cookiebot-placeholder--link\">\${m}</a>`
                 +'</div>';
                 div.style = e.dataset.previewImage ? `--cookiebot-placeholder-preview: url(\${e.dataset.previewImage})` : '';
                 if(e.width) div.style.setProperty('--cookiebot-placeholder-width', e.width + 'px');
                 if(e.height) div.style.setProperty('--cookiebot-placeholder-height', e.height + 'px');
                 div.classList.add('cookiebot-placeholder');
                 div.classList.add(`cookieconsent-optout-\${cat}`);
                 e.parentNode.insertBefore(div, e);
            });
        })(document, 'iframe[data-cookieblock-src]')");

        return '';
    }

    /**
     * Reads plugin.tx_cmpmediarenderer.settings from the frontend TypoScript of the current request.
     */
    protected function getSettings(ServerRequestInterface $request): array
    {
        $typoScript = $request->getAttribute('frontend.typoscript');
        if (!$typoScript instanceof FrontendTypoScript || !$typoScript->hasSetup()) {
            return [];
        }
        try {
            $settings = $typoScript->getSetupArray()['plugin.']['tx_cmpmediarenderer.']['settings.'] ?? [];
        } catch (\RuntimeException) {
            return [];
        }
        return is_array($settings) ? $this->typoScriptService->convertTypoScriptArrayToPlainArray($settings) : [];
    }
}
