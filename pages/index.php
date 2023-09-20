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

    if (empty($form_errors)) {
        try {
            $tmp_filename = $module->moveZipFileToTempLocation($_FILES['module_zip']['tmp_name']);
            // Get module information
            $module_info = $module->getModuleInformationFromZip($tmp_filename);
        } catch (ModuleInstallerException $e) {
            $form_errors['module_zip'] = $e->getMessage();
            if (isset($tmp_filename)) {
                $module->deleteTempZipFile($tmp_filename);
            }
            if (isset($module_info->parent_folder_name)) {
                $module->deleteTempFolder($module_info->parent_folder_name);
            }
        }
    }
    $step = (empty($form_errors)) ? 1 : 0;
}
// Form handling, part 2
elseif (isset($_POST['install'])) {
    // Validate the path: it must not be empty, and contain only letters, numbers, and underscores
    $module_path = trim(REDCap::escapeHtml($_POST['module_path']));
    if (empty($module_path)) {
        $form_errors['module_path'] = "The module path is required.";
    }
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $module_path)) {
        $form_errors['module_path'] = "The module path must contain only letters, numbers, and underscores.";
    }
    // Validate the version: it must not be empty, and follow the Semantic Versioning convention
    $module_version = trim(REDCap::escapeHtml($_POST['module_version']));
    if (empty($module_version)) {
        $form_errors['module_version'] = "The module version is required.";
    }
    elseif (!preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $module_version)) {
        $form_errors['module_version'] = "The module version must follow the Semantic Versioning convention (x.y.z).";
    }
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
echo RCView::h3(['class' => 'mt-2 mb-3'], $title);

// TODO A Breadcrumb with the current step


if (!empty($global_errors)) {
    echo RCView::errorBox(implode('<br>', $global_errors));
}
if (!empty($global_warnings)) {
    echo RCView::warnBox(implode('<br>', $global_warnings));
}

switch ($step) {
    case 0:
        include '_presentation.php';
        // Upload a ZIP file
        echo "<div class='my-4 p-4 border'>";
        include '_form-upload.php';
        echo "</div>";
        break;
    case 1:
        // Info about the module, and warnings about the installation
        include '_module-info.php';
        // Fill in the path and version of the module
        echo "<div class='my-4 p-4 border'>";
        include '_form-install.php';
        echo "</div>";
        break;
    case 2:
        // Confirm the installation
        include '_confirmation.php';
        break;
}

