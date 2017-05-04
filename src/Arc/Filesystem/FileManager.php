<?php

namespace Arc\Filesystem;

use Arc\Exceptions\InsufficientPermissionsException;

class FileManager
{
    /**
     * Copy a file from the first path to the second path, overwriting any file that exists
     * already at that path
     *
     * @param string $fromPath
     * @param string $toPath
     **/
    public function copyOver($fromPath, $toPath)
    {
        $this->createDirectory(dirname($toPath));

        if (file_exists($toPath)) {
            unlink($toPath);
        }

        return copy($fromPath, $toPath);
    }

    /**
     * Creates a directory at the given path if one does not already exist
     *
     * @param string $dirPath
     **/
    public function createDirectory($dirPath, $permissions = null, $recursive = true)
    {
        if (empty($dirPath)) {
            throw new \Exception('Directory path cannot be empty');
        }

        if (is_dir($dirPath)) {
            return;
        }

        if (!is_writeable(dirname($dirPath))) {
            throw new InsufficientPermissionsException('Insufficient permissions to create directory ' . $dirPath);
        }
        mkdir($dirPath, $permissions ?? 0777, $recursive);
    }

    /**
     * Deletes any existing directory recursively at the given path and creates a fresh
     * one, creating any parent folders that do not already exist
     *
     * @param string $dirPath
     **/
    public function createFreshDirectory($dirPath)
    {
        $this->deleteDirectory($dirPath);

        $subfolders = explode('/', $dirPath);

        $this->createDirectory($dirPath);
    }

    /**
     * Deletes the given directory and all files it contains recursively
     *
     * @param string $dirPath
     **/
    public function deleteDirectory($dirPath)
    {
        if (! is_dir($dirPath)) {
            return;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDirectory($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /**
     * Returns true if the given directory contains no files
     *
     * @param string $dirPath
     * @return bool
     **/
    public function directoryIsEmpty($dirPath)
    {
        return count($this->getAllFilesInDirectory($dirPath)) == 0;
    }

    /**
     * Get all the files in a given directory and return them as an array of File objects
     *
     * @param string $dirPath The full path to the directory
     * @return array
     **/
    public function getAllFilesInDirectory($dirPath)
    {
        // Sanitise the given path
        $dirPath = $this->removeDoubleSlashes($dirPath);

        // If the given path is not a directory, return empty array
        if (!is_dir($dirPath)) {
            return [];
        }

        // Map the files in directory to file objects
        return array_map(function($fileName) use ($dirPath) {
            return $this->getFile($dirPath . '/' . $fileName);
        }, preg_grep('/^([^.])/', scandir($dirPath)));
    }

    /**
     * Get the given file and return a File object or null if no file exists
     *
     * @param string $filePath The full path to the file
     * @return array
     **/
    public function getFile($filePath)
    {
        if (!file_exists($filePath)) {
            return null;
        }

        return new File($filePath);
    }

    /**
     * Delete the directory or file at the given path or file object
     * (Directories will be deleted recursively)
     **/
    public function delete($file)
    {
        if (is_subclass_of($file, \SplFileObject::class)) {
            return $this->delete($file->getPathname());
        }

        if (is_dir($file)) {
            $this->deleteDirectory();
        }

        if (!file_exists($file)) {
            return;
        }
        unlink($file);
    }

    /**
     * Removes double forward slashes from the given path and returns the result
     **/
    public function removeDoubleSlashes($path)
    {
        return preg_replace('#/+#','/', $path);
    }
}
