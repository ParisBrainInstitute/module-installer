<?php

namespace ICM\ModuleInstaller;

/**
 * Deletes a directory recursively.
 *
 * @param $dir
 * @return bool
 */
function delete_directory($dir): bool
{
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

function renderInput(): string
{

}
