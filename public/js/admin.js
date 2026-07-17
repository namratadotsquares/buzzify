'use strict';

/* Show loader start */
function showLoader() {
    $('#overlay').fadeIn();
}
/* Show loader end */

/* Hide loader start */
function hideLoader() {
    $('#overlay').fadeOut();
}

function myToastr(msg, type) {
    toastr.remove();
    if (type == 'error') {
        toastr.error(msg);
    } else if (type == 'success') {
        toastr.success(msg);
    }
}

function triggerEmailsField(val) {
    if (val == 'specific') {
        $('.emails').removeClass('hide');
    } else {
        $('.emails').addClass('hide');
    }

}


function resetFilter() {
    var newURL = location.href.split("?")[0];
    console.log(newURL);
    window.history.pushState('object', document.title, newURL);
    location.reload();
}

function resetFilterfeed() {
    var newURL = location.href.split("?")[0];
    console.log(newURL);
    location.href = newURL;
    //   window.history.pushState('object', document.title, newURL);
    //   location.reload();
}

function resetFilterBlog() {
    var newURL = location.href.split("&")[0];
    console.log(newURL);
    location.href = newURL;
    //   window.history.pushState('object', document.title, newURL);
    //   location.reload();
}

function searchClick() {
    $('#search').click();
}


$(document).ready(function () {
    $('.image-popup').magnificPopup({
        type: 'image',
        closeOnContentClick: true,
        mainClass: 'mfp-fade',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
        }
    });
});

$("#is_voting_enable").on('change', function () {
    if ($(this).is(':checked')) {
        $(".showTopicInput").removeClass('hide');
    }
    else {
        $(".showTopicInput").addClass('hide');
    }
});


function resetForm(formID) {
    $("#" + formID).closest('form').find("input[type=text], input[type=number], textarea").val("");
    $("#" + formID).closest('form').find("input[type=checkbox]").removeAttr("checked");
    $("#" + formID).closest('form').find("input[type=radio]").removeAttr("checked");
    $("#custom").attr("checked", true);
    $('select').val('');
    $('#createBtn').html('Create');
    $('#image_add').attr('src', '');
}



function printArea() {
    document.getElementById('iframeid').contentWindow.print();
}

function getFormData($form) {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};
    var l = 0;
    $.map(unindexed_array, function (n, i) {
        if (n['name'] == 'category_id[]') {
            if (l == 0) {
                indexed_array['category_id'] = [];
            }
            indexed_array['category_id'].push(n['value']);
            l++;
        } else {
            indexed_array[n['name']] = n['value'];
        }
    });
    return indexed_array;
}

function validateEmail(email) {
    var x = email;
    var atpos = x.indexOf("@");
    var dotpos = x.lastIndexOf(".");
    if (atpos < 1 || dotpos < atpos + 2 || dotpos + 2 >= x.length) {
        return true;
    } else {
        return false;
    }
}

function setDataLimit(limit, getData, type, portal) {
    var url;
    if (getData == 'NA') {
        window.location.href = base_url + "/" + portal + "/" + type + "?per_page=" + limit;
    } else {
        var res = getData.split("&");
        var myarray = [];
        $.each(res, function (key, value) {
            var res1 = value.split("=");
            if (res1[0] == 'per_page') {
                res1[1] = limit;
            }
            var newRes = res1.join("=");
            myarray.push(newRes);
        });
        var newUrl = myarray.join("&");
        if (newUrl) {
            window.location.href = base_url + "/" + portal + "/" + type + "?" + newUrl;
        }
    }
}

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});


var importerobjects = [];
$("#postal").typeahead({
    source: function (query, process) {
        console.log(query);
        if ($("#postal").val() != "") {
            var path = base_url + "/autocomplete";
            var map = {};
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                }
            });
            $.post(path, {
                term: query.toString()
            }, function (data) {
                console.log(data);
                if (data) {
                    $.each(data, function (i, object) {
                        importerobjects.push(object.email);
                    });
                }
                return process(importerobjects);
            });
        }
    },
});

function add_category(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.name != '') {
        if (data.color != '') {
            if (blogThumbImage != undefined || data.id) {
                $.ajax({
                    type: 'POST',
                    url: base_url + "/add-update-category",
                    headers: {},
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify(data),
                    success: function (response) {
                        if (response.success) {
                            myToastr(response.message, 'success');
                            setTimeout(function () {
                                window.location.reload();
                            }, 500);
                        } else {
                            myToastr(response.message, 'error');
                        }
                    }
                });
            } else {
                myToastr('Please Upload Category Image', 'error');
            }
        } else {
            myToastr('Select category color', 'error');
        }
    } else {
        myToastr('Enter category', 'error');
    }
}

function add_rss_feed_src(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    var re = /^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/;
    if (data.category_id != '') {
        if (data.rss_name != '') {
            if (data.rss_url != '') {
                if (/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(data.rss_url)) {
                    $.ajax({
                        type: 'POST',
                        url: base_url + "/add-update-rss-feed-src",
                        headers: {},
                        contentType: 'application/json',
                        dataType: 'json',
                        data: JSON.stringify(data),
                        success: function (response) {
                            if (response.success) {
                                myToastr(response.message, 'success');
                                setTimeout(function () {
                                    window.location.reload();
                                }, 500);
                            } else {
                                myToastr(response.message, 'error');
                            }
                        }
                    });
                } else {
                    myToastr('Please enter valid URL', 'error');
                }
            } else {
                myToastr('Please enter URL', 'error');
            }
        } else {
            myToastr('Enter name', 'error');
        }
    } else {
        myToastr('Select category', 'error');
    }
}


var blogThumbImage;

function uploadCategoryThumbImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    if (id == 0) {
        var authorimage = 'thumb_image';
    } else {
        var authorimage = 'thumb_image_' + id;
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadCategoryThumbImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                blogThumbImage = data.data;
                                if (blogThumbImage != undefined) {
                                    $('#' + authorimage).val(blogThumbImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


function triggerFileInput(className) {
    $('.' + className).click();
}

var authorImage;

function uploadauthorImage(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var authorimage = 'authorimage' + id;
    } else {
        createBtn = 'createBtn';
        authorimage = 'authorimage';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $("#show_cat_image_add").show();
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                category_image = data.data;
                                if (category_image != undefined) {
                                    $('#' + authorimage).val(category_image);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


function addUpdateAuthor(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    var flag = true;
    e.preventDefault();
    if (data.name == '') {
        flag = false;
        myToastr('Enter name', 'error');
    } else if (data.email == '') {
        flag = false;
        myToastr('Enter email', 'error');
    }

    if (flag) {
        $.ajax({
            type: 'POST',
            url: base_url + "/addUpdateAuthor",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}


var blogThumbImage;

function uploadblogThumbImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    var authorimage = 'thumb_image';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadBlogThumbImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                blogThumbImage = data.data;
                                if (blogThumbImage != undefined) {
                                    $('#' + authorimage).val(blogThumbImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


var BannerImage;

function uploadBannerImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    var authorimage = 'banner_image';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadBannerImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                BannerImage = data.data;
                                if (BannerImage != undefined) {
                                    $('#' + authorimage).val(BannerImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


var productMyltipleImages;

// function uploadMultipleBannerImage(input, previewid, type, id) {
   
//     var createBtn = 'createBtn';
//      const minFileSize = 200 * 1024;
//      let allValid = true;
//     var authorimage = 'banner_image';
//     $('#' + createBtn).prop('disabled', true);
//     $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
//     var form_data = new FormData();
//     // Read selected files
//     var files = document.getElementById('image').files;
//     var totalfiles = document.getElementById('image').files.length;
//     for (var i = 0; i < totalfiles; i++) {
//             if (files[i].size < minFileSize) {
//                 allValid = false;
//                     myToastr(`File ${files[i].name} is too small. Each file must be more than or equal to 200KB.`, 'error');
               
//                 break;
//             }
//         }
//     if (allValid) {
//     for (var index = 0; index < totalfiles; index++) {
//         form_data.append("image[]", document.getElementById('image').files[index]);
//     }
//     $.ajax({
//         url: base_url + '/uploadMultipleBannerImage',
//         data: form_data,
//         processData: false,
//         contentType: false,
//         type: 'POST',
//         dataType: 'json',
//         success: function (response) {
//             var productMyltipleimages_url = '';
//             setTimeout(function () {
//                 if (response.status) {
//                     if (id == 0) {
//                         $("#" + previewid).show();
//                     }
//                     productMyltipleImages = response.data.images;
//                     productMyltipleimages_url = response.data.images_url;
//                     $('#' + createBtn).prop('disabled', false);
//                     if ($('#productId').val()) {
//                         $('#' + createBtn).html('Update');
//                     } else {
//                         $('#' + createBtn).html('Create');
//                     }
//                 }
//                 for (var index = 0; index < productMyltipleimages_url.length; index++) {
//                     var src = productMyltipleimages_url[index];
//                     var cls = 'delete_div_' + index;
//                     $('#preview').append('<div class="col-span-12 sm:col-span-12" style="float:left" id="' + cls + '"  ><div><img src="' + src + '" class="multipleUpload"></div></div>');
//                 }
//             }, 10);
//         }
//     })
//     }
// }


function uploadMultipleBannerImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    const maxFileSize = 200 * 1024; // Set the maximum file size to 200KB
    let allValid = true;
    var authorimage = 'banner_image';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    var form_data = new FormData();
    // Read selected files
    var files = document.getElementById('image').files;
    var totalfiles = document.getElementById('image').files.length;
    for (var i = 0; i < totalfiles; i++) {
        if (files[i].size > maxFileSize) {
            allValid = false;
            myToastr(`File ${files[i].name} is too large. Each file must be less than 200KB.`, 'error');
            break;
        }
    }
    if (allValid) {
        for (var index = 0; index < totalfiles; index++) {
            form_data.append("image[]", document.getElementById('image').files[index]);
        }
        $.ajax({
            url: base_url + '/uploadMultipleBannerImage',
            data: form_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                var productMyltipleimages_url = '';
                setTimeout(function () {
                    if (response.status) {
                        if (id == 0) {
                            $("#" + previewid).show();
                        }
                        productMyltipleImages = response.data.images;
                        productMyltipleimages_url = response.data.images_url;
                        $('#' + createBtn).prop('disabled', false);
                        if ($('#productId').val()) {
                            $('#' + createBtn).html('Update');
                        } else {
                            $('#' + createBtn).html('Create');
                        }
                    }
                    for (var index = 0; index < productMyltipleimages_url.length; index++) {
                        var src = productMyltipleimages_url[index];
                        var cls = 'delete_div_' + index;
                        $('#preview').append('<div class="col-span-12 sm:col-span-12" style="float:left" id="' + cls + '"  ><div><img src="' + src + '" class="multipleUpload"></div></div>');
                    }
                }, 10);
            }
        })
    } else {
        $('#' + createBtn).prop('disabled', false);
        $('#' + createBtn).html('Create'); // or 'Update' based on your condition
    }
}


var audiofile;

function uploaudiofile(input, previewid, type, id) {
    var createBtn = 'createBtn';
    var audio = 'audio_file_upload';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "mp3") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    // $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('audio_file', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadAudioFIle',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                audiofile = data.data.name;
                                var fullpath = data.data.fullpath;
                                if (audiofile != undefined) {
                                    $("#" + previewid).show();
                                    $('#' + audio).val(audiofile);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                    setTimeout(() => {
                                        $('#audiopreview').html(`<audio controls controlsList="nodownload"><source src="` + fullpath + `" type="audio/mp3"></audio>`);
                                    }, 500);
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only mp3 file', 'error');
        }
    }
}

function addUpdateBlog(e, formid, submittype) {
    console.log('--->');
     $('#wordCountMessage').empty();
    var $clickedBtn = null;
    var originalBtnText = '';
    try {
        $clickedBtn = (e && e.currentTarget) ? $(e.currentTarget) : null;
    } catch (err) {
        $clickedBtn = null;
    }
    if (!$clickedBtn || $clickedBtn.length === 0) {
        $clickedBtn = $('.js-blog-update-full').first();
        if (!$clickedBtn || $clickedBtn.length === 0) {
            $clickedBtn = $('.js-blog-update-basic').first();
        }
        if (!$clickedBtn || $clickedBtn.length === 0) {
            $clickedBtn = $('#createBtn');
        }
    }
    if ($clickedBtn && $clickedBtn.length) {
        try {
            originalBtnText = $.trim($clickedBtn.text());
            $clickedBtn.data('original-text', originalBtnText);
        } catch (err) {
            originalBtnText = '';
        }
    }
    var $form = $("#" + formid);
    var data = getFormData($form);
    data.submittype = submittype;

    var hasInlineTranslations = (data.title_en !== undefined || data.description_en !== undefined);
    if (hasInlineTranslations && data.title_en !== undefined) {
        // Always validate/save Blog table in English
        data.title = data.title_en;
    }
    var selected = [];
    // selected = $('#language').val();

    selected = $("#language :selected").map((_, e) => e.value).get();
  
    data.language_code = selected;
   
    if (submittype == 'draft') {
        data.image = productMyltipleImages;
        var desc = CKEDITOR.instances['blogdescription'].getData();
        if (hasInlineTranslations) {
            var editLang = data.edit_lang || $('#edit_lang').val() || 'en';
            var currentTitle = ($('#blogTitle').length ? $('#blogTitle').val() : data.title);
            if (editLang === 'hi') {
                data.title_hi = currentTitle;
                data.description_hi = desc;
                if ($('#title_hi').length) { $('#title_hi').val(currentTitle); }
                if ($('#description_hi').length) { $('#description_hi').val(desc); }
            } else {
                data.title_en = currentTitle;
                data.description_en = desc;
                if ($('#title_en').length) { $('#title_en').val(currentTitle); }
                if ($('#description_en').length) { $('#description_en').val(desc); }
            }
            data.title = data.title_en;
            data.description = data.description_en;
        } else {
            data.description = desc;
        }
   
        $.ajax({
            type: 'POST',
            
            url: base_url + "/addUpdateblogDraft",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response?.data?.error) {
                    var errorDesc = response.data.error.description ? response.data.error.description[0] : '';
                    var errorDescHi = response.data.error.description_hi ? response.data.error.description_hi[0] : '';
                    if (errorDesc || errorDescHi) {
                        $('#wordCountMessage').text(errorDesc || errorDescHi);
                    }
                }
                if (response.status) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        var rc = $('#redirect_query_string').val() || '';
                        window.location.href = base_url + "/blog/side-menu/light" + rc;
                    }, 200);
                } else {
                    myToastr("something went wrong.", 'failure');
                    setTimeout(function () {
                        var rc = $('#redirect_query_string').val() || '';
                        window.location.href = base_url + "/blog/side-menu/light" + rc;
                    }, 200);
                }
            }
        });
    } else {
        var flag = true;
        if (e && typeof e.preventDefault === 'function') {
            e.preventDefault();
        }
        if (data.language == '') {
            flag = false;
            myToastr('Select language', 'error');
        } else if (data.category_id == '') {
            flag = false;
            myToastr('Select category', 'error');
        } else if (data.title == '') {
            flag = false;
            myToastr('Enter title', 'error');
        } else if (data.slug == '') {
            flag = false;
            myToastr('Enter slug', 'error');
        } else {
            data.image = productMyltipleImages;
             
            if (productMyltipleImages != undefined || data.id) {
                $clickedBtn.prop('disabled', true);
                $clickedBtn.text('Wait..');
                var desc = CKEDITOR.instances['blogdescription'].getData();
                if (hasInlineTranslations) {
                    var editLang = data.edit_lang || $('#edit_lang').val() || 'en';
                    var currentTitle = ($('#blogTitle').length ? $('#blogTitle').val() : data.title);
                    if (editLang === 'hi') {
                        data.title_hi = currentTitle;
                        data.description_hi = desc;
                        if ($('#title_hi').length) { $('#title_hi').val(currentTitle); }
                        if ($('#description_hi').length) { $('#description_hi').val(desc); }
                    } else {
                        data.title_en = currentTitle;
                        data.description_en = desc;
                        if ($('#title_en').length) { $('#title_en').val(currentTitle); }
                        if ($('#description_en').length) { $('#description_en').val(desc); }
                    }
                    data.title = data.title_en;
                    data.description = data.description_en;
                } else {
                    data.description = desc;
                }
                $.ajax({
                    type: 'POST',
                    url: base_url + "/addUpdateblog",
                    headers: {},
                     contentType: 'application/json',
                     dataType: 'json',
                     data: JSON.stringify(data),
                     success: function (response) {
                        try {
                            var restoreText = $clickedBtn.data('original-text') || originalBtnText;
                            if (restoreText) {
                                $clickedBtn.text(restoreText);
                            }
                        } catch (err) {}
                        $clickedBtn.prop('disabled', false);
                        if (response?.data?.error) {
                            var errorDesc = response.data.error.description ? response.data.error.description[0] : '';
                            var errorDescHi = response.data.error.description_hi ? response.data.error.description_hi[0] : '';
                            if (errorDesc || errorDescHi) {
                                $('#wordCountMessage').text(errorDesc || errorDescHi);
                            }
                        }
                        console.log(response);
                        // Normalize various possible success shapes
                        var successOK = false;
                        try {
                            successOK = (response && (response.status === true || response.success === true || response == 1 || (response.data && response.data.status === true)));
                        } catch (e) { successOK = false; }

                        if (successOK) {
                            myToastr(response.message || 'Saved', 'success');
                             setTimeout(function () {
                                 var rc = $('#redirect_query_string').val() || '';
                                 window.location.href = base_url + "/blog/side-menu/light" + rc;
                             }, 300);
                         } else {
                            try {
                                var restoreText2 = $clickedBtn.data('original-text') || originalBtnText;
                                if (restoreText2) {
                                    $clickedBtn.text(restoreText2);
                                }
                            } catch (err) {}
                            $clickedBtn.prop('disabled', false);
                            myToastr(response.message || 'Something went wrong', 'error');
                         }
                     }
                 });
             } else {
                 flag = false;
                 myToastr('Please select image', 'error');
                try {
                    var restoreText3 = $clickedBtn.data('original-text') || originalBtnText;
                    if (restoreText3) {
                        $clickedBtn.text(restoreText3);
                    }
                } catch (err) {}
            }
        }
    }
}

function validateSlug(slug) {
    var flag = true;
    console.log(slug);
    // e.preventDefault();
    if (slug == '') {
        flag = false;
        myToastr('input slug', 'error');
    } else {
        var data = {};
        data.slug = slug;
        $.ajax({
            type: 'POST',
            url: base_url + "/validateSlug",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                console.log(response);
                if (response.status) {
                    // myToastr(response.message, 'success');
                } else {
                    // $('#createBtn').text('Update');
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}
var logoUpload;
function uploadLogoImage(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var logoimage = 'app_logo' + id;
    } else {
        createBtn = 'createBtn';
        logoimage = 'app_logo';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $("#show_cat_image_add").show();
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadLogoImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                logoUpload = data.data;
                                if (logoUpload != undefined) {
                                    $('#' + logoimage).val(logoUpload);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}

var profileUpload;

function uploadProfileImage(input, previewid, type, id) {

    if (id != 0) {
        var createBtn = 'createBtn' + id;
        var logoimage = 'photo' + id;
    } else {
        createBtn = 'createBtn';
        logoimage = 'photo';
    }

    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $("#show_cat_image_add").show();
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadProfileImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                profileUpload = data.data;
                                if (profileUpload != undefined) {
                                    $('#' + logoimage).val(profileUpload);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Save');
                                    } else {
                                        $('#' + createBtn).html('Save');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}

var bgUpload;

function uploadBgImage(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var authorimage = 'bg_image' + id;
    } else {
        createBtn = 'createBtn';
        authorimage = 'bg_image';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadBGImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                bgUpload = data.data;
                                if (bgUpload != undefined) {
                                    $('#' + authorimage).val(bgUpload);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Save');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


var siteLogo;

function uploadWebsiteLogo(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var authorimage = 'site_logo' + id;
    } else {
        createBtn = 'createBtn';
        authorimage = 'site_logo';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadLogoImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                siteLogo = data.data;
                                if (siteLogo != undefined) {
                                    $('#' + authorimage).val(siteLogo);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Save');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}

var siteFavicon;

function uploadWebsiteFavicon(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var authorimage = 'site_favicon' + id;
    } else {
        createBtn = 'createBtn';
        authorimage = 'site_favicon';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "ico") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadLogoFavicon',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                siteFavicon = data.data;
                                if (siteFavicon != undefined) {
                                    $('#' + authorimage).val(siteFavicon);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Save');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}

var liveNewsLogo;
function uploadLiveNewsLogo(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var authorimage = 'live_news_logo' + id;
    } else {
        createBtn = 'createBtn';
        authorimage = 'live_news_logo';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadLiveNewsLogo',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                liveNewsLogo = data.data;
                                if (liveNewsLogo != undefined) {
                                    $('#' + authorimage).val(liveNewsLogo);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Save');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}

var EPaperLogo;
function uploadEPaperLogo(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var authorimage = 'e_paper_logo' + id;
    } else {
        createBtn = 'createBtn';
        authorimage = 'e_paper_logo';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $("#image_update_" + id + "").attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadEpaperLogo',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                EPaperLogo = data.data;
                                if (EPaperLogo != undefined) {
                                    $('#' + authorimage).val(EPaperLogo);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Save');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


var BannerImage;

function uploadCmsBannerImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    var authorimage = 'banner_image';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');

    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadCMSBannerImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                BannerImage = data.data;
                                if (BannerImage != undefined) {
                                    $('#' + authorimage).val(BannerImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


function addUpdateCmsPage(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    var flag = true;
    e.preventDefault();
    if (data.title == '') {
        flag = false;
        myToastr('Enter title', 'error');
    }
    var desc = CKEDITOR.instances['blogdescription'].getData();
    data.description = desc;
    if (flag) {
        $.ajax({
            type: 'POST',
            url: base_url + "/addUpdateCMSPage",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}

$(function () {

    $("#tablecontents_ads_images").sortable({
        items: "tr",
        cursor: 'move',
        opacity: 0.6,
        update: function () {
            sendOrderToServer($("#ad_id").val());
        }
    });

    function sendOrderToServer(ad_id) {
        var order = [];
        var token = $('meta[name="csrf-token"]').attr('content');
        $('tr.row1').each(function (index, element) {
            order.push({
                id: $(this).attr('data-id'),
                position: index + 1
            });
        });
        $.ajax({
            type: "POST",
            dataType: "json",
            url: base_url + "/ads-media-sortable",
            data: {
                ad_id: ad_id,
                order: order,
                _token: token
            },
            success: function (response) {
                if (response.status == "success") {
                    console.log(response);
                } else {
                    console.log(response);
                }
            }
        });
    }
});


$(function () {

    $("#tablecontents").sortable({
        items: "tr",
        cursor: 'move',
        opacity: 0.6,
        update: function () {
            sendOrderToServer();
        }
    });

    function sendOrderToServer() {
        var order = [];
        var token = $('meta[name="csrf-token"]').attr('content');
        $('tr.row1').each(function (index, element) {
            order.push({
                id: $(this).attr('data-id'),
                position: index + 1
            });
        });
        $.ajax({
            type: "POST",
            dataType: "json",
            url: base_url + "/category-sortable",
            data: {
                order: order,
                _token: token
            },
            success: function (response) {
                if (response.status == "success") {
                    console.log(response);
                } else {
                    console.log(response);
                }
            }
        });
    }
});

$(function () {

    $("#tablecontentsslider").sortable({
        items: "tr",
        cursor: 'move',
        opacity: 0.6,
        update: function () {
            sendOrderOfSliderPost();
        }
    });

    function sendOrderOfSliderPost() {
        var order = [];
        var token = $('meta[name="csrf-token"]').attr('content');
        $('tr.row1').each(function (index, element) {
            order.push({
                id: $(this).attr('data-id'),
                position: index + 1
            });
        });
        $.ajax({
            type: "POST",
            dataType: "json",
            url: base_url + "/blog-sortable",
            data: {
                order: order,
                _token: token
            },
            success: function (response) {
                if (response.status == "success") {
                    console.log(response);
                } else {
                    console.log(response);
                }
            }
        });
    }
});

function deleteBlogImage(blog_image_id) {
    var data = {};
    data.blog_image_id = blog_image_id;
    $.ajax({
        type: 'GET',
        url: base_url + "/deleteBlogImage/" + blog_image_id,
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        success: function (response) {
            if (response.status) {
                myToastr(response.message, 'success');
                $('#delete_div_' + blog_image_id).remove();
            } else {
                myToastr(response.message, 'error');
            }
        }
    });
}

function add_social(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.name != '') {
        if (data.url != '') {
            if (data.icon != '') {
                if (data.thumb_image != undefined && data.thumb_image != '') {
                    $.ajax({
                        type: 'POST',
                        url: base_url + "/add-update-social",
                        headers: {},
                        contentType: 'application/json',
                        dataType: 'json',
                        data: JSON.stringify(data),
                        success: function (response) {
                            if (response.success) {
                                myToastr(response.message, 'success');
                                setTimeout(function () {
                                    window.location.reload();
                                }, 500);
                            } else {
                                myToastr(response.message, 'error');
                            }
                        }
                    });
                } else {
                    myToastr('Please upload an image', 'error');
                }
            } else {
                myToastr('Enter icon', 'error');
            }
        } else {
            myToastr('Enter url', 'error');
        }
    } else {
        myToastr('Enter name', 'error');
    }
}

var socialImage;
function uploadSocialImage(input, previewid, type, id) {
    if (id) {
        var createBtn = 'createBtn' + id;
        var authorimage = 'social_image_' + id;
    } else {
        createBtn = 'createBtn';
        var authorimage = 'social_image';
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    if (type == "add") {
                        $('#' + previewid).attr('src', e.target.result);
                    } else if (type == "update") {
                        $('#' + previewid).attr('src', e.target.result);
                    }
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadSocialImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                socialImage = data.data;
                                if (socialImage != undefined) {
                                    $('#' + authorimage).val(socialImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}

function add_subadmin(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.name == '') {
        myToastr('Enter name', 'error');
    } else if (data.email == '') {
        myToastr('Enter email', 'error');
    } else {
        $.ajax({
            type: 'POST',
            url: base_url + "/add-update-sub-admin",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.success) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}

var subAdminThumbImage;

function uploadSubadminThumbImage(input, previewid, type, id) {
    var createBtn = 'createBtn';
    if (id == 0) {
        var authorimage = 'image';
    } else {
        var authorimage = 'image_' + id;
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadSubAdminThumbImage',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                subAdminThumbImage = data.data;
                                if (subAdminThumbImage != undefined) {
                                    $('#' + authorimage).val(subAdminThumbImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


function loadHtml(id, dataObj) {
    var html = "";
    var html = dataObj.html;
    $("#" + id).html(html);
}




var blogThumbImage;

function uploadLogo(input, previewid, type, id) {
    var createBtn = 'createBtn';
    if (id == 0) {
        var authorimage = 'thumb_image';
    } else {
        var authorimage = 'thumb_image_' + id;
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/upload-logo',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                blogThumbImage = data.data;
                                if (blogThumbImage != undefined) {
                                    $('#' + authorimage).val(blogThumbImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}


function add_livenews(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.company_name != '') {
        if (blogThumbImage != undefined || data.id) {
            $.ajax({
                type: 'POST',
                url: base_url + "/add-update-live-news",
                headers: {},
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(data),
                success: function (response) {
                    if (response.success) {
                        myToastr(response.message, 'success');
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        myToastr(response.message, 'error');
                    }
                }
            });
        } else {
            myToastr('Please select image', 'error');
        }

    } else {
        myToastr('Enter category', 'error');
    }
}


var blogThumbImage;

function uploadEpaperLogo(input, previewid, type, id) {
    var createBtn = 'createBtn';
    if (id == 0) {
        var authorimage = 'thumb_image';
    } else {
        var authorimage = 'thumb_image_' + id;
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $('#' + previewid).attr('src', e.target.result);
                    var fd = new FormData();
                    fd.append('image', input.files[0]);
                    $.ajax({
                        url: base_url + '/upload-logo-e-paper',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                blogThumbImage = data.data;
                                if (blogThumbImage != undefined) {
                                    $('#' + authorimage).val(blogThumbImage);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only image', 'error');
        }
    }
}



var pdf;

function uploadPdf(input, previewid, type, id) {
    var createBtn = 'createBtn';
    if (id == 0) {
        var authorimage = 'upload_file';
    } else {
        var authorimage = 'upload_file_' + id;
    }
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    if (input.files && input.files[0]) {
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if (extn == "pdf") {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var fd = new FormData();
                    fd.append('upload_file', input.files[0]);
                    $.ajax({
                        url: base_url + '/uploadPdf',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            setTimeout(function () {
                                pdf = data.data;
                                $('#' + previewid).text(pdf);
                                if (pdf != undefined) {
                                    $('#' + authorimage).val(pdf);
                                    $('#' + createBtn).prop('disabled', false);
                                    if (id) {
                                        $('#' + createBtn).html('Update');
                                    } else {
                                        $('#' + createBtn).html('Create');
                                    }
                                }
                            }, 10);
                        }
                    })
                };
            } else {
                myToastr('Something went wrong', 'error');
            }
        } else {
            myToastr('Please select only PDF', 'error');
        }
    }
}


function add_epaper(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.paper_name != '') {
        if (blogThumbImage != undefined || data.id) {
            $.ajax({
                type: 'POST',
                url: base_url + "/add-update-e-paper",
                headers: {},
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(data),
                success: function (response) {
                    if (response.success) {
                        myToastr(response.message, 'success');
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        myToastr(response.message, 'error');
                    }
                }
            });
        } else {
            myToastr('Please select image', 'error');
        }

    } else {
        myToastr('Enter paper name', 'error');
    }
}


function add_product(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.name != '') {
        if (blogThumbImage != undefined || data.id) {
            $.ajax({
                type: 'POST',
                url: base_url + "/add-update-product",
                headers: {},
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(data),
                success: function (response) {
                    if (response.success) {
                        myToastr(response.message, 'success');
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        myToastr(response.message, 'error');
                    }
                }
            });
        } else {
            myToastr('Please select image', 'error');
        }

    } else {
        myToastr('Enter Product name', 'error');
    }
}




function getTranslationValues(id) {
    var data = {};
    data.id = id;

    $('#append').html('');

    $.ajax({
        type: 'POST',
        url: base_url + "/languages/translations/show",
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                if (response.data.data.length > 0) {
                    $('#content-key').text('update key :' + response.data.keyword);
                    for (var c = 0; c < response.data.data.length; c++) {
                        $('#append').append(`
                            <input type="hidden" name="id[]" value=`+ response.data.data[c].id + `
                            <div class="p-5 grid grid-cols-12 mt-5 gap-4 row-gap-3">
                                <div class="col-span-12 sm:col-span-12">
                                    <label>`+ response.data.data[c].language_name + `</label>
                                    <input type="text" class="input w-full border mt-2 flex-1 focus" name="value[]" placeholder="value" value="`+ response.data.data[c].value + `">
                                </div>
                            </div>`
                        );
                    }
                    setTimeout(() => {
                        $('.focus').focus();
                    }, 500);
                }
            } else {
                myToastr(response.message, 'error');
            }
        }
    });
}

function getSources(id, source) {
    var data = {};
    data.category_id = id;
    $('#source').html('<option value="">All Source</option>');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: base_url + "/getFeeds",
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status == true) {
                // var firstNew = '<option value="">All Source</option>';
                for (var c = 0; c < response.data.length; c++) {
                    if (source != 0) {
                        var first = '<option value=' + response.data[c].id + '';
                        if (source == response.data[c].id) {
                            var second = " selected";
                        } else {
                            var second = "";
                        }
                        var third = '>' + response.data[c].rss_name + '</option>';
                        var final = first + '' + second + '' + third;
                        // $('#source').append(`` if(`+source+` == `+response.data[c].id+`)selected >`+response.data[c].rss_name+`</option>`);
                        $('#source').append(final);
                    } else {
                        $('#source').append(`<option value=` + response.data[c].id + `>` + response.data[c].rss_name + `</option>`);
                    }
                }
            } else {
                myToastr(response.message, 'error');
            }
        }
    });
}


function getCategoryTranslation(category_id, language_code) {
    $('#category_name_' + category_id).val('');
    var data = {};
    data.category_id = category_id;
    data.language_code = language_code;
    $.ajax({
        type: 'POST',
        url: base_url + '/get-category-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),

        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#category_name_' + category_id).val(response.data.name);
                }
            }
        }
    })
}

function translateCategory(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.category_id == '') {
        myToastr('something went wrong try to rfresh page !', 'error');
    } else if (data.language_code == '') {
        myToastr('select language', 'error');
    } else if (data.name == '') {
        myToastr('enter name', 'error');
    } else {
        $.ajax({
            type: 'POST',
            url: base_url + "/translate-category",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                 
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}



function getLiveNewsTranslation(live_news_id, language_code) {
    $('#company_name_' + live_news_id).val('');
    $('#youtube_url_' + live_news_id).val('');
    var data = {};
    data.live_news_id = live_news_id;
    data.language_code = language_code;
    $.ajax({
        type: 'POST',
        url: base_url + '/get-live-news-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#company_name_' + live_news_id).val(response.data.company_name);
                    $('#youtube_url_' + live_news_id).val(response.data.url);
                }
            }
        }
    })
}


function translateLiveNews(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.live_news_id == '') {
        myToastr('something went wrong try to rfresh page !', 'error');
    } else if (data.language_code == '') {
        myToastr('select language', 'error');
    } else if (data.company_name == '') {
        myToastr('enter company name', 'error');
    } else if (data.url == '') {
        myToastr('enter youtube url', 'error');
    } else {
        $.ajax({
            type: 'POST',
            url: base_url + "/translate-live-news",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}



function getEpaperTranslation(e_paper_id, language_code) {
    $('#paper_name_' + e_paper_id).val('');
    $('#upload_file_' + e_paper_id + '_translate').val('');
    $('#translate_pdf_name_' + e_paper_id).html('No file selected');

    var data = {};
    data.e_paper_id = e_paper_id;
    data.language_code = language_code;
    $.ajax({
        type: 'POST',
        url: base_url + '/get-e-paper-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#paper_name_' + e_paper_id).val(response.data.paper_name);
                    if (response.data.pdf_exist) {
                        $('#upload_file_' + e_paper_id + '_translate').val(response.data.pdf);
                        $tag = `<a href="` + response.data.pdf_file + `" target="_blank">view</a>`
                        $('#translate_pdf_name_' + e_paper_id).html($tag);
                    }
                }
            }
        }
    })
}

function translateEpaper(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.e_paper_id == '') {
        myToastr('something went wrong try to rfresh page !', 'error');
    } else if (data.language_code == '') {
        myToastr('select language', 'error');
    } else if (data.paper_name == '') {
        myToastr('enter paper name', 'error');
    } else {
        $.ajax({
            type: 'POST',
            url: base_url + "/translate-e-paper",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}



function getCmsTranslation(cms_id, language_code) {
    $('#title').val('');
    CKEDITOR.instances['description'].setData('');
    var data = {};
    data.cms_id = cms_id;
    data.language_code = language_code;
    $.ajax({
        type: 'POST',
        url: base_url + '/get-cms-page-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#title').val(response.data.title);
                    CKEDITOR.instances['description'].setData(response.data.description);
                }
            }
        }
    })
}


function translateCmsPage(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    var flag = true;
    e.preventDefault();
    if (data.title == '') {
        flag = false;
        myToastr('Enter title', 'error');
    }
    var desc = CKEDITOR.instances['description'].getData();
    data.description = desc;
    if (flag) {
        $.ajax({
            type: 'POST',
            url: base_url + "/translate-cms-page",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}


function getBlogTranslation(blog_id, language_code) {
    $('#title').val('');
    $('#blogdescription').val('');
    $('#seo_title').val('');
    $('#seo_keyword').val('');
    $('#seo_description').val('');
    CKEDITOR.instances['blogdescription'].setData('');
    $('#tags').tagsinput('removeAll');
    $('#seo_tag').tagsinput('removeAll');

    var data = {};
    data.blog_id = blog_id;
    data.language_code = language_code;
    $.ajax({
        type: 'POST',
        url: base_url + '/get-blog-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),

        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#title').val(response.data.title);
                    CKEDITOR.instances['blogdescription'].setData(response.data.description);
                    $('#seo_title').val(response.data.seo_title);
                    $('#seo_keyword').val(response.data.seo_keyword);
                    $('#seo_description').val(response.data.seo_description);
                    $('#tags').tagsinput('add', response.data.tags);
                    $('#seo_tag').tagsinput('add', response.data.seo_tag);
                    
                }
            }
        }
    })
}


function translateBlog(e, formid) {

    var $form = $("#" + formid);
    var data = getFormData($form);
    var flag = true;
     $('#wordCountMessage').empty();
    e.preventDefault();
    var desc = CKEDITOR.instances['blogdescription'].getData();
    data.description = desc;
    if (data.title == '') {
        flag = false;
        myToastr('Enter title', 'error');
    } else if (data.description == '') {
        flag = false;
        myToastr('Enter description', 'error');
    }

    if (flag) {
        $.ajax({
            type: 'POST',
            url: base_url + "/translate-blog",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                 
                if (response?.data?.error?.description && response.data.error.description.length > 0) {
                    $('#wordCountMessage').text(response.data.error.description[0]);
                }
                if (response.status) {
                    myToastr(response.message, 'success');
                       setTimeout(function () {
                        window.location.href = response.data.redirect_url;
                    }, 300);
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}



function convertToSlug(Text) {
    var slug = Text
        .toLowerCase()
        .replace(/ /g, '-')
        .replace(/[^\w-]+/g, '')
        ;
    $('#slug').val(slug);
}


function getQuoteTranslation(quote_id, language_code) {
    $('#quote_' + quote_id).val('');
    var data = {};
    data.quote_id = quote_id;
    data.language_code = language_code;
    $.ajax({
        type: 'POST',
        url: base_url + '/get-quote-translation',
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                if (response.data != null) {
                    $('#quote_' + quote_id).val(response.data.quote);

                }
            }
        }
    })
}


function deleteCategory(e, category_id, type) {
    var data = {};
    data.id = category_id;
    var flag = true;
    e.preventDefault();
    if (type == "yes") {
        if ($("#category_id_" + category_id).val() == '') {
            flag = false;
            myToastr('Select Category', 'error');
        }
    }
    if (flag) {
        if (type == "yes") {
            data.category_id = $("#category_id_" + category_id).val();
        }
        $.ajax({
            type: 'POST',
            url: base_url + "/delete-category",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                    window.location.reload();
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}

function checkBlogCategory() {
    myToastr('Please assign category to this blog then you can able to active the blog.', 'error');
}

function uploadMultipleAdsImages(input, previewid, type, id) {
    var createBtn = 'createBtn';
    var authorimage = 'banner_image';
    $('#' + createBtn).prop('disabled', true);
    $('#' + createBtn).html('<i class="fa fa-spinner fa-spin"></i> Loading');
    var form_data = new FormData();
    // Read selected files
    var imagesUrl = $(".images").map(function () { return $(this).val(); }).get();
    var imagesName = $(".images_name").map(function () { return $(this).val(); }).get();
    console.log(imagesUrl);
    var totalfiles = document.getElementById('images').files.length;
    for (var index = 0; index < totalfiles; index++) {
        form_data.append("images[]", document.getElementById('images').files[index]);
    }
    $.ajax({
        url: base_url + '/api/uploads/ads_images',
        data: form_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            var multipleimages_url = "";
            var multipleimages_name = "";
            console.log(response);
            multipleimages_url = response.data.images_url;
            multipleimages_name = response.data.images;
            if (imagesUrl.length > 0) {
                for (var index = 0; index < multipleimages_url.length; index++) {
                    if ($.inArray(multipleimages_url[index], imagesUrl) == -1) {
                        imagesUrl.push(multipleimages_url[index]);
                        imagesName.push(multipleimages_name[index]);
                    }

                }
            } else {
                console.log("Check");
                for (var index = 0; index < multipleimages_url.length; index++) {
                    if ($.inArray(multipleimages_url[index], imagesUrl) == -1) {
                        imagesUrl.push(multipleimages_url[index]);
                        imagesName.push(multipleimages_name[index]);
                    }
                }
            }
            $('#showInputsImages').html('');
            $('#display_images').html('');
            $('#showInputsImagesName').html('');
            for (var index_url = 0; index_url < imagesUrl.length; index_url++) {
                $('#showInputsImages').append('<input type="hidden" class="images" name="images_url" value="' + imagesUrl[index_url] + '">');
                $('#showInputsImagesName').append('<input type="hidden" class="images_name" name="images_name[]" value="' + imagesName[index_url] + '">');
                $('#display_images').append('<div class="col-span-12 xl:col-span-3"><div class="border border-gray-200 dark:border-dark-5 rounded-md p-5"><div class="w-40 h-40 relative image-fit cursor-pointer zoom-in mx-auto"><img class="rounded-md" alt="Midone Tailwind HTML Admin Template" src="' + imagesUrl[index_url] + '"></div><div class="w-40 mx-auto cursor-pointer relative mt-5"><button type="button" class="button w-full bg-theme-1 text-white" onclick="removeImage(' + index_url + ')">Delete Image</button></div></div> </div>');
            }
        }
    })
}

function removeImage(index) {
    var imagesUrl = $(".images").map(function () { return $(this).val(); }).get();
    var imagesName = $(".images_name").map(function () { return $(this).val(); }).get();
    // Remove the element at index 2 using the .splice() method
    imagesUrl.splice(index, 1);
    imagesName.splice(index, 1);

    // The array now contains four elements: "apple", "banana", "grape", and "kiwi"
    console.log(imagesUrl); // Output: ["apple", "banana", "grape", "kiwi"]
    console.log(imagesName); // Output: ["apple", "banana", "grape", "kiwi"]

    // Re-arrange the indexes of the array to start from 0
    imagesUrl = $.grep(imagesUrl, function (n) { return (n); });
    imagesName = $.grep(imagesName, function (n) { return (n); });


    // The array now contains four elements with indexes starting from 0: "apple", "banana", "grape", and "kiwi"
    $('#showInputsImages').html('');
    $('#display_images').html('');
    $('#showInputsImagesName').html('');
    for (var index_url = 0; index_url < imagesUrl.length; index_url++) {
        $('#showInputsImages').append('<input type="hidden" class="images" name="images_url" value="' + imagesUrl[index_url] + '">');
        $('#showInputsImagesName').append('<input type="hidden" class="images_name" name="images_name[]" value="' + imagesName[index_url] + '">');
        $('#display_images').append('<div class="col-span-12 xl:col-span-3"><div class="border border-gray-200 dark:border-dark-5 rounded-md p-5"><div class="w-40 h-40 relative image-fit cursor-pointer zoom-in mx-auto"><img class="rounded-md" alt="Midone Tailwind HTML Admin Template" src="' + imagesUrl[index_url] + '"></div><div class="w-40 mx-auto cursor-pointer relative mt-5"><button type="button" class="button w-full bg-theme-1 text-white" onclick="removeImage(' + index_url + ')">Delete Image</button></div></div> </div>');
    }
}

function validateAdForm(formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    var totalFiles = 0;
    var imagesUrl = $(".images").map(function () { return $(this).val(); }).get();

    if (imagesUrl.length > 0) {
        if (imagesUrl[0] != '') {
            totalFiles = totalFiles + 1;
        }
    }
    if (data.title == '') {
        myToastr('Enter title', 'error');
        return false;
    } else if (data.start_date == '') {
        myToastr('Select start date', 'error');
        return false;
    } else if (data.end_date == '') {
        myToastr('Select end date', 'error');
        return false;
    } else if (data.end_date < data.start_date) {
        myToastr('End date should be greater than start date', 'error');
        return false;
    } else if (data.frequency == '') {
        myToastr('Enter frequency', 'error');
        return false;
    } else if (totalFiles == 0) {
        myToastr('At least one media is mandatory either you have to select 1 image, video or video url.', 'error');
        return false;
    } else {
        return true;
    }

}

function req_product(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    console.log(data);
    e.preventDefault();
    if (data.status != '') {
        $.ajax({
            type: 'POST',
            url: base_url + "/req-update-product",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.success) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });


    } else {
        myToastr('select status', 'error');
    }
}


function deleteAdImage(id) {
    var data = {};
    data.id = id;
    $.ajax({
        type: 'POST',
        url: base_url + "/delete-ad-image",
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status) {
                myToastr(response.message, 'success');
                window.location.reload();
            } else {
                myToastr(response.message, 'error');
            }
        }
    });
}

function add_edit_redirected_url(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.redirected_url == '') {
        myToastr('Enter redirected url', 'error');
    } else {
        $.ajax({
            type: 'POST',
            url: base_url + "/addUpdateRedirectedUrl",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}

(function (cash) {
    "use strict";
    cash('.dark-mode-switcher').on('click', function () {
        let switcher = cash(this).find('.dark-mode-switcher__toggle');
        let themeMode = localStorage.getItem("admin-theme-mode");
        let newThemeMode = themeMode === 'dark' ? 'light' : 'dark';
        
        // Toggle the class based on the new theme mode
        cash(switcher).toggleClass('dark-mode-switcher__toggle--active', newThemeMode === 'dark');
        
        // Update the local storage with the new theme mode
        localStorage.setItem("admin-theme-mode", newThemeMode);
        
        // Apply the new theme mode to the HTML element
        $("html").removeClass('dark light').addClass(newThemeMode);
        
        // Update the content without refreshing the page
        var url = window.location.href;
        url = url.replace(/(dark|light)$/, newThemeMode);
        $.get(url, function (data) {
            $("#content").html(data);
            window.history.pushState(null, null, url);
            location.reload();
        });
    });
})(cash);
