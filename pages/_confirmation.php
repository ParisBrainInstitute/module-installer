<?php

global $module;

$ext_modules_url = APP_PATH_WEBROOT . 'ExternalModules/manager/control_center.php';

echo RCView::p([], "The module " . $module_info->name . " has been successfully installed in the following directory: "
        . "<code>" . APP_PATH_EXTERNAL_MODULES . '/' . $module_info->path . "_v" . $module_info->version . "</code>.");

echo RCView::p([], "You can now enable it from the Control Center.");

echo RCView::p([],
    "<a href='$ext_modules_url' class='btn btn-primary btn-sm text-white'><i class='fas fa-arrow-right mr-1'></i> Module Installation Page</a>"
);
