
(function(file_upload, undefined) {

    file_upload.API_File = "/api.php";

    file_upload.register_file = async function(file, password = '') {
        const filename = file.name;
        const filesize = file.size;
        const mimetype = file.type;

        const resp = await fetch(`${file_upload.API_File}?filename=${filename}&mimetype=${mimetype}&filesize=${filesize}&password=${password}`, {
            method: "POST"
        });

        if(resp.ok) {
            return (await resp.json()).id;
        }
    }

    file_upload.send_single_chunk = async function(id, chunk_index, chunk) {
        return await fetch(`${file_upload.API_File}?id=${id}&chunk_index=${chunk_index}`, {
            method: "PUT",
            body: chunk
        });
    }

    file_upload.send_file_in_chunks = async function(id, file, buffer = 1024**2) {
        const filesize = file.size;

        index = 1;
        sent = 0;

        while(sent < filesize) {
            chunk = file.slice(sent, sent+buffer);
            sent += chunk.size;

            file_upload.send_single_chunk(id, index++, chunk);
        }
    }


    file_upload.get_file_status = async function(id, password = null) {
        return await fetch(`${file_upload.API_File}?id=${id}&mode=status&password=${password}`);
    }

    file_upload.get_file = async function(id, password = null) {
        return await fetch(`${file_upload.API_File}?id=${id}&password=${password}`);
    }

    file_upload.get_file_chunk = async function(id, chunk_index, password = null) {
        return await fetch(`${file_upload.API_File}?id=${id}&chunk_index=${chunk_index}&password=${password}`);
    }

    file_upload.delete_file = async function(id, password = null) {
        return await fetch(`${file_upload.API_File}?id=${id}&password=${password}`);
    }




}(window.file_upload = window.file_upload || {}));