import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["submit", 'field'];

    connect() {
        this.formData = null, 
        this.dynamicFields = [];
        this.alertRemote = null;
        this.timeout = null;
        this.fields = [];
        this.addFields();
        this.addEventListeners();
        this.validate();
    }

    addFields() {
        this.fieldTargets.forEach((field) => {
            if (field.dataset.constraint !== undefined) {
                this.fields.push(new Field(this, field));
            }
        })
    }

    addEventListeners() {
        ['input', 'change', 'focusin'].forEach(eventType => {
            this.element.addEventListener(eventType, (event) => {
                const field = this.findById(event.target.id);
                if (!field) return;

                this.handleFieldEvent(field, event);
            });
        });
    }

    handleFieldEvent(field, event) {
        const el = event.target;
        if (event.type === 'input' && field.isPhoneNumber) {
            field.formatPhoneNumber(event.target);
        }

        if (event.type === 'focusin' && el.id === this.alertRemote) {
            el.value = '';
        }

        const isText = el.tagName === 'INPUT' && ['text', 'password'].includes(el.type);
        if (event.type === 'input' && isText) {
            field.handleChange(event);
        } else if (event.type === 'change' && !isText) {
            field.handleChange(event);
        }
    }

    executeValidate = async() => {
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
    validate = async() => {
        if (0 < this.fields.length) {
            clearTimeout(this.timeout);
            const self = this;
            this.timeout = setTimeout(() => {
                self.executeValidate();
            }, 700);
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
                field.refreshDynamicFields();
            })
            if (json.alert) {
                this.alertRemote = json.alert.id;
                this.callErrorRoute(json.alert)
            }
        });
    }
    getFieldsByContraint = (field) => {
        return this.fields.filter((item) => field.constraint === item.constraint && field.baseName['baseName'] === item.baseName['baseName'])
    }
    disabledSubmit = () => {
        const warnings = this.fields.filter((field) => field.status === 'ALERT_WARNING' || field.isRequired() && !field.getValue());
        this.submitTarget.disabled = 0 < warnings.length
    }
    callErrorRoute = (alert) => {
        this.dispatch("openFromUrl", { 
            prefix: "modal",
            detail: {url: alert.dialogRoute}
        })
    }
    findById = (id) => {
        return this.fields.find((field) => field.id === id)
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
        this.dynamicFieldIds = [];
        this.addRelations(field.dataset.modifier);
        this.addExtraParam(field.dataset.extraParamName, field.dataset.extraValue)
        const element = this.getFieldEl();
        if (this.isPhoneNumber) {
            this.formatPhoneNumber(element);
        }
    }
    
    addRelations = (dynamicFieldId) => {
        if (dynamicFieldId) {
            const dynamicFields = this.form.element.querySelectorAll(`#${dynamicFieldId} [data-constraint]`)
            dynamicFields.forEach((dynamicField) => {
                this.dynamicFieldIds.push(getBaseName(dynamicField.id)['shortName']);
            })
        }
    }
    addExtraParam = (name, value) => {
        if (name) {
            this.extraParam = {'name': name, 'value': value}
        }
    }
    formatPhoneNumber = (target) => {
        console.log('phone -------', this.baseName);
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
    refreshDynamicFields = () => {
        if (0 < this.dynamicFieldIds.length) {
            this.dynamicFieldIds.forEach((dynamicFieldId) => {
                const dynamicField = this.form.findById(dynamicFieldId);
                if (this.status === 'ALERT_WARNING') {
                    dynamicField.getFieldEl().value = null;
                    const errorEl = dynamicField.getErrorEl();
                    if (errorEl) {
                        errorEl.remove();
                    }
                }
            })

        }
    }
}

const getBaseName = (name) => {
    const regexp = new RegExp(/^([0-9A-Za-z\[\]-]+)\[([0-9A-Za-z-]+)\]$/, 'i');
    const match = name.match(regexp);

    return (null !== match) ? {'baseName':  match[1],'shortName' : match[2]} : {'baseName':  name,'shortName' : name} ;
}