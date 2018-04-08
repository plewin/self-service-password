var $ = require('jquery');

function NoReuseRule() {
}

NoReuseRule.prototype.check = function() {
    var oldpassword = $('input#oldpassword').val();
    var newpassword = $('input#newpassword').val();

    var $rule = $('#rule_policynoreuse');

    if(newpassword.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    if(oldpassword === newpassword) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};

module.exports = NoReuseRule;
