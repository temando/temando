
function createInputBox(id) {
    if ($(id).type != 'text') {
                
        name = $(id).name;
        inputBox = document.createElement("input");
                
        inputBox.addClassName('input-text');
        inputBox.addClassName('required');

        Element.extend($(id), inputBox)
        //Element.replace(id, inputBox);
        
        Element.insert($(id + '_selectbox'), {
            after: inputBox
        });

        inputBox.id = id;
        inputBox.name = name;

    } else {
        
        $(id).show();
        
    }
    
    if ($(id + '_selectbox')) $(id + '_selectbox').hide();
}

function createSelectBox(id, field, data, options) {
    
    if (!$(id + '_selectbox')) {
        selectBox = document.createElement("select");
        selectBox.references = options;
        
        name = $(id).name;

        for (i = 0; i < data.length; i++) {
            var opt = document.createElement('option');
            opt.text = data[i][field];
            opt.value = data[i][field];
            selectBox.options.add(opt);
        }

        Element.extend($(id), selectBox)
        //Element.replace(id, selectBox);
        Element.insert($(id), {
            after: selectBox
        });

        selectBox.id = id + '_selectbox';
        selectBox.name = name + '_selectbox';

    } else {
        
        $(id + '_selectbox').show();
        
    }
    
    if ($(id)) $(id).hide()
}



var CheckoutAutocomplete = function(options){

    this.options = {
        url: '',
        postcode: '',
        city: '',
        state: '',
        country: '',
	pcsDiv: ''
    };
    

    if (!$(options.postcode) || !$(options.city) || !$(options.state)) return null;

    this.options.pcsDiv = options.postcode + '_pcsDiv'
    
    if(options){
        Object.extend(this.options, options);
    }

    Event.observe(window, 'load', this.initialize.bind(this));
    /*
        this.initialize();
    if(CheckoutAutocomplete.isDomLoaded){
        this.initialize();
    }else{
        Event.observe(document, 'dom:loaded', this.initialize.bind(this), false);
    }
*/
};

CheckoutAutocomplete.prototype = {

    addressAutocomplete: null,
    pcsInputbox: null,
    pcsContainer: null,
//    pcsDiv: null,

    initialize: function() {
        
        $(this.options.country).references = this;
        
        Event.observe(this.options.country, 'change', function() {

            var postcode = this.references.options.postcode;
            var city = this.references.options.city;
            var state = this.references.options.state;
            var country = this.references.options.country;

            if ($(country).value == 'AU') {
                $(postcode).up().up().hide();
                $(city).up().up().hide();
                $(state).up().up().hide();

                this.references.pcsContainer.show();

            } else {

                $(postcode).up().up().show();
                $(city).up().up().show();
                $(state).up().up().show();

                this.references.pcsContainer.hide();

            }
        });
        
        this.create_pcsInputbox();

        new Autocomplete(this.pcsInputbox, {
            serviceUrl: this.options.url,
            postcode: this.options.postcode,
            city: this.options.city,
            state: this.options.state,
            pcsDiv: this.options.pcsDiv,
            onSelect: function(value, data, options) {

                $(options.city).value = data[0]['city'];
                $(options.postcode).value = data[0]['postcode'];

                var myChoice = data[0]['region_id'];

                for(var i = 0; i < $(options.state).options.length; i++){
                    var curOpt = $(options.state).options[i];

                    curOpt.selected = (curOpt.value == myChoice)

                }

                fireEvent($(options.postcode), 'change');
            },
            onLoadStart: function(options) {
                $(options.city).value = '';
                $(options.postcode).value = '';
                $(options.state).value = '';
		$(options.pcsDiv).addClassName('active');
		
            },
            onLoadComplete: function(options) {
		$(options.pcsDiv).removeClassName('active');
            },
            onAway: function(options) {
                if (!$(options.city).value) {
                    this.pcsInputbox.value = '';
                }
            }
        })


    },
     
     create_pcsInputbox: function() {
        pcsInputbox = $(document.createElement("input"));

        //pcsInputbox.addClassName('input-text');
        pcsInputbox.addClassName('pcs-field');
        pcsInputbox.addClassName('required-entry');
        
        pcsInputbox.id = this.options.postcode + '_pcs';
        pcsInputbox.name = this.options.postcode + '_pcs';
        
        var div = $(document.createElement('div'));
        div.addClassName('field');
        
        var label = $(document.createElement("label"));
        label.addClassName('required');
        label.innerHTML = 'Suburb / Postcode <em>*</em>';
        
        var divInput = $(document.createElement('div'));
        
        divInput.addClassName('estimate-field');

        divInput.id = this.options.postcode + '_pcsDiv';
        
        divInput.appendChild(pcsInputbox);
        
        div.appendChild(label);
        
        div.appendChild(divInput);

        Element.insert($(this.options.country).up().up(), {
            after: div
        });
        
        
        this.pcsInputbox = pcsInputbox;
        this.pcsContainer = div;
        this.pcsDiv = divInput;
        
        div.hide();
        
        var postcode = this.options.postcode;
        var city = this.options.city;
        var state = this.options.state;
        var country = this.options.country;

         // default data
         var pcs_data = {};
         if ((typeof pcs_data_global != 'undefined') && pcs_data_global[pcsInputbox.id]) {
             pcs_data = pcs_data_global[pcsInputbox.id];
         }

         if ((typeof pcs_data != 'undefined') && pcs_data && pcs_data['city'] && pcs_data['postcode'] && pcs_data['region_id'] && pcs_data['region']) {
            $(city).value = pcs_data['city'];
            $(postcode).value = pcs_data['postcode'];
            var myChoice = pcs_data['region_id'];
            for(var i = 0; i < $(state).options.length; i++){
                var curOpt = $(state).options[i];
                curOpt.selected = (curOpt.value == myChoice)
            }

             pcsInputbox.value = pcs_data['postcode'] + ' ' + pcs_data['city'] + ' ' + pcs_data['region'];
         }

        if ($(country).value == 'AU') {
            $(postcode).up().up().hide();
            $(city).up().up().hide();
            $(state).up().up().hide();

            this.pcsContainer.show();

        } else {

            $(postcode).up().up().show();
            $(city).up().up().show();
            $(state).up().up().show();

            this.pcsContainer.hide();

        }
       
        
        //return pcsInputbox;
    }
}

function fireEvent(element,event){
    if (document.createEventObject){
        // dispatch for IE
        var evt = document.createEventObject();
        return element.fireEvent('on'+event,evt)
    }
    else{
        // dispatch for firefox + others
        var evt = document.createEvent("HTMLEvents");
        evt.initEvent(event, true, true ); // event type,bubbling,cancelable
        return !element.dispatchEvent(evt);
    }
}
