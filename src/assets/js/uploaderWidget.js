/**
 *  UploaderWidget.js
 *
 *  Общий файл для работы с виджетом раширениея MUploader
 */
/*
$(document).ready(function () {

    app.document.on('click', '.uploader__remove', function () {
        var $uploadContainer = $(this).closest('.uploader__layout');

        $uploadContainer.closest('.input-field').find('input').val('');
        $uploadContainer.addClass('uploader__layout--empty');
    });

    app.document.on('click', '.uploader__add-file', function (e) {
        e.preventDefault();
        window.$uploadContainer = $(this).closest('.uploader__layout');
        window.uploadNotify = new Notify();
        $uploadContainer.removeClass('.uploader__layout--drag');
    });

    app.document.on('dragover', '.uploader__layout', function (e) {
        e.preventDefault();
        e.stopPropagation();

        $(this).addClass('uploader__layout--drag');
    });

    app.document.on('dragleave', '.uploader__layout', function (e) {
        e.preventDefault();
        e.stopPropagation();

        $(this).removeClass('uploader__layout--drag');
    });

    app.document.on('drop', '.uploader__layout', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $input = $(this);
        var Uploader = $input.closest('.uploader').data('instance');

        window.fileTransport = null;
        window.$uploadContainer = $(this);
        window.uploadNotify = new Notify();

        $uploadContainer.removeClass('uploader__layout--drag');

        Uploader.addFile(e.originalEvent.dataTransfer.files[0]);
        Uploader.start();
    });

    app.uploadFileInit();
});

UploaderFile = function () {
    this.getNewID = function () {
        return '_' + Math.random().toString(36).substr(2, 9);
    };

    this.getOptions = function ($input) {
        var input = $input.closest('.input-field').find('input'),
            result = input.data();

        result.name = input.attr('name');
        return result;
    };

    this.callbacks = function () {
        return {
            BeforeUpload: function (up) {
                var userTempName = $(document).find('input[name=userTempName]').val(),
                    oldFileName = $uploadContainer.find('.input-field').find('input').val();

                up.settings.multipart_params.uploadFileName = 'file';

                if (oldFileName !== undefined) up.settings.multipart_params.oldFileName = oldFileName;
                if (userTempName !== undefined) up.settings.multipart_params.userTempName = userTempName;

                $uploadContainer.addClass('uploader__layout--empty');
            },

            FilesAdded: function (up, files) {
                $uploadContainer.find('.uploader__add-file')
                    .html('<div class="preloader-wrapper active"><div class="spinner-layer spinner-blue"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div><div class="spinner-layer spinner-red"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div><div class="spinner-layer spinner-yellow"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div><div class="spinner-layer spinner-green"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>');

                up.start();
            },

            FileFiltered: function (up, file) {
                if (up.files.length > 1) {
                    up.removeFile(file.id);
                }
            },


            FileUploaded: function (up, file, response) {
                var responseJSON = JSON.parse(response.response);

                $uploadContainer.removeClass('uploader__layout--empty');

                if (responseJSON.error_code !== 0) {
                    ResponseError.getAllMessages(responseJSON.error_messages).forEach(function (currentValue) {
                        uploadNotify.add(currentValue);
                        uploadNotify.show();
                    });

                    return;
                }

                if ($uploadContainer.hasClass('uploader__layout--file')) {
                    var baseNameSplit = responseJSON.result.basename.split('.');
                    $uploadContainer.find('.uploader__file .uploader__file-content').text(baseNameSplit[baseNameSplit.length - 1]);
                } else if ($uploadContainer.hasClass('uploader__layout--image')) {
                    $uploadContainer.find('.uploader__file .uploader__file-content').html($('<img >', {
                        src: responseJSON.result.url
                    })).trigger('uploadImage', responseJSON.result);

                }

                $uploadContainer.find('.uploader__add-file').html('<i class="material-icons">file_upload</i>');
                $uploadContainer.find('.uploader__file .uploader__file-name').text(responseJSON.result.basename);

                $uploadContainer.closest('.input-field').find('input').val(responseJSON.result.basename);

                var inputTempName = $(document).find('input[name=userTempName]');

                if (!inputTempName.length) {
                    $uploadContainer.closest('form').append($('<input />', {
                        name : 'userTempName',
                        value: responseJSON.result.userTempName
                    }));
                }
            },

            UploadComplete: function (up) {
                window.$uploadContainer = null;
                window.uploadNotify = null;

                up.splice(0, 1)
            },

            Error: function (up, err) {
                uploadNotify.show(err.file.name + ' (' + err.message + ')', {speed: 5000, class: 'error'});
            }
        }
    }
};
*/

Uploader = {
    /**
     * Загрузка изображения в редакторе
     *
     * @param input
     * @param data
     * @param type
     * @param fileName
     */
    uploadRedactor: function (input, data, type, fileName) {
        if (input.files[0] == undefined) return;

        var fd = new FormData(),
            $input = $(input),
            $container = $input.closest('.row'),
            loader = $input.siblings('i')[0],
            $image = $container.find('.uploader-image');

        $(loader).addClass('loader');

        fd.append(input.name, input.files[0]); // прикрепляем файл
        fd.append('uploadFileName', input.name);

        $.each(data, function (index, value) {
            fd.append(index, value);
        });

        delete data.redactor;

        $.ajax({
            url: $input.data('upload-url'),
            type: 'POST', dataType: 'json', data: fd,
            cache: false, contentType: false, processData: false,

            beforeSend: function () {

            },

            success: function (data) {
                if (data.error_code === 0) {
                    if (type == 'image') {
                        $('#' + fileName).val(data.result.url);
                    } else if (type == 'file') {
                        var $targetInput = $('#' + fileName),
                            $inputs = $targetInput.closest('.mce-panel').find('input');

                        $targetInput.val(data.result.url);
                        $inputs.eq($inputs.index($targetInput) + 1).val(data.result.fileName);
                    }
                } else {
                    console.log(data.error_messages);
                    $image.val('');
                }
            },

            error: function (xhr) {
                switch (xhr.status) {
                    case 404:
                        console.log('Ошибка! Страница не найдена!');
                        break;

                    case 403:
                        console.log('Ошибка! Доступ запрещен!');
                        break;
                    default :
                        console.log('Ошибка');
                }
            },

            complete: function () {
                $(loader).removeClass('loader');
                $input.replaceWith($input.clone(true)); // Clearing <input type='file' />
            }
        });

    },

    browserFileCallback: function (redactorID, inputName, type) {
        var $redactor = $(redactorID),
            $fileInput = $redactor.siblings('.redactor-file');

        /* Снятие дублирующихся обработчиков  */
        $fileInput.off('change.uploaderImage');

        $fileInput.on('change.uploaderImage', function (e) {
            e.preventDefault();

            Uploader.uploadRedactor(e.target, $redactor.data(), type, inputName);
        });

        $fileInput.trigger('click');
    }
};