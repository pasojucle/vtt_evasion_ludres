const { async } = require("regenerator-runtime");
import Routing from 'fos-router';
import { showModal } from './modal';

export class Form {
    formData;
    fields = [];
    dynamicFields = [];
    fieldBlurred;
    alertRemote;
    constructor(form) {
        this.element = form;
        this.submit = document.querySelector('button[type="submit"]');
        this.addFields(form);
        this.validate();
    }
    addFields(form) {
        Array.from(form.elements).forEach((field) => {
            if (field.dataset.constraint !== undefined) {
                this.fields.push(new Field(this, field));
            }
        })
    }
    validate = async() => {
        if (0 < this.fields.length) {
            this.formData = new FormData();
            const lastFieldFilled = this.fields.findLastIndex((field) => !field.isEmpty());
            this.fields.forEach((field, index) => {
                this.addData(field, index <= lastFieldFilled);
            });
            
            await this.fetchData().then(() => {
                this.disabledSubmit();
            });
        }
    }
    addData = (field, filled) => {
        this.formData.append(`validator[${field.id}][constraint]`, field.constraint);
        this.formData.append(`validator[${field.id}][required]`, field.isRequired());
        this.formData.append(`validator[${field.id}][filled]`, Number(filled));
        if(field.multipleFields) {
            const items = this.getFieldsByContraint(field)
            items.forEach((item) => {
                this.formData.append(`validator[${field.id}][value][${item.baseName.shortName}]`, item.getValue());
            });
        } else if(field.extraParam) {
            this.formData.append(`validator[${field.id}][value][${field.baseName.shortName}]`, field.getValue());
            this.formData.append(`validator[${field.id}][value][${field.extraParam.name}]`, field.extraParam.value);
        } else {
            this.formData.append(`validator[${field.id}][value]`, field.getValue());
        }
    }
    fetchData = async () => {
        await fetch(Routing.generate('form_validator'), {
            method: 'POST',
            body : this.formData,
        })
        .then((response) => response.json())
        .then((json)=> {
            this.alertRemote = null;
            json.constraintsValidator.forEach((validator) => {
                let field = this.findById(validator.id);
                if (field && validator.filled) {
                    field.setMessageError(validator.html);
                }
                field.setStatus(validator.status);
                field.refreshDynamicField();
            })
            if (json.alert) {
                this.callErrorRoute(json.alert.id)
            }
        });
    }
    getFieldsByContraint = (field) => {
        return this.fields.filter((item) => field.constraint === item.constraint && field.baseName['baseName'] === item.baseName['baseName'])
    }
    disabledSubmit = () => {
        const warnings = this.fields.filter((field) => field.status === 'ALERT_WARNING' || field.isRequired() && !field.getValue());
        this.submit.disabled = 0 < warnings.length
    }
    callErrorRoute = (id) => {
        const alertField = this.findById(id);
        this.alertRemote = id;
        const params = {};
        this.fields.filter((field) => field.constraint === alertField.constraint).forEach((field) => {
            params[field.baseName.shortName] = field.getValue();
        })
        showModal(encodeURI(Routing.generate(alertField.alertRoute, params)), 'danger');
    }
    findById = (id) => {
        return this.fields.find((field) => field.id === id)
    }
    addEventListenerChange = (targeEl) => {
        const fieldEl = targeEl.querySelector('[data-constraint]');
        const field = (fieldEl) ? this.findById(fieldEl.id) : null;
        if (field) {
            field.getFieldEl().addEventListener('input', field.handleChange);
        }
    }
}

class Field {
    status;
    dynamicFieldId;
    relatedFieldId;
    extraParam;
    constructor(form, field) {
        this.form = form;
        this.id = field.id;
        this.name = field.name;
        this.element = field;
        this.baseName = getBaseName(field.name)
        this.constraint = this.getConstraint(field.dataset.constraint);
        this.multipleFields = field.dataset.multipleFields;
        this.isPhoneNumber = field.classList.contains('phone-number');
        this.alertRoute = field.dataset.alertRoute;
        this.addRelations(field.dataset.modifier);
        this.addExtraParam(field.dataset.extraParamName, field.dataset.extraValue)
        this.addEventListener();
    }
    addEventListener() {
        const element = this.getFieldEl();
        if (element.tagName === 'INPUT' && ['text', 'password'].includes(element.type)) {
            element.addEventListener('input', this.handleChange);
        } else {
            element.addEventListener('change', this.handleChange);
        }
        element.addEventListener('focus', this.handleFocus);
        if (this.isPhoneNumber) {
            this.formatPhoneNumber(element);
            element.addEventListener('keydown', (event) => {this.formatPhoneNumber(event.target)})
        }
    }
    addRelations = (dynamicFieldId) => {
        if (dynamicFieldId) {
            const dynamicField = this.form.element.querySelector(`#${dynamicFieldId} [data-constraint]`)
            this.dynamicFieldId = getBaseName(dynamicField.id)['shortName'];
            dynamicField.relatedFieldId = this.id;
        }
    }
    addExtraParam = (name, value) => {
        if (name) {
            this.extraParam = {'name': name, 'value': value}
        }
    }
    formatPhoneNumber = (target) => {
        if (target.value) {
            let input = target.value.replace(/\s/g, '').match(/.{1,2}/g);
            target.value = input.join(' ');
        }
    }
    getConstraint = (constraint) => {
        if (constraint !== undefined) {
            return constraint
        }
        return '';
    }
    getFieldEl = () => {
        return document.getElementById(this.id)
    }
    getErrorEl = () => {
        let field = this.getFieldEl();
        while (field) {
            if (field.tagName === 'UL' && field.classList.contains('errors')) {
                return field;
            }
            field = field.previousElementSibling
        }
        return;
    }
    getValue = () => {
        return this.getFieldEl().value;
    }
    isEmpty = () => {
        return 0 === this.getFieldEl().value.length;
    }
    isRequired = () => {
        return Number(this.getFieldEl().required);
    }
    handleFocus = (event) => {
        if (event.target.id === this.form.alertRemote) {
            event.target.value = '';
        }
    }
    handleChange = (event) => {
        this.value = event.target.value;
        this.form.validate();
    }
    setMessageError = (html) => {
        const elFromHtml = document.createRange().createContextualFragment(html).firstChild;
        const errorEl = this.getErrorEl();
        if (!errorEl) {
            this.getFieldEl().insertAdjacentElement('beforebegin', elFromHtml);
        } else {
            if (this.multipleFields === true) {
                const items = this.getFieldsByContraint(field.constraint)
                items.forEach((item) => {
                    item.getErrorEl().replaceWith(elFromHtml);
                });
            } else {
                errorEl.replaceWith(elFromHtml);
            }
        }
    }
    setStatus = (status) => {
        this.status = status;
        const fieldEl = this.getFieldEl();
        if (status === 'SUCCESS') {
            fieldEl.parentElement.classList.remove('alert-warning');
            fieldEl.parentElement.classList.add('success'); 
        } else if (status === 'ALERT_WARNING') {
            fieldEl.parentElement.classList.add('alert-warning');
            fieldEl.parentElement.classList.remove('success');
        } else {
            fieldEl.parentElement.classList.remove('alert-warning', 'success');
        }
    }
    refreshDynamicField = () => {
        if (this.dynamicFieldId) {
            const dynamicField = this.form.findById(this.dynamicFieldId);
            if (this.status === 'ALERT_WARNING') {
                dynamicField.getFieldEl().value = null;
                const errorEl = dynamicField.getErrorEl();
                if (errorEl) {
                    errorEl.remove();
                }
            }
        }
    }
}

const getBaseName = (name) => {
    const regexp = new RegExp(/^([0-9A-Za-z\[\]-]+)\[([0-9A-Za-z-]+)\]$/, 'i');
    const match = name.match(regexp);

    return (null !== match) ? {'baseName':  match[1],'shortName' : match[2]} : {'baseName':  name,'shortName' : name} ;
}