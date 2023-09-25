<?php
global $module;

use ICM\ModuleInstaller\ModuleInstaller;

if (!ModuleInstaller::isAdminWithModuleInstallPrivileges()) {
    echo RCView::errorBox("You don't have permission to access this page.");
    exit();
}

$module->cleanTempFolder();

$title = '<i class="fas fa-cube"></i> ' . REDCap::escapeHtml('External Module Installer');
echo RCView::h3(['class' => 'mt-2 mb-3'], $title);

echo RCView::p([], "The temp folder for this module, <code>" . ModuleInstaller::TEMP_FOLDER . "</code>, has been cleaned.");