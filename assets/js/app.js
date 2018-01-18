var $ = require('jquery');
require('hideshowpassword');
require('bootstrap-sass');

$(document).ready(function(){
    // Menu links popovers
    $('[data-toggle="menu-popover"]').popover({
        trigger: 'hover',
        placement: 'bottom',
        container: 'body' // Allows the popover to be larger than the menu button
    });

    // toggle password visibility
    $('.password + .glyphicon').on('click', function() {
        // toggle our classes for the eye icon
        $(this).toggleClass('glyphicon-eye-close').toggleClass('glyphicon-eye-open');
        // activate the hideShowPassword plugin
        $(this).prev('.password').togglePassword();
    });
});