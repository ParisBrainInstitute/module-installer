<?php

global $module;

$ext_modules_url = APP_PATH_WEBROOT . 'ExternalModules/manager/control_center.php';

echo RCView::p([], 'From here, you can <strong>install an external module by uploading a zip file.</strong>');
echo RCView::p([], 'The module will be installed in the REDCap module folder of your instance: <code>' . APP_PATH_EXTERNAL_MODULES . '</code>.'
    . ', using the path and version of the module to create a subfolder in the module folder: <code>&lt;path&gt;_v&lt;version&gt;</code>.');
echo RCView::p([], "If a module with the same name and version already exists, you must 
<a href='$ext_modules_url'>uninstall it</a> first from the Control Center.");
echo RCView::p([], "After installation, you can <a href='$ext_modules_url'>enable the module</a> from the Control Center, as usual.");
echo RCView::confBox('<strong>Warning:</strong> No verification is done on the zip file.'
    . ' It is your responsibility to make sure that the zip file contains a valid and safe REDCap module.'
    . ' NEVER INSTALL A MODULE FROM AN UNTRUSTED SOURCE!');