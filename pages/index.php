<?php
global $module;

use ICM\ModuleInstaller\ModuleInstallerException;

// TODO: Check if the current user is an admin.
if (!SUPER_USER) { // Not good, we want to test if the current user is an admin. SUPER_USER is not the same as admin.
    echo RCView::errorBox("You don't have permission to access this page.");
    exit();
}

// Step of the process
// 0: Upload a ZIP file
// 1: Fill in the path and version of the module
// 2: Confirm the installation
$step = 0;

// Form errors, by field name
$form_errors = array();
// Global warnings and errors
$global_warnings = array();

// Form handling, part 1
if (isset($_POST['upload'])) {
    // Validate that a file was uploaded
    if (empty($_FILES['module_zip']['name'])) {
        $form_errors['module_zip'] = "No file was uploaded.";
        // Validate that the file is a ZIP file
    } else if (pathinfo($_FILES['module_zip']['name'], PATHINFO_EXTENSION) != 'zip') {
        $form_errors['module_zip'] = "The uploaded file is not a ZIP file.";
    }

    try {
        $tmp_filename = $module->moveZipFileToTempLocation($_FILES['module_zip']['tmp_name']);
        // Get module information
        $module_info = $module->getModuleInformationFromZip($tmp_filename);
    }
    catch (ModuleInstallerException $e) {
        $form_errors['module_zip'] = $e->getMessage();
    }

    if (empty($form_errors)) {
        $step = 1;
    }
}
// Form handling, part 2
elseif (isset($_POST['install'])) {
    // Validate the path
    if (empty($_POST['module_path'])) {
        $form_errors['module_path'] = "The module path is required.";
    }
    // TODO: validate the path only has letters, numbers and underscores
    // Validate the version
    if (empty($_POST['module_version'])) {
        $form_errors['module_version'] = "The module version is required.";
    }
    // TODO validate the version is in the format x.y.z

    $step = (empty($form_errors)) ? 2 : 1;
}


if ($step > 0 && !isset($module_info) ) {
    if (!isset($_POST['tmp_filename'])) {
        $global_errors[] = "The temporary file does not exist anymore. Please provide the ZIP file again.";
        $step = 0;
    }
    else {
        $tmp_filename = trim(REDCap::escapeHtml($_POST['tmp_filename']));
        if (!file_exists(TEMP_FOLDER . '/' . $tmp_filename)) {
            $global_errors[] = "The temporary file does not exist anymore. Please provide the ZIP file again.";
            $step = 0;
        }
        else {
            $module_info = $module->getModuleInformationFromZip($tmp_filename);
        }
    }
}

// Installation of the module
if ($step == 2) {
    try {
        $user_module_info = clone $module_info;
        $user_module_info->path = trim(REDCap::escapeHtml($_POST['module_path']));
        $user_module_info->version = trim(REDCap::escapeHtml($_POST['module_version']));
        $module->installModuleFromZip($tmp_filename, $user_module_info);
        // Clean temp file
        $module->deleteTempZipFile($tmp_filename);
    }
    catch (ModuleInstallerException $e) {
        $global_errors[] = $e->getMessage();
        $step = 1;
    }
}

// HTML Rendering
$title = '<i class="fas fa-cube"></i> ' . REDCap::escapeHtml('External Module Installer');
echo RCView::h3(['class' => 'my-2'], $title);

// TODO A Breadcrumb with the current step


if (!empty($global_errors)) {
    echo RCView::errorBox(implode('<br>', $global_errors));
}
if (!empty($global_warnings)) {
    echo RCView::warnBox(implode('<br>', $global_warnings));
}

switch ($step) {
    case 0:
        // TODO Info about the module
        // Upload a ZIP file
        echo "<div class='my-4 p-4 border'>";
        include 'form-upload.php';
        echo "</div>";
        break;
    case 1:
        // Info about the module, and warnings about the installation
        include 'module-info.php';
        // Fill in the path and version of the module
        echo "<div class='my-4 p-4 border'>";
        include 'form-install.php';
        echo "</div>";
        break;
    case 2:
        // Confirm the installation
        include 'confirmation.php';
        break;
}

