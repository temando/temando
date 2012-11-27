function showHide(value) {
    switch(value) {
	case '1': //static
	case '2':
	    $("rule_actions_static_fieldset").show();
	    $("rule_actions_dynamic_fieldset").hide();
	    $("rule_actions_restrict_fieldset").hide();
	    break;
	case '3': //dynamic
	    $("rule_actions_dynamic_fieldset").show();
	    $("rule_actions_static_fieldset").hide();
	    $("rule_actions_restrict_fieldset").hide();
	    break;
	case '4': //restrict
	    $("rule_actions_restrict_fieldset").show();
	    $("rule_actions_static_fieldset").hide();
	    $("rule_actions_dynamic_fieldset").hide();
	    break;
    }
}

Event.observe(window, "load", function () {
    var ruleType = $("rule_action_rate_type");
    showHide(ruleType.options[ruleType.options.selectedIndex].value);
    
    $("rule_action_rate_type").observe("change", function () {
        showHide(this.options[this.options.selectedIndex].value);
    });
});

