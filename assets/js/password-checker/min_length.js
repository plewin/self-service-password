var $ = require('jquery');

function MinLengthRule(config) {
    this.minLength = config.minLength;
}

MinLengthRule.prototype.check = function() {
    var password = $('input#newpassword').val();

    var $rule = $('#rule_policyminlength');

    if(password.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    if(password.length < this.minLength) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};


module.exports = MinLengthRule;
