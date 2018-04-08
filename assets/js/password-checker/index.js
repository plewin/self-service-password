var ForbiddenCharactersRule = require('./forbidden_characters');
var DiffLoginRule = require('./diff_login');
var NoReuseRule = require('./no_reuse');
var MinLengthRule = require('./min_length');
var MaxLengthRule = require('./max_length');
var MinLowerRule = require('./min_lower');
var MinUpperRule = require('./min_upper');
var MinDigitRule = require('./min_digit');
var MinSpecialRule = require('./min_special');
var MinComplexityRule = require('./min_complexity');
var ZxcvbnMinRule = require('./zxcvbn_min');

function RuleFactory() {
    this.definitions = {
        'policyminlength': MinLengthRule,
        'policymaxlength': MaxLengthRule,
        'policyminlower': MinLowerRule,
        'policyminupper': MinUpperRule,
        'policymindigit': MinDigitRule,
        'policyminspecial': MinSpecialRule,
        'policyforbiddenchars': ForbiddenCharactersRule,
        'policynoreuse': NoReuseRule,
        'policymincomplexity': MinComplexityRule,
        'policydifflogin': DiffLoginRule,
        'policyzxcvbnmin': ZxcvbnMinRule
    }
}

RuleFactory.prototype.create = function (definitions) {
    console.log("creating");
    console.log(definitions);
    var rules = [];
    for(var name in definitions) {
        var ruleClass = this.definitions[name];
        if(ruleClass === undefined) {
            console.error("Rule " + name + " not found");
            continue;
        }
        console.log(ruleClass);
        rules.push(new ruleClass(definitions[name]));
    }
    console.log(rules);
    return rules;
};

var rf = new RuleFactory();

module.exports.factory = rf;