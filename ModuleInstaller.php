<?php

namespace ICM\ModuleInstaller;

include_once 'utils/functions.php';
include_once 'utils/ModuleInstallerException.php';

use ICM\ModuleInstaller\ModuleInstallerException;

define('APP_PATH_EXTERNAL_MODULES', dirname(APP_PATH_DOCROOT) . DIRECTORY_SEPARATOR . 'modules');

class ModuleInformation
{
    public string $parent_folder_name;
    public string $name = "";
    public string $version = "";
    public string $path = "";
}


class ModuleInstaller extends \ExternalModules\AbstractExternalModule
{
    const TEMP_FOLDER = APP_PATH_TEMP . DIRECTORY_SEPARATOR . 'module_installer';

    public function __construct()
    {
        parent::__construct();

        if (file_exists(self::TEMP_FOLDER) && !is_dir(self::TEMP_FOLDER)) {
            throw new ModuleInstallerException("The temporary folder is not a directory.");
        }
        if (!file_exists(self::TEMP_FOLDER)) {
            // Create the temporary folder
            if (!mkdir(self::TEMP_FOLDER, 0777, true)) {
                throw new ModuleInstallerException("The temporary folder could not be created.");
            }
        }
    }

    public function redcap_module_system_disable($version)
    {
        // Delete the temporary folder
        delete_directory(self::TEMP_FOLDER);
    }

    /**
     * Return whether the current user is an admin with module install privileges.
     * @return bool
     */
    public static function isAdminWithModuleInstallPrivileges(): bool
    {
        return (
            // For REDCap 10.1.0+
            (defined("ACCESS_EXTERNAL_MODULE_INSTALL") && ACCESS_EXTERNAL_MODULE_INSTALL == '1')
            // For versions prior to REDCap 10.1.0
            || (!defined("ACCESS_EXTERNAL_MODULE_INSTALL") && defined("SUPER_USER") && SUPER_USER == '1')
        );
    }

    /**
     * Create the temporary folder if it does not exist, or clean it (delete everything inside) if it exists.
     *
     * @return void
     */
    public function cleanTempFolder(): void
    {
        if (file_exists(self::TEMP_FOLDER) && is_dir(self::TEMP_FOLDER)) {
            delete_directory(self::TEMP_FOLDER);
        }
        // (Re-)create the temporary folder
        if (!mkdir(self::TEMP_FOLDER, 0777, true)) {
            throw new ModuleInstallerException("The temporary folder could not be created.");
        }
    }

    /**
     * Moves the file to a temporary location and returns and filename.
     *
     * @param $uploaded_file
     * @return string
     * @throws ModuleInstallerException
     */
    public function moveZipFileToTempLocation($filename): string
    {
        // Create a temporary filename
        $temp_filename = bin2hex(random_bytes(16)) . '.zip';

        // Move the uploaded file to the temporary location
        if (!move_uploaded_file($filename, self::TEMP_FOLDER . '/' . $temp_filename)) {
            throw new ModuleInstallerException("The uploaded file could not be moved to a temporary location.");
        }
        return $temp_filename;
    }

    /**
     * Deletes the temporary ZIP file.
     *
     * @param string $temp_filename
     * @return void
     */
    public function deleteTempZipFile(string $temp_filename): void
    {
        unlink(self::TEMP_FOLDER . '/' . $temp_filename);
    }

    /**
     * Delete a temporary folder inside the self::TEMP_FOLDER location.
     * @param string $temp_folder
     * @return void
     */
    public function deleteTempFolder(string $temp_folder): void
    {
        if (!empty($temp_folder) && is_dir(self::TEMP_FOLDER . '/' . $temp_folder)) {
            delete_directory(self::TEMP_FOLDER . '/' . $temp_folder);
        }
    }

    public function getModuleInformationFromZip($tmp_filename): ModuleInformation
    {
        $module_info = new ModuleInformation();

        try {
            // Extract the ZIP in place
            $zip = new \ZipArchive;
            if ($zip->open(self::TEMP_FOLDER . '/' . $tmp_filename) === TRUE) {
                // Get the parent folder name
                $module_info->parent_folder_name = rtrim($zip->getNameIndex(0), '/');
                // Extract the ZIP file in place
                $zip->extractTo(self::TEMP_FOLDER);
                $zip->close();
            } else {
                throw new ModuleInstallerException("The ZIP file could not be opened.");
            }

            // Check that the module contains a config.json file and read it
            if (!file_exists(self::TEMP_FOLDER . '/' . $module_info->parent_folder_name . '/config.json')) {
                throw new ModuleInstallerException("The ZIP file does not contain a config.json file.");
            }

            // Get the module name from the config.json file
            $config = json_decode(file_get_contents(self::TEMP_FOLDER . '/' . $module_info->parent_folder_name . '/config.json'), true);

            if (!isset($config['name'])) {
                throw new ModuleInstallerException("The config.json file does not contain a name.");
            }
            $module_info->name = $config['name'];

            // Extract the version number (x.y.z) from the parent folder name with a regex
            if (preg_match('/-([0-9]+)\.([0-9]+)\.([0-9]+)$/', $module_info->parent_folder_name, $matches)) {
                $module_info->version = substr($matches[0], 1);
            }

            // Get the module path from the parent folder name
            $path_from_name = $module_info->version ? substr($module_info->parent_folder_name, 0, -strlen($module_info->version) - 1)
                : $module_info->parent_folder_name;
            // Convert hyphens and special characters to underscores, and convert to lowercase
            $module_info->path = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $path_from_name));

        }
        catch (ModuleInstallerException $e) {
            throw new ModuleInstallerException($e->getMessage());
        } finally {
            $this->deleteTempFolder($module_info->parent_folder_name);
        }

        return $module_info;
    }

    public function installModuleFromZip($tmp_filename, $module_info)
    {
        $module_path = $module_info->path . '_v' . $module_info->version;

        // Check if the module already exists
        if (file_exists(APP_PATH_EXTERNAL_MODULES . '/' . $module_path)) {
            throw new ModuleInstallerException("<strong>The module already exists in $module_path.</strong><br>
             Please uninstall it first, or choose a different path and/or version.");
        }

        try {
            // Rename the parent folder of the zip to the module path
            $zip = new \ZipArchive;
            if ($zip->open(self::TEMP_FOLDER . DIRECTORY_SEPARATOR . $tmp_filename) !== TRUE) {
                throw new ModuleInstallerException("The ZIP file could not be opened.");
            }
            $i = 0;
            while ($item_name = $zip->getNameIndex($i)){
                $item_name_end = substr($item_name, strpos($item_name, "/"));
                $zip->renameIndex($i++, $module_path . DIRECTORY_SEPARATOR . $item_name_end);
            }
            $zip->close();

            // Now extract the zip to the modules folder
            $zip = new \ZipArchive;
            if ($zip->open(self::TEMP_FOLDER . DIRECTORY_SEPARATOR . $tmp_filename) === TRUE) {
                $zip->extractTo(APP_PATH_EXTERNAL_MODULES);
                $zip->close();
            } else {
                throw new ModuleInstallerException("The ZIP file could not be opened.");
            }
        }
        catch (\Exception $e) {
            throw new ModuleInstallerException($e->getMessage());
        }
    }

    /**
     * Returns the list of installed versions of a module.
     *
     * @param $module_name
     * @return array
     */
    public function installedVersionsOf($module_name): array
    {
        // Loop through the folders into the modules folder
        $installed_versions = [];
        foreach (scandir(APP_PATH_EXTERNAL_MODULES) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            // Check if the folder contains a config.json file and read it
            if (file_exists(APP_PATH_EXTERNAL_MODULES . '/' . $item . '/config.json')) {
                $config = json_decode(file_get_contents(APP_PATH_EXTERNAL_MODULES . '/' . $item . '/config.json'), true);
                if (isset($config['name']) && $config['name'] == $module_name) {
                    $installed_versions[] = $item;
                }
            }
        }
        return $installed_versions;
    }
}