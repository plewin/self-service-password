var $ = require('jquery');

function MinDigitRule(config) {
    this.minDigit = config.minDigit;
}

MinDigitRule.prototype.check = function() {
    var password = $('input#newpassword').val();

    var $rule = $('#rule_policymindigit');

    var onlydigits = password.replace(/[^0-9]/g, '');

    if (password.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    if(onlydigits.length < this.minDigit) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};


module.exports = MinDigitRule;
