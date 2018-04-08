var $ = require('jquery');

function DiffLoginRule() {
}

DiffLoginRule.prototype.check = function() {
    var $login = $('input#login');
    var $newpassword = $('input#newpassword');
    var $rule = $('#rule_policydifflogin');

    var password = $newpassword.val();

    if (password.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    if (password === $login.val()) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};

module.exports = DiffLoginRule;
