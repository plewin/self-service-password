var $ = require('jquery');

function ForbiddenCharactersRule(config) {
    this.forbiddenCharacters = config.forbiddenCharacters;
}

ForbiddenCharactersRule.prototype.check = function() {
    var password = $('input#newpassword').val();

    var $rule = $('#rule_policyforbiddenchars');

    if (password.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    if (contains_forbidden_characters(this.forbiddenCharacters, password)) {
        $rule.removeClass('rule_ok').addClass('rule_ko');
        return false;
    }

    $rule.addClass('rule_ok').removeClass('rule_ko');
    return true;
};

function contains_forbidden_characters(forbiddenCharacters, value) {
    for (var i = 0; i < forbiddenCharacters.length; i++) {
        var forbiddenChar = forbiddenCharacters.charAt(i);

        if (value.indexOf(forbiddenChar) > -1) {
            return true;
        }
    }

    return false;
}

module.exports = ForbiddenCharactersRule;
