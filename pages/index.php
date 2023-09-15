<?php
global $module;

// TODO: Check if the current user is an admin.
if (!SUPER_USER) { // Not good, we want to test if the current user is an admin. SUPER_USER is not the same as admin.
    echo RCView::errorBox("You don't have permission to access this page.");
    exit();
}

// Form errors, by field name
$form_errors = array();

// Form handling
if (isset($_POST['submit'])) {
    // Validate that a file was uploaded
    if (empty($_FILES['module_zip']['name'])) {
        $form_errors['module_zip'] = "No file was uploaded.";
        // Validate that the file is a ZIP file
    } else if (pathinfo($_FILES['module_zip']['name'], PATHINFO_EXTENSION) != 'zip') {
        $form_errors['module_zip'] = "The uploaded file is not a ZIP file.";
    }

    $tmp_filename = $module->moveZipFileToTempLocation($_FILES['module_zip']['tmp_name']);

    // Get module information
    $module_info = $module->getModuleInformationFromZip($tmp_filename);

    echo RCView::warnBox("Module name: $module_info->name, version: $module_info->version, path: $module_info->path");

    // Install the module
    $success = $module->installModuleFromZip($tmp_filename, $module_info);

    // Clean temp file
    $module->deleteTempZipFile($tmp_filename);
}


// HTML Rendering

$title = '<i class="fas fa-cube"></i> ' . REDCap::escapeHtml('External Module Installer');
echo RCView::h3([], $title);

// HTML form creation
$form_content = "";

$form_content .= RCView::label(['for' => 'module_name'], 'Module name ')
               . RCView::input(['type' => 'text', 'name' => 'module_name', 'id' => 'module_name', 'style' => 'margin-bottom: 10px;'])
               . RCView::br();
if (isset($form_errors['module_name'])) {
    $form_content .= RCView::div(['class' => 'alert alert-danger p-1'], $form_errors['module_name']);
}

$form_content .= RCView::label(['for' => 'module_zip'], 'Module ZIP file ')
               . RCView::file(['name' => 'module_zip', 'id' => 'module_zip', 'style' => 'margin-bottom: 10px;'])
               . RCView::br();
if (isset($form_errors['module_zip'])) {
    $form_content .= RCView::div(['class' => 'alert alert-danger p-1'], $form_errors['module_zip']);
}

$form_content .= RCView::button(['class' => 'btn btn-primary', 'type' => 'submit', 'name' => 'submit'], 'Install module');

echo RCView::form(['method' => 'post', 'action' => '', 'enctype' => 'multipart/form-data', 'style' => 'margin-bottom: 20px;'], $form_content);


