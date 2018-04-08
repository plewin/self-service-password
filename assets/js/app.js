var $ = require('jquery');
require('hideshowpassword');
require('bootstrap');

var checkers = require('./password-checker');

$(document).ready(function(){
    // Menu links popovers
    $('[data-toggle="menu-popover"]').popover({
        trigger: 'hover',
        placement: 'bottom',
        container: 'body' // Allows the popover to be larger than the menu button
    });

    // toggle password visibility
    $('.password + .input-group-append').on('click', function() {
        // toggle our classes for the eye icon
        $(this).find('i.fa').toggleClass('fa-eye-slash').toggleClass('fa-eye');
        // activate the hideShowPassword plugin
        $(this).prev('.password').togglePassword();
    });

    var newPasswordEl = $('input#newpassword');

    console.log(rulesDefinitions);
    var rules = checkers.factory.create(rulesDefinitions);

    newPasswordEl.on('input', function(e){
        if(newPasswordEl.val() === '') {
            newPasswordEl.addClass('is-invalid');
        }

        var isValid = true;
        for(var rule in rules) {
            if(rules[rule].check() !== true) {
                isValid = false;
            }
        }

        if(isValid || newPasswordEl.val() === '') {
            newPasswordEl.removeClass('is-invalid');
        } else {
            newPasswordEl.addClass('is-invalid');
        }
    });
});