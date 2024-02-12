var files = [];
let folderID = [...Array(30)].map(() => Math.floor(Math.random() * 10)).join('');
document.addEventListener('DOMContentLoaded', function() {
    const inputElements = document.querySelectorAll('input[type="file"].filepond');
    inputElements.forEach(function(inputElement) {
        FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginImageExifOrientation, FilePondPluginFileValidateSize, FilePondPluginFileValidateType);
        FilePond.create(inputElement, {
            server: {
                process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                    const formData = new FormData();
                    formData.append(fieldName, file);
                    fetch('filepond.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.text()).then(response => {
                        load(response);
                    })
                }
            },
            onprocessfile: (error, file) => {
                if (!error) {
                    const fieldName = inputElement.getAttribute('name');
                    const fileData = {
                        method: "add",
                        folderID: folderID,
                        fileId: file.id,
                        fileName: file.filename,
                        fileExtension: file.fileExtension,
                        fileSize: file.fileSize,
                        fileType: file.fileType,
                        fileHex: file.serverId
                    };
                    const fileJson = JSON.stringify(fileData);
                    fetch('filepond.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: fileJson
                        })
                        .then(response => response.text())
                        .then(data => {
                            if (data == "true") {
                                files.push({
                                    folderID: folderID,
                                    fileId: file.id,
                                    fileName: file.filename,
                                    fileExtension: file.fileExtension,
                                    fileSize: file.fileSize,
                                    fileType: file.fileType,
                                    fieldName: fieldName
                                });
                            }
                        })
                }
            },
            onremovefile: (error, file) => {
                if (!error) {
                    const fileData = {
                        method: "delete",
                        folderID: folderID,
                        fileId: file.id,
                        fileName: file.filename,
                        fileExtension: file.fileExtension,
                        fileSize: file.fileSize,
                        fileType: file.fileType,
                        fileHex: file.serverId
                    };
                    const fileJson = JSON.stringify(fileData);
                    fetch('filepond.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: fileJson
                        })
                        .then(response => response.text())
                }
            }
        });
    });
});