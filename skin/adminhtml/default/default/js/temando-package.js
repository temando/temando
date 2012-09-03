Event.observe(window, "load", function () {
    
    $("product[temando_packaging_mode]").observe("change", function () {
        if($("product[temando_packaging_mode]").value == 1) {
	    $("product-packages").show();
	    add_package(1);
	} else {
	    for(var i = 1; i <= 5; i++)
		remove_package(i);
	    
	    $("product-packages").hide();
	}
    });
});

function add_package(index) {
    var div = $("package_" + index);
    if(div) {
	$('product[temando_package_'+index+'_description]').value = null;
	$('product[temando_package_'+index+'_type]').value = null;
	$('product[temando_package_'+index+'_fragile]').value = 0;
	$('product[temando_package_'+index+'_weight]').value = '';
	$('product[temando_package_'+index+'_length]').value = '';
	$('product[temando_package_'+index+'_width]').value = '';
	$('product[temando_package_'+index+'_height]').value = '';
	
	$('product[temando_package_'+index+'_description]').addClassName('required-entry');
	$('product[temando_package_'+index+'_weight]').addClassName('required-entry validate-number');
	$('product[temando_package_'+index+'_length]').addClassName('required-entry validate-number');
	$('product[temando_package_'+index+'_width]').addClassName('required-entry validate-number');
	$('product[temando_package_'+index+'_height]').addClassName('required-entry validate-number');
	div.show();
	
	//disable remove button on previous package
	index = index -1;
	var btn = $('remove_package['+index+']');
	if(btn) {btn.hide();}
    }
}

function remove_package(index) {
    var div = $("package_" + index);
    if(div) {
	$('product[temando_package_'+index+'_description]').value = null;
	$('product[temando_package_'+index+'_type]').value = null;
	$('product[temando_package_'+index+'_fragile]').value = 0;
	$('product[temando_package_'+index+'_weight]').value = '';
	$('product[temando_package_'+index+'_length]').value = '';
	$('product[temando_package_'+index+'_width]').value = '';
	$('product[temando_package_'+index+'_height]').value = '';
	
	$('product[temando_package_'+index+'_description]').removeClassName('required-entry');
	$('product[temando_package_'+index+'_weight]').removeClassName('required-entry validate-number');
	$('product[temando_package_'+index+'_length]').removeClassName('required-entry validate-number');
	$('product[temando_package_'+index+'_width]').removeClassName('required-entry validate-number');
	$('product[temando_package_'+index+'_height]').removeClassName('required-entry validate-number');
	div.hide();
	
	//enable remove button on previous package
	index = index -1;
	var btn = $('remove_package['+index+']');
	if(btn) {btn.show();}
    }
}

