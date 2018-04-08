var $ = require('jquery');

function MinLowerRule(config) {
    this.minLower = config.minLower;
}

MinLowerRule.prototype.check = function() {
    var password = $('input#newpassword').val();

    var $rule = $('#rule_policyminlower');

    var onlylower = password.replace(/[^a-z]/g, '');

    if(password.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    if(onlylower.length < this.minLower) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};


module.exports = MinLowerRule;
