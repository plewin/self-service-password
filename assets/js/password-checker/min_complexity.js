var $ = require('jquery');

function MinComplexityRule(config) {
    this.minComplexity = config.minComplexity;
}

MinComplexityRule.prototype.check = function() {
    var password = $('input#newpassword').val();

    var $rule = $('#rule_policymincomplexity');

    if (password.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    var nbdigits = password.replace(/[^0-9]/g, '').length;
    var nblower = password.replace(/[^a-z]/g, '').length;
    var nbupper = password.replace(/[^A-Z]/g, '').length;
    var nbspecial = password.replace(/[a-zA-Z0-9]/g, '').length;

    var complexity = 0;

    if (nbdigits > 0) {
        complexity++;
    }
    if (nblower > 0) {
        complexity++;
    }
    if (nbupper > 0) {
        complexity++;
    }
    if (nbspecial > 0) {
        complexity++;
    }

    if (complexity < this.minComplexity) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};


module.exports = MinComplexityRule;
