<?php

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';

require_once dirname(__FILE__).'/../../lib/sfThumbnail.class.php';

require_once dirname(__FILE__).'/../../lib/sfGDAdapter.class.php';

require_once dirname(__FILE__).'/../../lib/sfImageMagickAdapter.class.php';

// These tests require you have both [http://php.net/gd GD]
// and [http://www.imagemagick.org ImageMagick] installed
$adapters = ['sfGDAdapter', 'sfImageMagickAdapter'];

$tests_generic = 31;
$tests_imagemagick = 3;
$tests_gd = 3;

$data = [
    'invalid' => dirname(__FILE__).'/../data/invalid.txt',
    'blob' => dirname(__FILE__).'/../data/image.blob',
    'image/jpeg' => dirname(__FILE__).'/../data/einstein.jpg',
    // 'image/pjpeg'  => dirname(__FILE__).'/../data/pjpeg.jpg',
    'image/png' => dirname(__FILE__).'/../data/gnome.png',
    'image/gif' => dirname(__FILE__).'/../data/symfony.gif',
    'document/pdf' => dirname(__FILE__).'/../data/mogpres.pdf',
];

$result = getResultPath();
$mimeMap = unserialize(file_get_contents(dirname(__FILE__).'/../data/mime_types.dat'));

function getResultPath()
{
    return $result = dirname(__FILE__).'/../data/result';
}

class my_lime_test extends lime_test
{
    public function diag($message, $adapter = '')
    {
        if ($adapter) {
            $adapter = " [{$adapter}]";
        }
        $this->output->diag($message.$adapter);
    }
}

$t = new my_lime_test($tests_generic, new lime_output_color());
$t->diag('*** Generic Tests ***');

$t->diag('initialization', $adapter);

foreach ($adapters as $adapter) {
    $t->todo('loadFile() throws an exception when an invalid file is loaded');
    // todo: remove annoying error that results when uncommenting below code
    //  try
    //  {
    //    $thmb = new sfThumbnail(150, 150, true, true, 75, $adapter, array());
    //    $thmb->loadFile($invalid);
    //    $t->fail('loadFile() throws an exception when an invalid file is loaded');
    //  }
    //  catch (Exception $e)
    //  {
    //    $t->pass('loadFile() throws an exception when an invalid file is loaded');
    //  }

    // default options
    $t->diag('creates standard thumbnail', $adapter);
    $thmb = new sfThumbnail(150, 150, true, true, 75, $adapter, []);
    $thmb->loadFile($data['image/jpeg']);
    $thmb->save($result.'.jpg');
    checkResult($t, 150, 113, 'image/jpeg');

    $t->diag('saves a file to a different mime type');
    $thmb = new sfThumbnail(150, 150, true, true, 75, $adapter, []);
    $thmb->loadFile($data['image/jpeg']);
    $thmb->save($result.'.png', 'image/png');
    checkResult($t, 150, 113, 'image/png');

    $t->diag('creates absolutely sized thumbnail');
    $thmb = new sfThumbnail(150, 150, false, true, 75, $adapter, []);
    $thmb->loadFile($data['image/jpeg']);
    $thmb->save($result.'.jpg');
    checkResult($t, 150, 150, 'image/jpeg');

    $t->todo('handles image/pjpeg mime type');
    // $t->diag('handles image/pjpeg mime type');
    // $thmb = new sfThumbnail(150, 150, false, true, 75, $adapter, array());
    // $thmb->loadFile($data['image/pjpeg']);
    // $thmb->save($result.'.jpg');
    // checkResult($t, 150, 150, 'image/jpeg');

    $t->diag('creates inflated thumbnail');
    $thmb = new sfThumbnail(200, 200, false, true, 75, $adapter, []);
    $thmb->loadFile($data['image/gif']);
    $thmb->save($result.'.gif');
    checkResult($t, 200, 200, 'image/gif');

    // imagemagick-specific tests
    if ('sfImageMagickAdapter' == $adapter) {
        $t = new my_lime_test($tests_imagemagick, new lime_output_color());

        $t->diag('creates thumbnail from pdf', $adapter);
        $thmb = new sfThumbnail(150, 150, true, true, 75, $adapter, ['extract' => 1]);
        $thmb->loadFile($data['document/pdf']);
        $thmb->save($result.'.jpg');
        checkResult($t, 150, 116, 'image/jpeg');
    }

    // gd specific tests (imagemagick does not currently support loadData())
    if ('sfGDAdapter' == $adapter) {
        $t->diag('creates image from string');
        $thmb = new sfThumbnail(200, 200, false, true, 75, $adapter, []);
        $blob = file_get_contents($data['blob']);
        $thmb->loadData($blob, 'image/jpeg');
        $thmb->save($result.'.jpg', 'image/jpeg');
        checkResult($t, 200, 200, 'image/jpeg');
    }
}

function checkResult($t, $width, $height, $mime)
{
    global $mimeMap;
    $result = getResultPath();

    $result .= '.'.$mimeMap[$mime];

    // check generated thumbnail for expected results
    // use getimagesize() when possible, otherwise use 'identify'
    $imgData = @getimagesize($result);
    if ($imgData) {
        $res_width = $imgData[0];
        $res_height = $imgData[1];
        $res_mime = $imgData['mime'];
    } else {
        exec('identify '.$result, $output);
        list($img, $type, $dimen) = explode(' ', $output[0]);
        list($res_width, $res_height) = explode('x', $dimen);
    }

    $t->is($res_width, $width, 'thumbnail has correct width');
    $t->is($res_height, $height, 'thumbnail has correct height');
    $t->is($res_mime, $mime, 'thumbnail has correct mime type');

    unlink($result);
}
