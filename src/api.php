<?php


namespace FILE_UPLOAD {


    class RestAPI implements \REST_API\Rest {

        private Database $db;

        public function __construct(Database $db) {
            $this->db = $db;
        }

        public function onGET() {
            if(isset($_GET['id'])) {
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1));

                if($id === false) {
                    \REST_API\set_rest_code(400);
                    return array('error' => 'ID must be an integer >=1');
                }
            } else {
                \REST_API\set_rest_code(400);
                return array('error' => 'ID must be specified.');
            }

            if(isset($_GET['mode'])) {
                $mode = $_GET['mode'];
            } else {
                $mode = 'download';
            }

            $password = '';
            if(isset($_GET['password'])) {
                $password = $_GET['password'];
            }

            switch($mode) {
                case 'status':
                    $data = $this->db->get_file_status($id, $password);
                    break;

                case 'download':
                    $chunk_index = -1;
                    if(isset($_GET['chunk_index'])) {
                        $chunk_index = filter_var($_GET['chunk_index'], FILTER_VALIDATE_INT, array('min_range' => 1));
                    }

                    if($chunk_index === -1) {
                        $data = $this->db->get_file_status($id, $password);
                        if($data) {
                            header('Content-Disposition: attachment; filename="'.$data['filename'].'"');
                            header('Content-Type: '.$data['mimetype']);
                            header('Content-Length: '.$data['filesize']);
                            foreach ($data['chunks'] as $index => $size) {
                                $blob = $this->db->get_chunk_data($id, $index);
                                if($blob) echo base64_decode($blob);
                            }
                        }
                    }
                    else {
                        $data = $this->db->get_file_status($id, $password);
                        if($data) {
                            header('Content-Disposition: attachment; filename="'.$data['filename'].'"');
                            header('Content-Type: '.$data['mimetype']);
                            header('Content-Length: '.$data['chunks'][$chunk_index]);
                            $chunk = $this->db->get_chunk_data($id, $chunk_index);
                            if($chunk) echo base64_decode($chunk);

                        }
                    }
                    exit(); // NOTE: Stop REST_API from processing this command's output.
                    break;

                default:
                    \REST_API\set_rest_code(400);
                    $data = array('error' => 'The selected MODE does not exist');
                    break;
            }

            return $data;
        }

        public function onPOST() {
            if(isset($_GET['filename']) && $_GET['filename'] !== '') {
                $filename = $_GET['filename'];
            }
            else {
                \REST_API\set_rest_code(400);
                return array('error' => 'FILENAME cannot be empty.');
            }

            if(isset($_GET['mimetype']) && $_GET['mimetype'] !== '') {
                $mimetype = $_GET['mimetype'];
            }
            else {
                \REST_API\set_rest_code(400);
                return array('error' => 'MIMETYPE cannot be empty.');
            }

            if(isset($_GET['filesize'])) {
                $filesize = filter_var($_GET['filesize'], FILTER_VALIDATE_INT, array('min_range' => 1));

                if($filesize === false) {
                    \REST_API\set_rest_code(400);
                    return array('error' => 'FILESIZE must be an integer >= 1');
                }
            }
            else {
                \REST_API\set_rest_code(400);
                return array('error' => 'FILESIZE must be specified.');
            }

            $password = '';
            if(isset($_GET['password'])) {
                $password = $_GET['password'];
            }

            $id = $this->db->register_file($filename, $mimetype, $filesize, $password);

            return array('id' => $id);
        }

        public function onDELETE() {
            if(isset($_GET['id'])) {
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1));

                if($id === false) {
                    \REST_API\set_rest_code(400);
                    return array('error' => 'ID must be an integer >=1');
                }
            } else {
                \REST_API\set_rest_code(400);
                return array('error' => 'ID must be specified.');
            }

            $password = '';
            if(isset($_GET['password'])) {
                $password = $_GET['password'];
            }

            $deleted = $this->db->delete_file($id); // TODO: Add password
            return array('file_deleted' => $deleted);
        }


        public function onPUT() {
            if(isset($_GET['id'])) {
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1));

                if($id === false) {
                    \REST_API\set_rest_code(400);
                    return array('error' => 'ID must be an integer >=1');
                }
            } else {
                \REST_API\set_rest_code(400);
                return array('error' => 'ID must be specified.');
            }

            if(isset($_GET['chunk_index'])) {
                $chunk_index = filter_var($_GET['chunk_index'], FILTER_VALIDATE_INT, array('min_range' => 1));

                if($chunk_index === false) {
                    \REST_API\set_rest_code(400);
                    return array('error' => 'CHUNK_INDEX must be an integer >=1');
                }
            } else {
                \REST_API\set_rest_code(400);
                return array('error' => 'CHUNK_INDEX must be specified.');
            }

            $password = '';
            if(isset($_GET['password'])) {
                $password = $_GET['password']; // TODO: Use the password
            }

            $chunk_data = file_get_contents("php://input");
            $chunk_size = strlen($chunk_data);

            $this->db->add_chunk($id, $chunk_index, $chunk_size, base64_encode($chunk_data));
        }

    }



}