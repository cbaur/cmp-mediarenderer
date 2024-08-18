<?php
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

 use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
 use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\AssetCollector;

class JavaScriptService
{

    protected $settings = [];



    public static function injectJs()
    {

        $services = self::$settings['services'] ?? [];
        /*
        [
            'Google Maps' => [
                'regex' => '/google\.com\/maps\/embed/',
                'category' => 'Marketing'
            ],
            'Vimeo' => '/player\.vimeo\.com\/video\//',
            'YouTube' => '/youtube(-nocookie)?\.com\/embed\//',
        ];
        */

        $textMessages = [];

        foreach(array_keys($services) as $service) {
            $l10nParams = [
                $services[$service]['category'] ?? 'Marketing',
                $service
            ];
            $serviceLcc = GeneralUtility::underscoredToLowerCamelCase(
                preg_replace('/\s+/', '_', trim($service))
            );
            $services[$service]['message'] = LocalizationUtility::translate(
                'cookiebot.blockingMessage.'
                 . $serviceLcc , 'CmpMediarenderer', $l10nParams)
            ?? LocalizationUtility::translate('cookiebot.blockingMessage', 'CmpMediarenderer', $l10nParams);
            $services[$service]['lcc'] = $serviceLcc;
        }

        GeneralUtility::makeInstance(AssetCollector::class)->addInlineJavaScript('cookiebot-generate-placeholder', "
        ((d,i)=>{
            c=JSON.parse('".json_encode($services)."');
            ce=e=>d.createElement(e);
            d.querySelectorAll(i).forEach(e=>{
                const a=ce('a'),
                    div=ce('div'),
                    p=ce('p'),
                    s=e.dataset.cookieblockSrc;
                let m='',
                    cat='marketing', sp=undefined;
                for(const se in c){
                    if(c[se]['regex'].test(s)){
                        sp=c[se]['lcc'];
                        m=c[se]['message'];
                        cat=c[se]['category'].toLowerCase();
                        break;
                    }
                }    
                if(!sp)return;
                div.innerHTML=`<div class=\"cookiebot-placeholder cookiebot-placeholder--\${sp}\" style=\"--cookiebot-placeholder-preview: url(\${e.dataset.previewImage})\">`
                 +`<a href=\"javascript:Cookiebot.renew()\" class=\"cookiebot-placeholder--inner\">\${m}`
                 +'</div>';
                 div.classList.add(`cookieconsent-optout-\${cat}`);
                 e.parentNode.insertBefore(div, e);
            });
        })(document, 'iframe[data-cookieblock-src]')");
    }
}