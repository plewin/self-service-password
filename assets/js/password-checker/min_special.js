var $ = require('jquery');

function MinSpecialRule(config) {
    this.specialChars = config.specialChars;
    this.minSpecial = config.minSpecial;
}

MinSpecialRule.prototype.check = function() {
    var password = $('input#newpassword').val();

    var $rule = $('#rule_policyminspecial');

    var notspecials = password.replace(new RegExp('[' + this.specialChars + ']','g'), '');

    if ( password.length === 0 ) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    if ( (password.length - notspecials.length) < this.minSpecial) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};


module.exports = MinSpecialRule;
