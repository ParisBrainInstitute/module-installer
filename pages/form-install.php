<?php

/**
 * This file needs to be included in the main index.php page.
 * It will render the installation form (path and version for the module).
 */

echo RCView::h4([], 'Installation path and version');
echo RCView::p([], "Please provide a base path and a version for the module. The base path is relative to the REDCap modules folder. The version must follow the <a href='https://semver.org/' target='_blank'>Semantic Versioning</a> convention (x.y.z).");
echo RCView::p(['class' => 'mb-4'], "The module will be installed in the following folder: <code>" . APP_PATH_EXTERNAL_MODULES . '/&lt;base_path&gt;_v&lt;version&gt;</code>');

$module_path = (isset($_POST['module_path'])) ? $_POST['module_path'] : (isset($module_info) ? $module_info->path : '');
$module_version = (isset($_POST['module_version'])) ? $_POST['module_version'] : (isset($module_info) ? $module_info->version : '');

$form = RCView::div(array('class'=>'form-group row my-4'),
        RCView::label(['for' => 'module_path', 'class' => 'col-3 col-form-label text-end'], 'Module path:') .
        RCView::input(['type' => 'text', 'name' => 'module_path', 'id' => 'module_path', 'value' => $module_path, 'class' => 'form-control form-control-sm col-6'])
);
if (isset($form_errors['module_path'])) {
    $form .= RCView::div(['class' => 'alert alert-danger p-1'], $form_errors['module_path']);
}

$form .= RCView::div(array('class'=>'form-group row mb-4'),
    RCView::label(['for' => 'module_version', 'class' => 'col-3 col-form-label text-end'], 'Module version:') .
    RCView::input(['type' => 'text', 'name' => 'module_version', 'id' => 'module_version', 'value' => $module_version, 'class' => 'form-control form-control-sm col-6'])
);
if (isset($form_errors['module_version'])) {
    $form .= RCView::div(['class' => 'alert alert-danger p-1'], $form_errors['module_version']);
}

$form .= RCView::hidden(['name' => 'tmp_filename', 'value' => $tmp_filename]);

$form .= RCView::div(array('class'=>'form-group row mb-4'),
    RCView::div(['class' => 'col-3']) .
    RCView::div(['class' => 'm-0'],
        RCView::button(['class' => 'btn btn-primary btn-sm', 'type' => 'submit', 'name' => 'install'], 'Install module')
    )
);

echo RCView::form(['method' => 'post', 'action' => '', 'style' => 'margin-bottom: 20px;'], $form);
