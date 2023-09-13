<?php



namespace FILE_UPLOAD {
    require_once("relocation.php");
    
    require_once(DATABASE_ADAPTER_PATH.'/main.php');
    require_once(REST_API_PATH.'/main.php');
    require_once(FILE_UPLOAD_PATH.'/src/database.php');
    require_once(FILE_UPLOAD_PATH.'/src/api.php');


    function load_all() {
        load_file(FILE_UPLOAD_RESOURCES_PATH.'/file_upload.js');
    }
}