'use strict';

function myToastr(msg, type) {
    var html = ''
    $('.ajax-msg').html('');
    if (type == 'error') {
        html = `<div class="alert alert-danger">
                  <strong>Error!</strong> <span id="showmsg">`+msg+`</span>
                </div>`;
    } else if (type == 'success') {
       html =  `<div class="alert alert-success">
                  <strong>Success!</strong> <span id="showmsg">`+msg+`</span>
                </div>`;
    }
    $('.ajax-msg').removeClass('hide');
    $('.ajax-msg').html(html);
    setTimeout(()=>{
        $('.ajax-msg').html('');
        $('.ajax-msg').addClass('hide');
    },3000);

}

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

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

function user_login(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.email == '') {
        myToastr('Enter email', 'error');
    }else if(validateEmail(data.email)){
        myToastr('Please enter valid email address', 'error');
    }else if(data.password == ''){
        myToastr('Enter password', 'error');
    }else{
        $("#login_button").html("Please wait..");
        $("#login_button").attr('disabled',true);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url: base_url + "/user-login",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status==true) {
                    setTimeout(function () {
                        location.href = base_url+'/edit-profile';
                    }, 1000);
                } else {
                    $("#login_button").removeAttr('disabled');
                    $("#login_button").html("Log In");
                    myToastr(response.message, 'error');
                }
            }
        });
    } 
}

function user_signup(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.email == '') {
        myToastr('Enter email', 'error');
    }else if(validateEmail(data.email)){
        myToastr('Please enter valid email address', 'error');
    }else if(data.password == ''){
        myToastr('Enter password', 'error');
    }else if(data.password.length < 6){
        myToastr('Password at least of 6 digits', 'error');
    }else{
        $("#user_signup_btn").html("Please wait..");
        $("#user_signup_btn").attr('disabled',true);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url: base_url + "/user-signup",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status==true) {
                    setTimeout(function () {
                        location.href = base_url+'/edit-profile';
                    }, 1000);
                } else {
                    $("#user_signup_btn").html("Signup");
                    $("#user_signup_btn").removeAttr('disabled');
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}

function edit_profile(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.name == '') {
        myToastr('Enter name', 'error');
    }else if(validateEmail(data.email)){
        myToastr('Please enter valid email address', 'error');
    }else if (data.email == '') {
        myToastr('Enter email', 'error');
    }else{
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url: base_url + "/edit-profile",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status==true) {
                    myToastr(response.message, 'success');
                } else {
                    myToastr(response.message, 'error');
                }
            }
        });
    }
}

function bookmark(id) {
    var data = {};
    data.blog_id = id;
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: base_url + "/bookmark",
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status==true) {
                myToastr(response.message, 'success');
                if(response.data.data=='first'){
                    $("#notmarked").hide();
                    $("#marked").show();
                    $("#marked").removeClass('hide');
                }else{
                    $("#marked").hide();
                    $("#notmarked").removeClass('hide');
                    $("#notmarked").show();
                }
                
            } else {
                myToastr(response.message, 'error');
            }
        }
    });
}

function uploadUserProfilePicture(input) {
    if(input.files && input.files[0]){
        var imgPath = input.files[0].name;
        var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
        if(extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
            if(typeof (FileReader) != "undefined"){
                $("#preloader").show();
                var reader = new FileReader();
                reader.readAsDataURL(input.files[0]);
                reader.onload = function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var userId = $("#id").val();
                    var fd = new FormData();
                    fd.append('photo', input.files[0]);
                    fd.append('id', userId);
                    $('#whole_page_loader').fadeIn();
                    $.ajax({
                        url:base_url+'/upload-profile-image',
                        data:fd,
                        processData:false,
                        contentType:false,
                        type:'POST',
                        dataType:'json',
                        success:function(data){
                            myToastr(data.message, 'success');
                            $("#show_image").attr("src",data.data.url);
                            $('#whole_page_loader').fadeOut();
                        }
                    })
                };
            }else{
                myToastr("Please select valid image", 'error');
            }
        }else{
            myToastr('Something went wrong .', 'error');
        }
    }        
}

function onFileChanged() {
    $('.file-uploads').trigger('click');
}

setTimeout(function(){
    $(".file-uploads").on('change', function(){
        uploadUserProfilePicture(this);
    });
},2000);

function forget_password(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.email == '') {
        myToastr('Enter email', 'error');
    }else{
        $("#forget_password_btn").html("Please wait..");
        $("#forget_password_btn").attr('disabled',true);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url: base_url + "/forget-password",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status==true) {
                    myToastr(response.message, 'success');
                    $("#email_value").val(response.data.email);
                    $("#otp_check").val(response.data.otp);
                    $("#forget").hide();
                    $("#reset").removeClass('hide');
                } else {
                    $("#forget_password_btn").html("Forgot Password");
                    $("#forget_password_btn").removeAttr('disabled');
                    myToastr(response.message, 'error');
                }
            }
        });
    } 
}

function reset_password(e, formid) {
    var $form = $("#" + formid);
    var data = getFormData($form);
    e.preventDefault();
    if (data.otp == '') {
        myToastr('Enter otp', 'error');
    }else if (data.otp != data.otp_check) {
        myToastr('Please enter valid otp', 'error');
    }else if (data.password == '') {
        myToastr('Enter password', 'error');
    }else if (data.password.length < 6) {
        myToastr('Password should be of atleast 6 digits', 'error');
    }else if (data.c_password == '') {
        myToastr('Enter confirm password', 'error');
    }else if (data.password != data.c_password) {
        myToastr('password and confirm password should be same', 'error');
    }else{
        $("#reset_password_btn").html("Please wait..");
        $("#reset_password_btn").attr('disabled',true);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url: base_url + "/reset-password",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.status==true) {
                    myToastr(response.message, 'success');
                    setTimeout(function () {
                        location.href = base_url+'/user-login';
                    }, 1000);
                } else {
                    $("#reset_password_btn").html("Forgot Password");
                    $("#reset_password_btn").removeAttr('disabled');
                    myToastr(response.message, 'error');
                }
            }
        });
    } 
}

$("#user_language").change(function(){
    var data = {};
    data.lang_code = $('#user_language').val();
  $.ajax({
        type: 'POST',
        url: base_url + "/set-language",
        headers: {},
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(data),
        success: function (response) {
            location.reload();
        }
    });
});

var flag = true;
$(window).on('scroll',function() {
    clearTimeout($.data(this, 'scrollTimer'));
    $.data(this, 'scrollTimer', setTimeout(function() {
        var flag = true;
        if(document.getElementById("load_when_come_here")){
            var hT = $('#load_when_come_here').offset().top,
            hH = $('#load_when_come_here').outerHeight(),
            wH = $(window).height(),
            wS = $(this).scrollTop();
            if (wS > (hT+hH-wH) && flag){
                if (!$('div.myspinner').length) {
                    $('#load-data').append('<div class="myspinner text-center"><svg class="spinner" width="25px" height="25px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg"><circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle></svg></div>');
                }
                flag = false;
                var data = {};
                data.loaded_ids = $('#loaded_ids').val();
                data.active_category = $('#active_category').val();
                $.ajax({
                    type: 'POST',
                    url: base_url + "/load-blog",
                    headers: {},
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify(data),
                    success: function (response) {
                        if(response.status) 
                        {  
                            $('.myspinner').remove();
                            $('#load_when_come_here').remove();
                            setTimeout(()=>{
                                $('#load-data').append(response.data.output);
                                $('#loaded_ids').val(response.data.loaded_ids);
                            },300);
                        }
                    },
                    error : function(res){
                        $('.myspinner').remove();
                    }
                });
            }
        }
    }, 200));
});

function deletebtnModal(id,flag = true) {
    if (flag) {
        $('#'+id).attr('style','display:block');
    }else{
        $('#'+id).removeAttr('style');
    }
}

function togglePlay(id,blog_id) {
    var attr = $('#'+id).attr('src');
    if (typeof attr !== 'undefined' && attr !== false) {
        var myAudio = document.getElementById(id);
        if(myAudio.paused==true){
            $('#'+id+'_btn').html('<i class="fa fa-volume-off"></i>');
        }else{
            $('#'+id+'_btn').html('<i class="fa fa-volume-up"></i>');
        }
        return myAudio.paused ? myAudio.play() : myAudio.pause();
    }else{
        $('#'+id+'_btn').html('Loading..');
        var data = {};
        data.blog_id = blog_id;
        $.ajax({
            type: 'POST',
            url: base_url + "/get-blog-speech",
            headers: {},
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(data),
            success: function (response) {
                $('#'+id).attr('src',response.data.voice);             
                var myAudio = document.getElementById(id);
                if(myAudio.paused==true){
                    $('#'+id+'_btn').html('<i class="fa fa-volume-off"></i>');
                }else{
                    $('#'+id+'_btn').html('<i class="fa fa-volume-up"></i>');
                }
                console.log(myAudio.paused);
                return myAudio.paused ? myAudio.play() : myAudio.pause();
            }
        });
    }
};

function getSources(id) {
    // var data = {};
    // data.category_id = id;
    // $.ajaxSetup({
    //     headers: {
    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //     }
    // });
    // $.ajax({
    //     type: 'POST',
    //     url: base_url + "/bookmark",
    //     headers: {},
    //     contentType: 'application/json',
    //     dataType: 'json',
    //     data: JSON.stringify(data),
    //     success: function (response) {
    //         if (response.status==true) {
    //             myToastr(response.message, 'success');
    //             if(response.data.data=='first'){
    //                 $("#notmarked").hide();
    //                 $("#marked").removeClass('hide');
    //             }else{
    //                 $("#marked").hide();
    //                 $("#notmarked").removeClass('hide');
    //                 $("#notmarked").show();
    //             }
                
    //         } else {
    //             myToastr(response.message, 'error');
    //         }
    //     }
    // });
}