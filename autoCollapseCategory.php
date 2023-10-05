<?php
$plugins->add_hook("global_start", "setCollapsedCategory");

function autoCollapseCategory_info()
{
    return array(
        "name" => "Autocollapse Category",
        "description" => "Allows for categories to be collapsed by default.",
        "website" => "http://eagle-time.org",
        "author" => "Rhababo",
        "authorsite" => "github.com/Rhababo",
        "version" => "1.0",
        "guid" => "",
        "codename" => "autoCollapseCategory",
        "compatibility" => "18*"
    );
}

function autoCollapseCategory_activate()
{

}

function autoCollapseCategory_deactivate()
{
}

function setCollapsedCategory()
{
    global $mybb, $db;

    if(!isset($mybb->settings['categorySelect'])){
        return;
    }
    if($mybb->settings['categorySelect']=="") {
        return;
    }
    $categorySelect = $mybb->settings['categorySelect'];

    //check for AllCategories selection
    if($categorySelect == "-1")
    {
        $categorySelect = "";
        $query = $db->simple_select("forums", "fid", "type='c' AND pid=0 AND active!=0 AND password=''");
        while($categoryID = $db->fetch_array($query)){
            $categorySelect .= $categoryID['fid'].",";
        }
    }

    //categorySelect is a string, split it by ','
    $categoryArray = is_string($categorySelect) ? explode(',', $categorySelect) : (array)$categorySelect;
    $collapsed_names = array_map(function($value) {
        return 'cat_' . $value;
    }, $categoryArray);

    $mybb->cookies['collapsed'] = trim(implode('|', (array)$collapsed_names));
    my_setcookie("collapsed", $mybb->cookies['collapsed']);
}

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.");
}

function autoCollapseCategory_install()
{
    global $db, $mybb;

    $setting_group = array(
        'name' => 'autoCollapseCategorySettingGroup',
        'title' => 'Auto Collapse Categories',
        'description' => 'Select the categories to be collapsed by default',
        'disporder' => 5,
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        //list of forums
        'categorySelect' => array(
            'title' => 'Select Categories to collapse',
            'description' => 'Choose one or more categories to collapse',
            'optionscode' => 'forumselect',
            'value' => "",
            'disporder' => 2
        )
    );

    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

    rebuild_settings();
}

function autoCollapseCategory_is_installed()
{
    global $mybb;
    if(isset($mybb->settings['categorySelect']))
    {
        return true;
    }

    return false;
}

function autoCollapseCategory_uninstall()
{
    global $db;

    $db->delete_query('settings', "name IN ('categorySelect')");
    $db->delete_query('settinggroups', "name = 'autoCollapseCategorySettingGroup'");

    rebuild_settings();
}
