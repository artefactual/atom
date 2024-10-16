<?php

$app = 'qubit';

include dirname(__FILE__).'/../../../bootstrap/functional.php';

class Browser extends sfBrowser
{
    public function files($files)
    {
        foreach ($files as $key => $path) {
            $error = UPLOAD_ERR_NO_FILE;
            $size = 0;
            if (is_readable($path)) {
                $error = UPLOAD_ERR_OK;
                $size = filesize($path);
            }

            $this->files[$key] = ['error' => $error,
                'name' => basename($path),
                'size' => $size,
                'tmp_name' => $path,
                'type' => '',
            ];
        }

        return $this;
    }
}

$browser = new QubitTestFunctional(new Browser());
$browser->disableSecurity();

$browser
    ->files(['file' => dirname(__FILE__).'/../../../fixtures/ead.xml'])
    ->post(';object/import')
    ->with('request')->begin()
    ->isParameter('module', 'object')
    ->isParameter('action', 'import')
    ->end()
    ->click('View Information object')
    ->with('request')->begin()
    ->isParameter('module', 'sfIsadPlugin')
    ->isParameter('action', 'index')
    ->end();

$object = QubitObject::getById($browser->getRequest()->id);

$browser->test()->ok(
    isset($object->parent),
    'Never create an information object without a parent'
);

$creators = $object->getCreators();

$browser->test()->ok(
    isset($creators[0]->parent),
    'Never create an actor without a parent'
);

$object->delete();
$object->events[0]->delete();
$creators[0]->delete();
