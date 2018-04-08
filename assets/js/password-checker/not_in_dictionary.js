var $ = require('jquery');

var debounce = require('lodash/debounce');

function NotInDictionaryRule(config) {
    this.apiUrl = config.apiUrl;

    this.checkInServer = debounce(function() {
        var $rule = $('#rule_policynotindictionary');

        var newpassword = $('input#newpassword').val();

        if(newpassword.length === 0) {
            $rule.removeClass('rule_ok').removeClass('rule_ko');
            return;
        }

        $.post(config.apiUrl, {'password': newpassword})
            .done(function(result) {
                if(result['found']) {
                    $rule.removeClass('rule_ok').addClass('rule_ko');
                    $('input#newpassword').addClass('is-invalid');
                } else {
                    $rule.addClass('rule_ok').removeClass('rule_ko');
                }
            })
            .fail(function() {
                $rule.removeClass('rule_ok').addClass('rule_ko');
            })
        ;
    }, 1000);
}


NotInDictionaryRule.prototype.check = function() {
    var newpassword = $('input#newpassword').val();

    var $rule = $('#rule_policynotindictionary');

    if(newpassword.length === 0) {
        $rule.removeClass('rule_ok').removeClass('rule_ko');
        return false;
    }

    $rule.removeClass('rule_ok').removeClass('rule_ko');

    this.checkInServer();

    return true;
};

module.exports = NotInDictionaryRule;
