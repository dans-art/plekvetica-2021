/**
 * Validator for From
 * 
 */
var plekvalidator = {

    fields: {},
    errors: {},
    error_messages: {},

    construct() {
        this.error_messages = this.default_error_messages();
    },

    add_field(input_id, validator_type, allow_empty = false) {
        this.fields[input_id] = { id: input_id, type: validator_type, allow_empty: allow_empty };
        return;
    },

    /**
     * Allows to add custom error Messages
     * @param {} input_id - The ID of the input field
     * @param {*} empty - Error Message on empty field
     * @param {*} invalid_type  - Error Message on invalid type
     * @param {*} to_long  - Error Message if string is to long
     * @param {*} to_short  - Error Message if string is to short
     */
    add_error_messages(input_id, empty = null, invalid_type = null, not_a_number = null, to_long = null, to_short = null) {
        let vali = plekvalidator.error_messages;
        this.fields[input_id].error_messages = {
            'empty': (empty === null) ? vali.empty : empty,
            'invalid_type': (invalid_type === null) ? vali.invalid_type : invalid_type,
            'nan': (not_a_number === null) ? vali.not_a_number : not_a_number,
            'to_long': (to_long === null) ? vali.to_long : to_long,
            'to_short': (to_short === null) ? vali.to_short : to_short,

        }
    },

    default_error_messages() {
        return {
            'empty': __('Field cannot be empty', 'pleklang'),
            'invalid_type': __('Input is not the correct type', 'pleklang'),
            'nan': __('Input is not a Number', 'pleklang'),
            'to_long': __('Input is to long', 'pleklang'),
            'to_short': __('Input is to short', 'pleklang')
        }
    },

    add_error(input_id, message) {
        try {
            this.errors[input_id].push(message);
        } catch (error) {
            this.errors[input_id] = [message];
        }
        return;
    },

    display_errors() {
        if (Object.keys(this.errors).length === 0) {
            return null;
        }
        jQuery.each(this.errors, function (key, val) {
            if (typeof val !== 'object') {
                return;
            }

            jQuery.each(val, function (index, msg) {
                plekerror.display_error(key, msg);
            });

        });
    },

    validate_data() {
        if (this.fields.length === 0) {
            plekerror.display_error('Validator', __('No fields set', 'pleklang'));
            return false;
        }

        plekerror.display_error('Validator', 'Nicht validiert!');
        return true;
    },

    validate_form_data(data) {
        //Remove all errors
        this.errors = {};
        plekerror.clear_field_errors();

        if (this.fields.length === 0) {
            plekerror.display_error('Validator', __('No fields set', 'pleklang'));
            return false;
        }

        for (var dataset of data.entries()) {
            if (typeof this.fields[dataset[0]] !== 'undefined') {
                var type = this.fields[dataset[0]].type;
                this.validate_field(dataset[0], type, dataset[1])
            }
        }
        if (Object.keys(this.errors).length > 0) {
            plekerror.display_error('Validator', __('Cannot save because of errors. Please check the fields again.', 'pleklang'));
            return this.errors;
        }
        return true;
    },

    validate_field(field_id, type, value) {
        if (typeof value === "undefined") {
            value = "";
        }
        //Try to convert to array, but only if value is not a number
        if (typeof value !== 'number') {
            try {
                var nv = JSON.parse(value);
                if (typeof nv === 'object') {
                    jQuery.each(nv, function (key, val) {
                        plekvalidator.validate_field(field_id, type, val);
                    });
                    return; //Make sure to end the function on success
                }
            } catch (error) {
                //Do noting if no error
            }
        }

        //check field
        console.log("Validator for: " + field_id);
        console.log(type);
        console.log(value);
        let error_msg = (typeof this.fields[field_id].error_messages !== 'undefined') ? this.fields[field_id].error_messages : this.default_error_messages();
        if (value.length === 0) {
            if (plekvalidator.fields[field_id].allow_empty === true) {
                return true;
            }
            plekvalidator.add_error(field_id, error_msg.empty);
            return false;
        }

        switch (type) {
            case 'int':
                if (typeof value !== 'number') {
                    plekvalidator.add_error(field_id, error_msg.invalid_type);
                    return false;
                }
                break;

            default:
                break;
        }


    }
}

plekvalidator.construct();