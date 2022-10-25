let lastField;
$(document).ready(function(){
    lastField = getLastField();
    console.log('lastFied', lastField);
    validateAll();
    $(document).on('blur', '[data-constraint]', function(){
        validateAll(this.name);
    });

    $(document).on('change', '[data-constraint="app-BirthDate"], .select2entity', function(){
        validateAll(this.name);
    });
});

function getLastField() {
    let notEmptyFields = [];
    $('[data-constraint]').each(function(index, item) {
        if (this.value !== '' && !$(this).parent().hasClass('hidden')) {
            notEmptyFields.push(index);
        }
    });
    return (0 < notEmptyFields.length) ? notEmptyFields.pop() : -1;
}

function validateAll(name = null) {
    $('[data-constraint]').each(function(index, item) {
        if (name && lastField < index) {
            lastField = index;
        }
        if (lastField < index) {
            return false;
        }
        if (!$(this).parent().hasClass('hidden')) {
            validate(this);
        }
        if (this.name === name ) {
            return false;
        }
    });
}

function validate(element){
    let data = {};
    const input = $(element);
    data['constraint'] = input.data('constraint');
    data['required'] = input.attr('required');
    data['current'] = getName(input.attr('name'));
    if(input.data('multiple-fields')) {
        let values = {};
        $('[data-constraint="'+input.data('constraint')+'"]').each(function(){
            values[getName(this.name)] = this.value;
        });
        data['values'] = values;
    } else {
        data['value'] = input.val();
    }
    $.ajax({
        url : Routing.generate('form_validator'),
        type: 'POST',
        data : data,
        success: function(response) {
            const target = input.prev('ul.errors');
            if (target.length === 0) {
                $(response.html).insertBefore(input);
            } else {
                if (response.multiple === true) {
                    $('[data-constraint="'+input.data('constraint')+'"]').prev('ul.errors').replaceWith($(response.html));
                } else {
                    target.replaceWith($(response.html));
                }
            }
            if (!input.is(':disabled')) {
                if (response.status === 'SUCCESS') {
                    if (response.multiple === true) {
                        $('[data-constraint="'+input.data('constraint')+'"]').parent().removeClass('alert-warning').addClass('success');
                    } else {
                        input.parent().removeClass('alert-warning').addClass('success');
                    }
                } else if (response.status === 'ALERT_WARNING') {
                    input.parent().removeClass('success').addClass('alert-warning');
                } else {
                    input.parent().removeClass('success').removeClass('alert-warning');
                }
            }

        }
    });
}

function getName(name) {
    const regexp = new RegExp(/^[0-9A-Za-z\[\]-]+\[([0-9A-Za-z-]+)\]$/, 'i');
    const match = name.match(regexp);

    return (null !== match) ? match[1] : name;
}