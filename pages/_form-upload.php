<?php
/**
 * This file needs to be included in the main index.php page.
 * It will render the upload form.
 */


echo RCView::h4([], 'Upload a zip file containing the module');

$form = RCView::div(array('class'=>'form-group row my-4'),
    RCView::label(['for' => 'module_zip', 'class' => 'col-3 col-form-label text-end'], 'Module ZIP file:') .
    RCView::file(['name' => 'module_zip', 'id' => 'module_zip', 'class' => 'form-control form-control-sm col-6']) .
    RCView::div(['class' => 'col-3'],
        RCView::button(['class' => 'btn btn-primary btn-sm form-control col', 'type' => 'submit', 'name' => 'upload' ], 'Upload')
    )
);
if (isset($form_errors['module_path'])) {
    $form .= RCView::div(['class' => 'alert alert-danger p-1'], $form_errors['module_path']);
}

echo RCView::form(['method' => 'post', 'action' => '', 'enctype' => 'multipart/form-data'], $form);

