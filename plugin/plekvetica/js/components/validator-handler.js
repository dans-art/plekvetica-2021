/**
 * Validator for From
 * 
 */
var plekvalidator = {

    fields: {},
    errors: {},
    error_messages: {},

    /**
     * Constructs the validator
     * - Loads the default error messages
     */
    construct() {
        this.error_messages = this.default_error_messages();
        console.log('validator loaded');
    },

    /**
     * Monitor the fields for changes and validates the fields.
     * Make sure to set the validator fields with add_field.
     */
    monitor_fields() {
        jQuery('input').on('change', (e) => {
            var field_id = e.currentTarget.id;
            var form = jQuery('#' + field_id).closest('form').attr('id');
            var value = e.currentTarget.value;
            //Check after one second delay. This give other functions some time to set the right values.
            setTimeout(() => {
                this.check_monitored_field(field_id, value, form);
            }, 1000);
            return;
        });
    },

    /**
     * Checks if a monitored field is valid or not
     * Displays the error if field is not valid
     * @param {string} field_id 
     * @param {mixed} value 
     * @param {string} form 
     */
    check_monitored_field(field_id, value, form) {
        if (typeof plekvalidator.fields[form] !== "undefined" && typeof plekvalidator.fields[form][field_id] !== "undefined") {
            let field_obj = plekvalidator.fields[form][field_id];
            //Remove the errors for the field
            if (typeof plekvalidator.errors[form] === "object" && typeof plekvalidator.errors[form][field_id] !== "undefined") {
                delete plekvalidator.errors[form][field_id];
            }
            //Validate the field
            if (plekvalidator.validate_field(field_id, field_obj.type, value, form) !== true) {
                plekvalidator.display_errors(form, field_id);
            }
        }
    },

    /**
     * Adds a single field to the validator.
     * 
     * @param {string|array} input_id The IDs of the input field. Accept single id or array with ids
     * @param {string} validator_type Type to validate
     * @param {bool} allow_empty If the value is allowed to be empty
     * @param {string} form The ID of the form. Use this to avoid name conflicts using multiple forms on one page
     * @returns void
     */
    add_field(input_id, validator_type, allow_empty = false, form = 'default') {
        if (typeof input_id === 'object') {
            jQuery.each(input_id, function (index, value) {
                plekvalidator.add_field(value, validator_type, allow_empty, form);
            });
            return;
        }
        if (typeof this.fields[form] === 'undefined') {
            this.fields[form] = {};
        }
        this.fields[form][input_id] = { id: input_id, type: validator_type, allow_empty: allow_empty };
        return;
    },


    /**
     * 
     * @param {string} input_id ID of input field
     * @param {object} values Invalid values
     * @param {string} form The ID of the form. Use this to avoid name conflicts using multiple forms on one page 
     * @returns 
     */
    add_invalid_field_values(input_id, values, form = 'default') {
        if (typeof this.fields[form][input_id] !== 'undefined') {
            this.fields[form][input_id].invalid_value = values;
        } else {
            console.log("Could not add invalid value to field. Field not found: " + input_id);
        }
        return;
    },

    /**
     * Allows to add custom error Messages
     * @param {string} input_id - The ID of the input field
     * @param {string} form - The ID of the input field
     * @param {string} empty - Error Message on empty field
     * @param {string} invalid_type  - Error Message on invalid type
     * @param {string} to_long  - Error Message if string is to long
     * @param {string} to_short  - Error Message if string is to short
     */
    add_error_messages(input_id, form = 'default', empty = null, invalid_type = null, not_a_number = null, to_long = null, to_short = null, not_allowed = null) {
        let vali = plekvalidator.error_messages;
        this.fields[form][input_id].error_messages = {
            'empty': (empty === null) ? vali.empty : empty,
            'invalid_type': (invalid_type === null) ? vali.invalid_type : invalid_type,
            'nan': (not_a_number === null) ? vali.not_a_number : not_a_number,
            'to_long': (to_long === null) ? vali.to_long : to_long,
            'to_short': (to_short === null) ? vali.to_short : to_short,
            'not_allowed': (not_allowed === null) ? vali.not_allowed : not_allowed,

        }
    },

    /**
     * The default errors messages for field errors
     * @returns Object - The default error messages.
     */
    default_error_messages() {
        return {
            'empty': __('Field cannot be empty', 'pleklang'),
            'invalid_type': __('Input is not the correct type', 'pleklang'),
            'nan': __('Input is not a Number', 'pleklang'),
            'to_long': __('Input is to long', 'pleklang'),
            'to_short': __('Input is to short', 'pleklang'),
            'not_allowed': __('The provided value is not allowed, please change', 'pleklang'),
            'nice_try': __('Nice try! No Robots allowed here!', 'pleklang')
        }
    },

    /**
     * 
     * @param {string} input_id ID of the input field
     * @param {string} message Error message
     * @param {string} form The Form to validate. Make sure to use the form parameter in add_field as well
     * @returns 
     */
    add_error(input_id, message, form = 'default') {
        if (typeof this.errors[form] === 'undefined') {
            this.errors[form] = {};
        }
        (typeof this.errors[form][input_id] === 'object') ? this.errors[form][input_id].push(message) : this.errors[form][input_id] = [message];
        return;
    },

    /**
     * Shows the errors in the form or as an toastr message
     * @param {string} form The Form to validate. Make sure to use the form parameter in add_field as well
     * @param {string} field_id If not null, only the errors for the given field will be printed out
     * @returns 
     */
    display_errors(form = 'default', field_id = null) {
        var errors = this.errors[form];
        if (typeof errors !== 'object' || Object.keys(errors).length === 0) {
            return null;
        }
        jQuery.each(errors, function (key, val) {
            if (typeof val !== 'object') {
                return;
            }
            if (field_id !== null && key !== field_id) {
                return;
            }

            jQuery.each(val, function (index, msg) {
                plekerror.display_error(key, msg);
            });

        });
    },

    /**
     * 
     * @param {object} data The Data to validate
     * @param {string} form The Form to validate. Make sure to use the form parameter in add_field as well
     * @returns {bool} True if the fields are valid, false otherwise.
     */
    validate_form_data(data, form = 'default') {
        //Remove all errors
        this.errors = {};
        plekerror.clear_field_errors();

        if (this.fields.length === 0) {
            plekerror.display_error('Validator', __('No fields set', 'pleklang'));
            return false;
        }
        for (var dataset of data.entries()) {
            let val_fields = this.fields[form];
            if (typeof val_fields[dataset[0]] !== 'undefined') {
                var type = val_fields[dataset[0]].type;
                console.log('Check: ' + dataset[0]);
                let field_id = dataset[0];
                let value = dataset[1];

                this.validate_field(field_id, type, value, form);
            } else {
                console.log(dataset[0] + ' is not in validator set');
            }
        }
        if (Object.keys(this.errors).length > 0) {
            plekerror.display_error('Validator', __('Cannot save because of errors. Please check the fields again.', 'pleklang'));
            return this.errors;
        }
        return true;
    },

    /**
     * 
     * @param {string} field_id ID of the field to validate
     * @param {string} type Type to validate
     * @param {mixed} value Value to validate. Objects will be converted to string before validating
     * @param {string} form The Form to validate. Make sure to use the form parameter in add_field as well 
     * @param {string} filter_value If the value should be filtered by the plekevent.get_field_value() function. This is to prevent an infinite loop
     * @returns {bool} True if field is valid, false otherwise
     */
    validate_field(field_id, type, value = '', form = 'default', filter_value = true) {
        if (typeof plekevent === 'object' && filter_value === true) {
            value = plekevent.get_field_value(field_id); //Get the correct field value / filter the value before checking
            return this.validate_field(field_id, type, value, form, false); //Make sure to not filter the value again!
        }

        //Try to convert to array, but only if value is not a number
        if (typeof value !== 'number') {
            try {
                var nv = JSON.parse(value);
                console.log("Validate_field try json");
                console.log(nv);
                if (nv !== null && typeof nv === 'object') {
                    jQuery.each(nv, function (key, val) {
                        plekvalidator.validate_field(field_id, type, val, form, false); //Make sure to not filter the value again!
                    });
                    console.log("skip validate");
                    return; //Make sure to end the function on success
                }
            } catch (error) {
                //Value is not a array, or cannot be converted to a array
                //Do noting if no error
                //console.log(error);
            }
        }

        //check field
        console.log("Validator for: " + field_id);
        console.log(type);
        console.log(value);
        let val_fields = this.fields[form];
        let error_msg = (typeof val_fields[field_id].error_messages !== 'undefined') ? val_fields[field_id].error_messages : this.default_error_messages();
        if (typeof val_fields[field_id].invalid_value === 'object' || typeof val_fields[field_id].invalid_value === 'array') {
            jQuery.each(val_fields[field_id].invalid_value, function (index, val_invalid) {
                if (val_invalid === value) {
                    plekvalidator.add_error(field_id, error_msg.not_allowed, form);
                    return false;
                }
            });
        }
        if (value.length === 0) {
            if (val_fields[field_id].allow_empty === true) {
                return true;
            }
            plekvalidator.add_error(field_id, error_msg.empty, form);
            return false;
        }

        let reg_patern = null;
        switch (type) {
            case 'int':
                reg_patern = new RegExp('^[0-9]+$');
                if (reg_patern.test(value) === false) {
                    plekvalidator.add_error(field_id, error_msg.invalid_type, form);
                    return false;
                }
                break;
            case 'price':
                reg_patern = new RegExp('^[0-9.-]+$');
                if (reg_patern.test(value) === false) {
                    plekvalidator.add_error(field_id, __('Field contains forbidden characters. Only Numbers, dots and dashes are allowed.','pleklang'), form);
                    return false;
                }
                break;

            case 'honeypot':
                if (value.length > 0) {
                    plekvalidator.add_error(field_id, error_msg.nice_try, form);
                    return false;
                }
                break;
            case 'url':
                if (value.indexOf('https://') === -1 && value.indexOf('http://') === -1 && value.indexOf('www.') === -1) {
                    plekvalidator.add_error(field_id, __('Please provide a valid URL. Must start with https:// or www.','pleklang'), form);
                    return false;
                }
                break;

            default:
                break;
        }


    }
}

plekvalidator.construct();