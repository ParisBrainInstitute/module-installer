<?php

global $module;
global $module_info;

echo RCView::p([], "The zip you provided contains a module with the following information:");
echo RCView::ul([],
    RCView::li([], "Name: <strong>" . $module_info->name . "</strong>" ) .
    RCView::li([], "Version: <code>" . $module_info->version . "</code>") .
    RCView::li([], "Installation Path: <code>" . $module_info->path . "</code>")
);

// Do a module with the same name already exist?
$installed_versions = $module->installedVersionsOf($module_info->name);
if (!empty($installed_versions)) {
    echo RCView::p([], "A module with the same name already exists in the following directories:");
    echo RCView::ul([], implode('', array_map(function($version) {
        return RCView::li([], "<code>$version</code>");
    }, $installed_versions)));
}

if (in_array($module_info->path . "_v" . $module_info->version, $installed_versions)) {
    echo RCView::confBox("<strong>The module is already installed in the path <code>" . $module_info->path . "_v" . $module_info->version . "</code>.</strong><br>
        You must uninstall it first if you want to reinstall it, or you can provide a different path and/or version in the following form.");
}

