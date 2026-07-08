  'use strict';

  cash(function () {
    async function login() {
        // Reset state
        cash('#login-form').find('.input').removeClass('border-theme-6')
        cash('#login-form').find('.login__input-error').html('')
        // Post form
        let email = cash('#input-email').val()
        let password = cash('#input-password').val()
        let rememberMe = cash('#input-remember-me').val()                
        // Loading state
        cash('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>').svgLoader()
        await helper.delay(1500)
        axios.post(`admin-login`, {
            email: email,
            password: password,
            remember_me: rememberMe
        }).then(res => {
            if(res.data.status){
                location.href = base_url +'/dashboard/side-menu/light';
            }else{
				cash('#btn-login').html('Login')
                cash(`#input-email`).addClass('border-theme-6')
                cash(`#error-email`).html(res.data.message)
            }
        }).catch(err => {
            cash('#btn-login').html('Login')
            if (err.response.data.message != 'Wrong email or password.') {
                for (const [key, val] of Object.entries(err.response.data.errors)) {
                    cash(`#input-${key}`).addClass('border-theme-6')
                    cash(`#error-${key}`).html(val)
                }
            } else {
                cash(`#input-password`).addClass('border-theme-6')
                cash(`#error-password`).html(err.response.data.message)
            }
        })
    }

    cash('#login-form').on('keyup', function(e) {
        if (e.keyCode === 13) {
            login()
        }
    })
    
    cash('#btn-login').on('click', function() {
        login()
    })
})