<?php
require 'zb_system/function/c_system_base.php';

function _GetHttpContent($url)
{
    $r = null;
    if (function_exists("curl_init") && function_exists('curl_exec')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (ini_get("safe_mode") == false && ini_get("open_basedir") == false) {
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }
        if (extension_loaded('zlib')) {
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        $r = curl_exec($ch);
        curl_close($ch);
    } elseif (ini_get("allow_url_fopen")) {
        if (function_exists('ini_set')) {
            ini_set('default_socket_timeout', 300);
        }
        $r = file_get_contents((extension_loaded('zlib') ? 'compress.zlib://' : '') . $url);
    }

    return $r;
}

$arrPlugins = array(
    "AppCentre" => "https://app.zblogcn.com/?zba=231",
    "tencentcloud_common" => "https://app.zblogcn.com/?zba=17846",
    "tencentcloud_captcha" => "https://app.zblogcn.com/?zba=17847",
    "tencentcloud_cdn" => "https://app.zblogcn.com/?zba=17850",
    "tencentcloud_cos" => "https://app.zblogcn.com/?zba=17851",
    "tencentcloud_tms" => "https://app.zblogcn.com/?zba=17852",
);

$zbp->Load();

// $ZC_USING_PLUGIN_LIST = $zbp->option['ZC_USING_PLUGIN_LIST'];

foreach ($arrPlugins as $name => $url) {
    $zba = _GetHttpContent($url);
    if (!$zba) {
        throw new Exception('Downloaded zba failed.');
    }
    echo "Installing {$name} <br>\n";
    App::UnPack($zba);
    // $ZC_USING_PLUGIN_LIST = AddNameInString($ZC_USING_PLUGIN_LIST, $name);
    // if ("AppCentre" !== $name && is_readable($file_base = $GLOBALS['usersdir'] . 'plugin/' . $name . '/include.php')) {
    //     include $file_base;
    // }
    // InstallPlugin($name);
}

// $zbp->option['ZC_USING_PLUGIN_LIST'] = $ZC_USING_PLUGIN_LIST;

// $zbp->SaveOption();
