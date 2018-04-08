var $ = require('jquery');

var zxcvbn = require('zxcvbn');

function ZxcvbnMinRule(config) {
    this.minStrength = config.minStrength;
}

ZxcvbnMinRule.prototype.check = function() {
    //var oldpassword = $('input#oldpassword').val();
    var newpassword = $('input#newpassword').val();


    var result = zxcvbn(newpassword);

    var $rule = $('#rule_policyzxcvbnmin');

    if(newpassword.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    if(result.score < this.minStrength) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};

module.exports = ZxcvbnMinRule;
