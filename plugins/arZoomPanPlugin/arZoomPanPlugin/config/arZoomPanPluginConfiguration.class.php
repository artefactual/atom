<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * arZoomPanPluginConfiguration.
 *
 * Modified by Johan Pieterse to use arMetadataExtractionPlugin
 *
 * @package    arZoomPanPlugin
 * @subpackage config
 */

class arZoomPanPluginConfiguration extends sfPluginConfiguration
{
    public const VERSION = '1.0.0';

    public function initialize()
    {
        // 1. Load custom routes
        $this->dispatcher->connect(
            'routing.load_configuration',
            [$this, 'addRoutes']
        );

        // 2. Load helper
        $this->dispatcher->connect(
            'template.filter_parameters',
            [$this, 'registerHelpers']
        );

        // 3. Inject JS/CSS
        $this->dispatcher->connect(
            'response.filter_content',
            [$this, 'injectAssets']
        );
    }

    public function addRoutes(sfEvent $event)
    {
        $routing = $event->getSubject();

        // /zoompan/viewer/1552
        $routing->prependRoute(
            'zoompan_viewer',
            new sfRoute(
                '/zoompan/viewer/:id',
                ['module' => 'arZoomPan', 'action' => 'viewer'],
                ['id' => '\d+']
            )
        );

        // /zoompan/info/1552
        $routing->prependRoute(
            'zoompan_info',
            new sfRoute(
                '/zoompan/info/:id',
                ['module' => 'arZoomPan', 'action' => 'info'],
                ['id' => '\d+']
            )
        );

        // /zoompan/tile/1552/10/0/0.jpg
        $routing->prependRoute(
            'zoompan_tile',
            new sfRoute(
                '/zoompan/tile/:id/:z/:x/:y.:format',
                ['module' => 'arZoomPan', 'action' => 'tile'],
                [
                    'id' => '\d+',
                    'z' => '\d+',
                    'x' => '\d+',
                    'y' => '\d+',
                    'format' => '(jpg|png)',
                ]
            )
        );
    }

    public function registerHelpers($event, $params)
    {
        $params['helpers'][] = 'ZoomPan';

        return $params;
    }

    public function injectAssets(sfEvent $event, $content)
    {
        $request = sfContext::getInstance()->getRequest();

        // Only inject on pages displaying digital objects
        if (!preg_match('#/(informationobject|digitalobject)#i', $request->getPathInfo())) {
            return $content;
        }

        // OpenSeadragon
        $osd = '<script src="https://cdn.jsdelivr.net/npm/openseadragon@3.1.0/build/openseadragon/openseadragon.min.js"></script>';

        // Plugin CSS
        $css = '<link rel="stylesheet" href="/plugins/arZoomPanPlugin/css/zoom-pan.css">';

        // Plugin JS
        $js = '<script src="/plugins/arZoomPanPlugin/js/zoom-pan.js"></script>';

        if (false !== strpos($content, 'zoom-pan.js')) {
            return $content;
        }

        return str_replace('</head>', $osd."\n".$css."\n".$js."\n</head>", $content);
    }
}
