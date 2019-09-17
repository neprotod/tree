<?php
    function GetFolders($dir) {
        $folders = array();

        if ($dh = @opendir($dir)) {
            while($file = readdir($dh)) {
                if (!preg_match("/^\.+$/", $file)) {
                    if (is_dir($dir.$file)) { $folders[] = $file; }
                }
            }
            closedir($dh);
        }

        @sort($folders, SORT_STRING);

        // Server-Cache lцschen
        clearstatcache();

        // Ordner-Array zurьckgeben
        return $folders;
    }
    
    
    function GetFiles($dir, $orderby) {
        $files = array();

        if ($dh = @opendir($dir)) {
            while($file = readdir($dh)) {
                if (!preg_match("/^\.+$/", $file)) {
                    if (is_file($dir.$file) && (IsFileExt($file, "jpg") || IsFileExt($file, "jpeg") || IsFileExt($file, "gif") || IsFileExt($file, "png"))) {
                        $files[0][] = $file;
                        $files[1][] = filemtime($dir.$file);
                        $files[2][] = filesize($dir.$file);
                        $files[3][] = Image_GetWidth($dir.$file);
                    }
                }
            }
            closedir($dh);
        }

        switch ($orderby) {
            case "0":
                @array_multisort($files[1], SORT_NUMERIC, SORT_DESC, $files[0], SORT_STRING, SORT_DESC);
                break;
            case "1":
                @array_multisort($files[0], SORT_STRING, SORT_ASC);
                break;
            case "2":
                @array_multisort($files[0], SORT_STRING, SORT_DESC);
                break;
            case "3":
                @array_multisort($files[2], SORT_NUMERIC, SORT_ASC, $files[0], SORT_STRING, SORT_DESC);
                break;
            case "4":
                @array_multisort($files[2], SORT_NUMERIC, SORT_DESC, $files[0], SORT_STRING, SORT_DESC);
                break;
            case "5":
                @array_multisort($files[3], SORT_NUMERIC, SORT_ASC, $files[0], SORT_STRING, SORT_DESC);
                break;
            case "6":
                @array_multisort($files[3], SORT_NUMERIC, SORT_DESC, $files[0], SORT_STRING, SORT_DESC);
                break;
            case "7":
                @array_multisort($files[1], SORT_NUMERIC, SORT_ASC, $files[0], SORT_STRING, SORT_DESC);
                break;
            case "8":
                @array_multisort($files[1], SORT_NUMERIC, SORT_DESC, $files[0], SORT_STRING, SORT_DESC);
                break;
        }


        // Datei-Array zurьckgeben
        return $files[0];
    }
    
    
    
    /*вспомогательные*/
    // Datei-Erweiterung zurьckgeben
    function GetFileExt($file) {
        $pfad_info = @pathinfo($file);
        return $pfad_info['extension'];
    }

    // Datei-Erweiterung prьfen
    function IsFileExt($file, $ext) {
        if (GetFileExt(strtolower($file)) == strtolower($ext)) { return true; }
        else { return false; }
}
?>