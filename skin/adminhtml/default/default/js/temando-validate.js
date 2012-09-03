Validation.addAllThese([
    ['validate-range-single', 'Please use only numbers (0-9) or colon sign to specify range. No spaces or other characters are allowed.', function(v) {
	return Validation.get('IsEmpty').test(v) || /^[0-9:]+$/.test(v)
    }], 
    ['validate-range-single-decimal', 'Please use only numbers (0-9) or colon sign to specify range. No spaces or other characters are allowed.', function(v) {
	return Validation.get('IsEmpty').test(v) || /^[0-9:\.]+$/.test(v)
    }], 
    ['validate-range-multi', 'Please use only numbers (0-9), colon sign to specify range and comma to separate ranges. No spaces or other characters are allowed.', function(v) {
	return Validation.get('IsEmpty').test(v) || /^[0-9:,]+$/.test(v)
    }], 
    ['validate-range-multi-decimal', 'Please use only numbers (0-9), colon sign to specify range and comma to separate ranges. No spaces or other characters are allowed.', function(v) {
	return Validation.get('IsEmpty').test(v) || /^[0-9:,\.]+$/.test(v)
    }], 
]);
