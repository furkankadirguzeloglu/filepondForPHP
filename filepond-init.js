document.addEventListener('DOMContentLoaded', function() {
    const inputElements = document.querySelectorAll('input[type="file"].filepond');
    inputElements.forEach(function(inputElement) {
        FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginImageExifOrientation, FilePondPluginFileValidateSize, FilePondPluginFileValidateType);
        const pond = FilePond.create(inputElement, {
            server: {
                process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                    const formData = new FormData();
                    formData.append(fieldName, file);
                    fetch('filepond.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => {
                        return response.text();
                    }).then(response => {
                        var responseJSON = JSON.parse(response);
                        if (responseJSON.status === "error") {
                            alert(response.message);
                            error(response.message);
                        } else {
                            load(response);
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                    });
                },
            },
            onremovefile: (error, file) => {
                if (!error) {
                    const fileData = {
                        method: "delete",
                        fileName: file.filename,
                        fileSize: file.fileSize,
                        fileType: file.fileType
                    };
                    const fileJson = JSON.stringify(fileData);
                    fetch('filepond.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: fileJson
                    }).then(response => {
                        return response.text();
                    }).then(response => {
                        var responseJSON = JSON.parse(response);
                        if (responseJSON.status === "error") {
                            alert(response.message);
                            error(response.message);
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                    });
                }
            }
        });
    });
});