
/*
 * Event listener that updates the hidden (original) radio buttons when the 
 * Temando (custom) radio buttons are changed.
 */
function method_update(temando_control)
{
    if (!temando_control && $$('input[type=radio][name=temando_quotes][checked]').length > 0) {
        temando_control = $$('input[type=radio][name=temando_quotes][checked]')[0];
    }
    
    $$('input[name=shipping_method]').each(function (control) {
        control.checked = false;
    });
    
    if (temando_control) {
        method_control = $(temando_control.id.replace(/temando_quote_/, 's_method_temando_'));
        if (method_control) {
            method_control.checked = true;
        }
    }
}

/*
 * Check the currently selected quote radio button (custom) based on the
 * original radio button that is selected on page load (if any).
 */
function temando_update(method_control)
{
    if (!method_control && $$('input[type=radio][name=shipping_method][checked]').length > 0) {
        method_control = $$('input[type=radio][name=shipping_method][checked]')[0];
    }
    
    $$('input[name=temando_quotes]').each(function (control) {
        control.checked = false;
    });
    
    if (method_control) {
        temando_control = $(method_control.id.replace(/s_method_temando_/, 'temando_quote_'));
        if (temando_control) {
            temando_control.checked = true;
        }
    }
}


/*
 * Updates the visible custom radio buttons to be only those that match the 
 * criteria specified by the checkboxes.
 */
function option_update()
{
    // build class
    classes = '';
    
    checkboxes = $$('#temando_checkboxes input[type=checkbox]');
    checkboxes.each(function (checkbox) {
        if (checkbox.id.indexOf('temando_checkbox_') === 0) {
            classes += '.' + checkbox.id.replace(/temando_checkbox_/, '') + '_' + (checkbox.checked  ? 'Y' : 'N');
        }
    });
    
    // hide all
    $$('input[name=temando_quotes]').each(function (control) {
        control.up('li').hide();
        control.checked = false;
    });
    
    // show those matching the classes
    $$(classes).each(function (control) {
        control.up('li').show();
    });

    // show free shipping
    $$('.temando_free_ship').each(function (control) {
        control.up('li').show();
    });

    method_update();
}

function includingShipping(getShippingCode) {
    if ((typeof(shippingMe) !== 'undefined') && (shippingMe != null) && shippingMe && shippingMe.length) {
        var newPrice = shippingMe[getShippingCode];
        if (!lastPrice) {
            lastPrice = newPrice;
            if (window.quoteBaseGrandTotal != undefined) {
                quoteBaseGrandTotal += newPrice;
            }
        }
        if (newPrice != lastPrice) {
            if (window.quoteBaseGrandTotal != undefined) {
                quoteBaseGrandTotal += (newPrice-lastPrice);
            }
            lastPrice = newPrice;

        }
    }
    if (window.quoteBaseGrandTotal != undefined && window.checkQuoteBaseGrandTotal != undefined) {
        checkQuoteBaseGrandTotal = quoteBaseGrandTotal;
    }
    return false;
}
