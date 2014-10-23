<?php
define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(__FILE__). DS);

// include composer autoload
require 'vendor/autoload.php';
use Intervention\Image\ImageManagerStatic as Image;

function getParam($param, $split_uri = array()) {
    $pos = array_search($param, $split_uri);
    if ($pos && isset($split_uri[$pos + 1])) {
        return $split_uri[$pos + 1];
    }
    return null;
}

function getPlaceholder($attr)
{
    $placeholder =  'public/placeholder/'.$attr.'.jpg';
    return $placeholder;
}

$request_uri = $_SERVER['REQUEST_URI'];
$split_uri = explode('/',$request_uri);

try {

    // Generate uri:http://DOMAINNAME/public/attachment/201410/13/16/543b916341d19_200_200.jpg
    $new_file = BP . $request_uri;
    $origName = pathinfo($new_file, PATHINFO_FILENAME);
    $ext = pathinfo($new_file, PATHINFO_EXTENSION);
    
    if (file_exists($new_file)) {
        echo  Image::make($new_file)->response($ext);
    } else {
        //$split_uri[6] source image
        if(!isset($split_uri[6])) {
            throw new Exception('Access Denied!');
        }


        //Fetch source image name and size
        list($sourceName, $width, $height) = explode('_', $origName);

        unset($split_uri[6]);

        $_file = BP . implode("\\", $split_uri) . DS . $sourceName .'.'. $ext;
        $img = Image::make($_file);

        if ($width) {
            $img->fit($width,  $height, function ($constraint) {
                $constraint->upsize();
            });
        }

        $new_file =  BP . $request_uri;

        if(!is_dir(dirname($new_file))) {
            @mkdir(dirname( $new_file), 0777, true);
        }
        
        if (!file_exists($new_file) || !@imagecreatefromjpeg($new_file)) {
            $img->save($new_file);
        }
        echo Image::make($new_file)->response($ext);
    }
    exit();
} catch (Exception $e) {
    // echo $e->getMessage();die();
    $url = 'http://media.local.ve.cn/' . getPlaceholder('ve');
    header("Location: " . $url, TRUE, 302);
    exit();
}
exit;
