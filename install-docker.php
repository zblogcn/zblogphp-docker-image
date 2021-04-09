<?php
date_default_timezone_set('UTC');

require 'zb_system/function/c_system_base.php';
require 'zb_system/function/c_system_admin.php';

// a helper function to lookup "env_FILE", "env", then fallback
function getenv_docker($env, $default)
{
    if ($fileEnv = getenv($env . '_FILE')) {
        return rtrim(file_get_contents($fileEnv), "\r\n");
    } else if (($val = getenv($env)) !== false) {
        return $val;
    } else {
        return $default;
    }
}

// ** MySQL settings - You can get this info from your web host ** //

define('DB_HOST', getenv_docker('ZC_DB_HOST', 'localhost'));

define('DB_NAME', getenv_docker('ZC_DB_NAME', 'zblog_docker'));

define('DB_USER', getenv_docker('ZC_DB_USER', 'root'));

define('DB_PWDD', getenv_docker('ZC_DB_PWDD', ''));

// 可选
define('DB_PREFIX', getenv_docker('ZC_DB_PREFIX', 'zbp_'));

define('DB_ENGINE', getenv_docker('ZC_DB_ENGINE', 'MyISAM'));

define('DB_TYPE', getenv_docker('ZC_DB_TYPE', 'mysqli'));

// ** Site info & User  ** //
define('BLOG_NAME', getenv_docker('ZC_BLOG_NAME', '又一个 Z-BLOG 站点'));

define('BLOG_USER', getenv_docker('ZC_BLOG_USER', 'zblog_user'));

define('BLOG_PWDD', getenv_docker('ZC_BLOG_PWDD', 'zblog_pwdd'));


// Main

$zbloglang = &$zbp->option['ZC_BLOG_LANGUAGEPACK'];
$zbp->LoadLanguage('system', '', $zbloglang);
$zbp->LoadLanguage('zb_install', 'zb_install', $zbloglang);

$cts = file_get_contents($GLOBALS['blogpath'] . 'zb_system/defend/createtable/mysql.sql');

$zbp->option['ZC_MYSQL_SERVER'] = DB_HOST;
if (strpos($zbp->option['ZC_MYSQL_SERVER'], ':') !== false) {
    $servers = explode(':', $zbp->option['ZC_MYSQL_SERVER']);
    $zbp->option['ZC_MYSQL_SERVER'] = trim($servers[0]);
    $zbp->option['ZC_MYSQL_PORT'] = (int) $servers[1];
    if ($zbp->option['ZC_MYSQL_PORT'] == 0) {
        $zbp->option['ZC_MYSQL_PORT'] = 3306;
    }
    unset($servers);
}
$zbp->option['ZC_MYSQL_NAME'] = trim(str_replace(array('\'', '"'), array('', ''), DB_NAME));
$zbp->option['ZC_MYSQL_USERNAME'] = DB_USER;
$zbp->option['ZC_MYSQL_PASSWORD'] = DB_PWDD;

$zbp->option['ZC_MYSQL_PRE'] = DB_PREFIX;
$zbp->option['ZC_MYSQL_ENGINE'] = DB_ENGINE;
$zbp->option['ZC_DATABASE_TYPE'] = DB_TYPE;

$cts = str_replace('MyISAM', $zbp->option['ZC_MYSQL_ENGINE'], $cts);

$zbp->db = ZBlogPHP::InitializeDB($zbp->option['ZC_DATABASE_TYPE']);
if ($zbp->db->CreateDB($zbp->option['ZC_MYSQL_SERVER'], $zbp->option['ZC_MYSQL_PORT'], $zbp->option['ZC_MYSQL_USERNAME'], $zbp->option['ZC_MYSQL_PASSWORD'], $zbp->option['ZC_MYSQL_NAME']) == true) {
    echo $zbp->lang['zb_install']['create_db'] . $zbp->option['ZC_MYSQL_NAME'] . "<br/>";
}
$zbp->db->dbpre = $zbp->option['ZC_MYSQL_PRE'];
$zbp->db->Close();

$zbp->OpenConnect();
$zbp->ConvertTableAndDatainfo();
CreateTable($cts);
InsertInfo();
SaveConfig();
$zbp->CloseConnect();

// End 

function CreateTable($sql)
{
    global $zbp;
    if ($zbp->db->ExistTable($GLOBALS['table']['Config']) == true) {
        echo $zbp->lang['zb_install']['exist_table_in_db'];
        return false;
    }
    $sql = $zbp->db->sql->ReplacePre($sql);
    $zbp->db->QueryMulit($sql);
    if ($zbp->db->ExistTable($GLOBALS['table']['Config']) == false) {
        echo $zbp->lang['zb_install']['not_create_table'];
        return false;
    }
    echo $zbp->lang['zb_install']['create_table'] . "<br/>";
    return true;
}

function InsertInfo()
{
    global $zbp;
    $zbp->guid = GetGuid();

    $guid = GetGuid();

    $mem = new Member();
    $mem->Guid = $guid;
    $mem->Level = 1;
    $mem->Name = BLOG_USER;
    $mem->Password = Member::GetPassWordByGuid(BLOG_PWDD, $guid);
    $mem->IP = GetGuestIP();
    $mem->PostTime = time();

    FilterMember($mem);
    $mem->Save();

    $cate = new Category();
    $cate->Name = $zbp->lang['msg']['uncategory'];
    $cate->Alias = 'uncategorized';
    $cate->Count = 1;
    $cate->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_navbar'];
    $t->FileName = "navbar";
    $t->Source = "system";
    $t->SidebarID = 0;
    $t->Content = '<li id="nvabar-item-index"><a href="{#ZC_BLOG_HOST#}">' . $zbp->lang['zb_install']['index'] . '</a></li><li id="navbar-page-2"><a href="{#ZC_BLOG_HOST#}?id=2">' . $zbp->lang['zb_install']['guestbook'] . '</a></li>';
    $t->HtmlID = "divNavBar";
    $t->Type = "ul";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['calendar'];
    $t->FileName = "calendar";
    $t->Source = "system";
    $t->SidebarID = 1;
    $t->Content = "";
    $t->HtmlID = "divCalendar";
    $t->Type = "div";
    $t->IsHideTitle = true;
    $t->Build();
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['control_panel'];
    $t->FileName = "controlpanel";
    $t->Source = "system";
    $t->SidebarID = 1;
    $t->Content = '<span class="cp-hello">' . $zbp->lang['zb_install']['wellcome'] . '</span><br/><span class="cp-login"><a href="{#ZC_BLOG_HOST#}zb_system/cmd.php?act=login">' . $zbp->lang['msg']['admin_login'] . '</a></span>&nbsp;&nbsp;<span class="cp-vrs"><a href="{#ZC_BLOG_HOST#}zb_system/cmd.php?act=misc&amp;type=vrs">' . $zbp->lang['msg']['view_rights'] . '</a></span>';
    $t->HtmlID = "divContorPanel";
    $t->Type = "div";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_catalog'];
    $t->FileName = "catalog";
    $t->Source = "system";
    $t->SidebarID = 1;
    $t->Content = "";
    $t->HtmlID = "divCatalog";
    $t->Type = "ul";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['search'];
    $t->FileName = "searchpanel";
    $t->Source = "system";
    $t->SidebarID = 1;
    $t->Content = '<form name="search" method="post" action="{#ZC_BLOG_HOST#}zb_system/cmd.php?act=search"><input type="text" name="q" size="11" /> <input type="submit" value="' . $zbp->lang['msg']['search'] . '" /></form>';
    $t->HtmlID = "divSearchPanel";
    $t->Type = "div";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_comments'];
    $t->FileName = "comments";
    $t->Source = "system";
    $t->SidebarID = 1;
    $t->Content = "";
    $t->HtmlID = "divComments";
    $t->Type = "ul";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_archives'];
    $t->FileName = "archives";
    $t->Source = "system";
    $t->SidebarID = 1;
    $t->Content = "";
    $t->HtmlID = "divArchives";
    $t->Type = "ul";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_statistics'];
    $t->FileName = "statistics";
    $t->Source = "system";
    $t->SidebarID = 0;
    $t->Content = "";
    $t->HtmlID = "divStatistics";
    $t->Type = "ul";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_favorite'];
    $t->FileName = "favorite";
    $t->Source = "system";
    $t->SidebarID = 1;
    $t->Content = '<li><a href="https://app.zblogcn.com/" target="_blank">Z-Blog应用中心</a></li><li><a href="https://weibo.com/zblogcn" target="_blank">Z-Blog官方微博</a></li><li><a href="https://bbs.zblogcn.com/" target="_blank">ZBlogger社区</a></li>';
    $t->HtmlID = "divFavorites";
    $t->Type = "ul";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_link'];
    $t->FileName = "link";
    $t->Source = "system";
    $t->SidebarID = 1;
    $t->Content = '<li><a href="https://github.com/zblogcn" target="_blank" title="Z-Blog on Github">Z-Blog on Github</a></li><li><a href="https://zbloghost.cn/" target="_blank" title="Z-Blog官方主机">Z-Blog主机</a></li>';
    $t->HtmlID = "divLinkage";
    $t->Type = "ul";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_misc'];
    $t->FileName = "misc";
    $t->Source = "system";
    $t->SidebarID = 1;
    $t->Content = '<li><a href="https://www.zblogcn.com/" target="_blank"><img src="{#ZC_BLOG_HOST#}zb_system/image/logo/zblog.gif" height="31" width="88" alt="Z-BlogPHP" /></a></li><li><a href="{#ZC_BLOG_HOST#}feed.php" target="_blank"><img src="{#ZC_BLOG_HOST#}zb_system/image/logo/rss.png" height="31" width="88" alt="订阅本站的 RSS 2.0 新闻聚合" /></a></li>';
    $t->HtmlID = "divMisc";
    $t->Type = "ul";
    $t->IsHideTitle = true;
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_authors'];
    $t->FileName = "authors";
    $t->Source = "system";
    $t->SidebarID = 0;
    $t->Content = "";
    $t->HtmlID = "divAuthors";
    $t->Type = "ul";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_previous'];
    $t->FileName = "previous";
    $t->Source = "system";
    $t->SidebarID = 0;
    $t->Content = "";
    $t->HtmlID = "divPrevious";
    $t->Type = "ul";
    $t->Save();

    $t = new Module();
    $t->Name = $zbp->lang['msg']['module_tags'];
    $t->FileName = "tags";
    $t->Source = "system";
    $t->SidebarID = 0;
    $t->Content = "";
    $t->HtmlID = "divTags";
    $t->Type = "ul";
    $t->Save();

    $a = new Post();
    $a->CateID = 1;
    $a->AuthorID = 1;
    $a->Tag = '';
    $a->Status = ZC_POST_STATUS_PUBLIC;
    $a->Type = ZC_POST_TYPE_ARTICLE;
    $a->Alias = '';
    $a->IsTop = false;
    $a->IsLock = false;
    $a->Title = $zbp->lang['zb_install']['hello_zblog'];
    $a->Intro = $zbp->lang['zb_install']['hello_zblog_content'];
    $a->Content = $zbp->lang['zb_install']['hello_zblog_content'];
    $a->IP = GetGuestIP();
    $a->PostTime = time();
    $a->CommNums = 0;
    $a->ViewNums = 0;
    $a->Template = '';
    $a->Meta = '';
    $a->Save();

    $a = new Post();
    $a->CateID = 0;
    $a->AuthorID = 1;
    $a->Tag = '';
    $a->Status = ZC_POST_STATUS_PUBLIC;
    $a->Type = ZC_POST_TYPE_PAGE;
    $a->Alias = '';
    $a->IsTop = false;
    $a->IsLock = false;
    $a->Title = $zbp->lang['zb_install']['guestbook'];
    $a->Intro = '';
    $a->Content = $zbp->lang['zb_install']['guestbook_content'];
    $a->IP = GetGuestIP();
    $a->PostTime = time();
    $a->CommNums = 0;
    $a->ViewNums = 0;
    $a->Template = '';
    $a->Meta = '';
    $a->Save();

    $zbp->LoadMembers(0);
    if (count($zbp->members) == 0) {
        echo $zbp->lang['zb_install']['not_insert_data'] . "<br/>";

        return false;
    } else {
        echo $zbp->lang['zb_install']['create_datainfo'] . "<br/>";

        return true;
    }
}

function SaveConfig()
{
    global $zbp;

    $zbp->option['ZC_BLOG_VERSION'] = ZC_BLOG_VERSION;
    $zbp->option['ZC_BLOG_TITLE'] = BLOG_NAME;
    $zbp->option['ZC_USING_PLUGIN_LIST'] = 'AppCentre|UEditor|Totoro|LinksManage';

    $zbp->option['ZC_BLOG_THEME'] = SplitAndGet("tpure|style", '|', 0);
    $zbp->option['ZC_BLOG_CSS'] = SplitAndGet("tpure|style", '|', 1);
    $zbp->option['ZC_DEBUG_MODE'] = false;
    $zbp->option['ZC_LAST_VERSION'] = $zbp->version;
    $zbp->option['ZC_NOW_VERSION'] = $zbp->version;

    $zbp->LoadCache();
    $app = $zbp->LoadApp('theme', 'default');
    $app->SaveSideBars();

    $app = $zbp->LoadApp('theme', 'Zit');
    $app->LoadSideBars();
    $app->SaveSideBars();

    $app = $zbp->LoadApp('theme', 'tpure');
    $app->LoadSideBars();
    $app->SaveSideBars();

    $app = $zbp->LoadApp('theme', $zbp->option['ZC_BLOG_THEME']);
    $app->LoadSideBars();

    $zbp->SaveOption();

    if (file_exists($zbp->path . 'zb_users/c_option.php') == false) {
        echo $zbp->lang['zb_install']['not_create_option_file'] . "<br/>";

        $s = "<pre>&lt;" . "?" . "php\r\n";
        $s .= "return ";
        $option = array();
        foreach ($zbp->option as $key => $value) {
            if (($key == 'ZC_DATABASE_TYPE') ||
                ($key == 'ZC_SQLITE_NAME') ||
                ($key == 'ZC_SQLITE_PRE') ||
                ($key == 'ZC_MYSQL_SERVER') ||
                ($key == 'ZC_MYSQL_USERNAME') ||
                ($key == 'ZC_MYSQL_PASSWORD') ||
                ($key == 'ZC_MYSQL_NAME') ||
                ($key == 'ZC_MYSQL_CHARSET') ||
                ($key == 'ZC_MYSQL_PRE') ||
                ($key == 'ZC_MYSQL_ENGINE') ||
                ($key == 'ZC_MYSQL_PORT') ||
                ($key == 'ZC_MYSQL_PERSISTENT') ||
                ($key == 'ZC_PGSQL_SERVER') ||
                ($key == 'ZC_PGSQL_USERNAME') ||
                ($key == 'ZC_PGSQL_PASSWORD') ||
                ($key == 'ZC_PGSQL_NAME') ||
                ($key == 'ZC_PGSQL_CHARSET') ||
                ($key == 'ZC_PGSQL_PRE') ||
                ($key == 'ZC_PGSQL_PORT') ||
                ($key == 'ZC_PGSQL_PERSISTENT') ||
                ($key == 'ZC_CLOSE_WHOLE_SITE')
            ) {
                $option[$key] = $value;
            }
        }
        $s .= var_export($option, true);
        $s .= ";\r\n</pre>";

        echo $s;
    }

    $zbp->Config('cache')->templates_md5 = '';
    $zbp->SaveCache();

    $zbp->Config('AppCentre')->enabledcheck = 1;
    $zbp->Config('AppCentre')->checkbeta = 0;
    $zbp->Config('AppCentre')->enabledevelop = 0;
    $zbp->Config('AppCentre')->enablegzipapp = 0;
    $zbp->SaveConfig('AppCentre');

    if (is_readable($file_base = $GLOBALS['usersdir'] . 'theme/' . $zbp->option['ZC_BLOG_THEME'] . '/include.php')) {
        if (CheckIncludedFiles($file_base) == false) {
            require $file_base;
        }
    }
    if (function_exists($fn = 'InstallPlugin_' . $zbp->option['ZC_BLOG_THEME'])) {
        $fn();
    }

    $zbp->template = $zbp->PrepareTemplate();
    $zbp->BuildTemplate();

    if (file_exists($zbp->path . 'zb_users/cache/compiled/' . $zbp->option['ZC_BLOG_THEME'] . '/index.php') == false) {
        echo $zbp->lang['zb_install']['not_create_template_file'] . "<br/>";
    }

    $zbp->LoadCategories();
    $zbp->LoadModules();
    $zbp->RegBuildModules();
    $zbp->modulesbyfilename['calendar']->Build();
    $zbp->modulesbyfilename['calendar']->Save();
    $zbp->modulesbyfilename['catalog']->Build();
    $zbp->modulesbyfilename['catalog']->Save();

    echo $zbp->lang['zb_install']['save_option'] . "<br/>";

    return true;
}
