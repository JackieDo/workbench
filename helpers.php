<?php

if (! function_exists('undir')) {
    /**
     * Remove entire directory
     *
     * @param  string  $dirPath The path to directory want to remove
     *
     * @return boolean          Whether the removal was successful or not
     */
    function undir($dirPath) {
        if (! $handle = opendir($dirPath)) {
            return false;
        }

        while($item = readdir($handle)) {
            if (($item != '.') && ($item != '..')) {
                $realpath = realpath($dirPath . DIRECTORY_SEPARATOR . $item);

                if (is_dir($realpath)) {
                    // Recursively calling custom copy function for sub directory
                    if (! undir($realpath)) {
                        return false;
                    }
                } else {
                    $removed = unlink($realpath);

                    if (! $removed) {
                        return false;
                    }

                    unset($removed);
                }
            }
        }

        closedir($handle);

        if (! rmdir($dirPath)) {
            return false;
        }

        return true;
    }
}

if (! function_exists('is_empty_dir')) {
    /**
     * Determine if directory is empty
     *
     * @param  string  $dir The path to directory
     *
     * @return boolean
     */
    function is_empty_dir($dir) {
        if (!is_readable($dir)) return NULL;
        return (count(scandir($dir)) == 2);
    }
}
