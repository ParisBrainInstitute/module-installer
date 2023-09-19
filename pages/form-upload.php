<?php
/**
 * This file needs to be included in the main index.php page.
 * It will render the upload form.
 */

// HTML form creation
$form = RCView::label(['for' => 'module_zip'], 'Module ZIP file ')
      . RCView::file(['name' => 'module_zip', 'id' => 'module_zip', 'style' => 'margin-bottom: 10px;'])
      . RCView::br();
if (isset($form_errors['module_zip'])) {
    $form .= RCView::div(['class' => 'alert alert-danger p-1'], $form_errors['module_zip']);
}

$form .= RCView::button(['class' => 'btn btn-primary', 'type' => 'submit', 'name' => 'upload'], 'Upload module zip');

echo RCView::form(['method' => 'post', 'action' => '', 'enctype' => 'multipart/form-data', 'style' => 'margin-bottom: 20px;'], $form);

