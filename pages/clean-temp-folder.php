<?php
global $module;

use ICM\ModuleInstaller\ModuleInstallerException;

// TODO: Check if the current user is an admin.
if (!SUPER_USER) { // Not good, we want to test if the current user is an admin. SUPER_USER is not the same as admin.
    echo RCView::errorBox("You don't have permission to access this page.");
    exit();
}

$module->cleanTempFolder();

$title = '<i class="fas fa-cube"></i> ' . REDCap::escapeHtml('External Module Installer');
echo RCView::h3(['class' => 'mt-2 mb-3'], $title);

echo RCView::p([], "The temp folder for this module, <code>" . $module::TEMP_FOLDER . "</code>, has been cleaned.");