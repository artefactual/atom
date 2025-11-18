<?php

class arZoomPanPluginConfiguration extends sfPluginConfiguration
{
    const VERSION = '1.0.0';

    public function initialize()
    {
        // 1. Load custom routes
        $this->dispatcher->connect(
            'routing.load_configuration',
            array($this, 'addRoutes')
        );

        // 2. Load helper
        $this->dispatcher->connect(
            'template.filter_parameters',
            array($this, 'registerHelpers')
        );

        // 3. Inject JS/CSS
        $this->dispatcher->connect(
            'response.filter_content',
            array($this, 'injectAssets')
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
                array('module' => 'arZoomPan', 'action' => 'viewer'),
                array('id' => '\d+')
            )
        );

        // /zoompan/info/1552
        $routing->prependRoute(
            'zoompan_info',
            new sfRoute(
                '/zoompan/info/:id',
                array('module' => 'arZoomPan', 'action' => 'info'),
                array('id' => '\d+')
            )
        );

        // /zoompan/tile/1552/10/0/0.jpg
        $routing->prependRoute(
            'zoompan_tile',
            new sfRoute(
                '/zoompan/tile/:id/:z/:x/:y.:format',
                array('module' => 'arZoomPan', 'action' => 'tile'),
                array(
                    'id'     => '\d+',
                    'z'      => '\d+',
                    'x'      => '\d+',
                    'y'      => '\d+',
                    'format' => '(jpg|png)'
                )
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
        $js  = '<script src="/plugins/arZoomPanPlugin/js/zoom-pan.js"></script>';

        if (strpos($content, 'zoom-pan.js') !== false) {
            return $content;
        }

        return str_replace('</head>', $osd . "\n" . $css . "\n" . $js . "\n</head>", $content);
    }
}

?>
