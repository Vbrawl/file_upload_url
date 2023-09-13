<?php


namespace FILE_UPLOAD {


    class Database {
        private \DATABASE_ADAPTER\DBAdapter $db;

        public function __construct(\DATABASE_ADAPTER\DBAdapter $db) {
            $this->db = $db;

            $this->setup();
        }

        private function setup() {
            if(!$this->db->isConnected()) $this->db->connect();

            $this->db->exec("CREATE TABLE IF NOT EXISTS `file_uploads` (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                filename TEXT NOT NULL,
                mimetype TEXT NOT NULL,
                filesize INTEGER NOT NULL,
                password TEXT DEFAULT NULL
            );");

            $this->db->exec("CREATE TABLE IF NOT EXISTS `file_upload_chunks` (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                file_id INTEGER NOT NULL,
                chunk_index INTEGER NOT NULL DEFAULT 1,
                chunk_data TEXT NOT NULL,
                chunk_size INTEGER NOT NULL
            );");
        }

        public function get_file_status($id, $password = null) {
            if(!$this->db->isConnected()) $this->db->connect();

            $file = $this->db->queryPrepared('SELECT filename, mimetype, filesize FROM `file_uploads` WHERE id = :id AND password = :password', array(':id' => $id, ':password' => $password));
            if(!$file) return null;

            $status = $file->getRowA();
            if($status === false) return null;
            $status['chunks'] = array();

            $file_chunks = $this->db->queryPrepared('SELECT chunk_index, chunk_size FROM `file_upload_chunks` WHERE file_id = :id ORDER BY chunk_index', array(':id' => $id));
            if(!$file_chunks) return $status;

            while(($chunk = $file_chunks->getRowA()) !== false) {
                // array_push($status["chunks"], $chunk);
                $status['chunks'][$chunk['chunk_index']] = $chunk['chunk_size'];
            }

            return $status;
        }

        public function register_file($filename, $mimetype, $filesize, $password = null) {
            if(!$this->db->isConnected()) $this->db->connect();

            $this->db->execPrepared('INSERT INTO `file_uploads` (`filename`, `mimetype`, `filesize`, `password`) VALUES (:fname, :mtype, :fsize, :passwd);', array(':fname' => $filename, ':mtype' => $mimetype, ':fsize' => $filesize, ':passwd' => $password));

            return $this->db->lastInsertRowId();
        }

        public function delete_file($id, $delete = null) {
            // TODO: Add password
            if(!$this->db->isConnected()) $this->db->connect();

            $deleted = $this->db->execPrepared('DELETE FROM `file_uploads` WHERE id=:id;', array(':id' => $id));
            if($deleted) $deleted &= $this->db->execPrepared('DELETE FROM `file_upload_chunks` WHERE file_id=:id;', array(':id' => $id));
            return $deleted;
        }

        public function add_chunk($id, $chunk_index, $chunk_size, $chunk_data) {
            if(!$this->db->isConnected()) $this->db->connect();

            return $this->db->execPrepared('INSERT INTO `file_upload_chunks` (file_id, chunk_index, chunk_data, chunk_size) VALUES (:id, :index, :data, :size)', array(':id' => $id, ':index' => $chunk_index, ':size' => $chunk_size, ':data' => $chunk_data));
        }

        public function get_chunk_data($id, $chunk_index) {
            if(!$this->db->isConnected()) $this->db->connect();

            $results = $this->db->queryPrepared('SELECT chunk_data FROM `file_upload_chunks` WHERE file_id=:id AND chunk_index=:ci;', array(':id' => $id, ':ci' => $chunk_index));
            if($results) return $results->getRowI()[0];
        }
    }

}