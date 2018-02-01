var $ = require('jquery');
require('hideshowpassword');
require('bootstrap');

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
});