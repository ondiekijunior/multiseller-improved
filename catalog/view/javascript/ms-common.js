function MSUploader(params, userParams) {
    var paramsId = userParams.paramsId || 'product_form';
    var uploaderParams = getUploaderParams(paramsId);

    new plupload.Uploader($.extend(true, uploaderParams, params, {
        url: $('base').attr('href') + 'index.php?route=' + userParams.url,

        preinit : {
            Init: function(up, info) {
                if (userParams.initSelectors) {
                    $(userParams.initSelectors.addId).attr("id", up.id);
                    $(userParams.initSelectors.addClass).addClass(up.id);
                }
            }
        },

        init : {
            FileUploaded: function(up, file, info) {
                $("#"+file.id).fadeOut(500, function() {
                    $(this).html("").remove();
                });

                var data = [];

                try {
                    data = $.parseJSON(info.response);
                } catch(e) {
                    data.errors = []; data.errors.push(msGlobals.uploadError);
                }


                if (!$.isEmptyObject(data.errors)) {
                    if (userParams.dataErrorsCb) {
                        userParams.dataErrorsCb.call(this, data);
                    } else {
                        var errorText = '';
                        for (var i = 0; i < data.errors.length; i++) {
                            errorText += '<p>' + file.name + ': ' + data.errors[i] + '</p>';
                        }
                        $("." + up.id + ".error").append(errorText).fadeIn(1000);
                    }
                }


                if (userParams.fileUploadedCb) {
                    userParams.fileUploadedCb.call(this, data);
                }

                if (data.cancel) {
                    up.stop();
                }
            }
        }
    })).init();

}

function getUploaderParams(paramsId) {
    var MS_UPLOADER_PARAMS = {
        product_form: {
            runtimes : 'html5,html4,flash,silverlight',
            flash_swf_url: 'catalog/view/javascript/plupload/plupload.flash.swf',
            silverlight_xap_url : 'catalog/view/javascript/plupload/plupload.silverlight.xap',

            multipart_params : {
                'timestamp' : msGlobals.timestamp,
                'token'	 : msGlobals.token,
                'session_id': msGlobals.session_id,
                'product_id': msGlobals.product_id
            },

            preinit : {
                UploadFile: function(up, file) {
                    up.settings.multipart_params.fileCount = $('#' + up.id + " div").length;
                }
            },

            init: {
                StateChanged: function(up) {
                    if (up.state == plupload.STOPPED) {
                        $("."+up.id+".progress").fadeOut(500, function() { $(this).html("").hide(); });
                    } else {
                        $("."+up.id+".progress").show();
                    }
                },

                UploadProgress: function(up, file) {
                    $("#"+file.id).attr("aria-valuenow", file.percent);
                    $("#"+file.id).width(file.percent + '%');
                    $("#"+file.id).html(file.percent + '%');
                },

                FilesAdded: function(up, files) {
                    plupload.each(files, function(file) {
                        $('<div id="'+file.id+'" class="progress-bar" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100"></div>').appendTo("."+up.id+".progress").show();
                    });

                    $("."+up.id+".error").html('');
                    up.start();
                },

                Error: function(up, args) {
                    $("."+up.id+".error").append(msGlobals.uploadError).hide().fadeIn(2000);
                }
            }
        },
        seller_profile: {
            runtimes : 'gears,html5,flash,silverlight',
            multi_selection:false,
            flash_swf_url: 'catalog/view/javascript/plupload/plupload.flash.swf',
            silverlight_xap_url : 'catalog/view/javascript/plupload/plupload.silverlight.xap',

            multipart_params : {
                'timestamp' : msGlobals.timestamp,
                'token'     : msGlobals.token,
                'session_id': msGlobals.session_id
            },

            filters : [
                //{title : "Image files", extensions : "png,jpg,jpeg"},
            ]
        }
    };

    return MS_UPLOADER_PARAMS[paramsId];
}

