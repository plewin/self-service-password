var $ = require('jquery');

function MinUpperRule(config) {
    this.minUpper = config.minUpper;
}

MinUpperRule.prototype.check = function() {
    var password = $('input#newpassword').val();

    var $rule = $('#rule_policyminupper');

    var onlyupper = password.replace(/[^A-Z]/g, '');

    if(password.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    if(onlyupper.length < this.minUpper) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};


module.exports = MinUpperRule;
