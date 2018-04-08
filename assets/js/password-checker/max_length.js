var $ = require('jquery');

function MaxLengthRule(config) {
    this.maxLength = config.maxLength;
}

MaxLengthRule.prototype.check = function() {
    var password = $('input#newpassword').val();

    var $rule = $('#rule_policymaxlength');

    if (password.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    if (password.length > this.maxLength) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};


module.exports = MaxLengthRule;
