/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ.
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @copyright Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик».
 *             Все права защищены.
 * @license   https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @see       https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @see       https://cms-girvas.ru Сайт продукта
 * @author    Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * @support   support@karelian-developer.ru
 */

'use strict';

import {Interactive} from "../../../interactive.class.js";
import {URLParser} from "../../../urlParser.class.js";
import {Utils} from "../../../utils.class.js";

export class PageForm {
  constructor(page, params = {}) {
    this.page = page;
    
    this.buttons = {save: null, delete: null, addElement: null};
    this.elementsCount = 0;
  }

  init() {
    let searchParams = new URLParser();
    let elementForm = document.querySelector('[data-element="main-form"]');

    let locales;
    const interactiveLocaleChoices = new Interactive('choices');
    interactiveLocaleChoices.target.setName('common_locale');

    const interactiveMethodChoices = new Interactive('choices');

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((localeData) => {
      let urlInputElement = document.querySelector('[data-element="input-name"]');
      let titleInputElement = document.querySelector('[data-element="input-title"]');
      let descriptionTextareaElement = document.querySelector('[data-element="input-description"]');

      locales.forEach((locale, localeIndex) => {
        let localeTitle = locale.title;
        let localeIconURL = locale.iconURL;
        let localeName = locale.name;
        let localeISO639_2 = locale.iso639_2;

        let localeIconImageElement = document.createElement('img');
        localeIconImageElement.setAttribute('src', localeIconURL);
        localeIconImageElement.setAttribute('alt', localeTitle);

        let localeLabelElement = document.createElement('span');
        localeLabelElement.innerText = localeTitle;

        let localeTemplate = document.createElement('template');
        localeTemplate.innerHTML += localeIconImageElement.outerHTML;
        localeTemplate.innerHTML += localeLabelElement.outerHTML;

        interactiveLocaleChoices.target.addItem(localeTemplate.innerHTML, localeName);
      });

      locales.forEach((locale, localeIndex) => {
        if (locale.name === window.CMSCore.locales.admin.name) {
          interactiveLocaleChoices.target.setItemSelectedIndex(localeIndex);
        }

        if (locale.name === window.CMSCore.locales.admin.name) {
          descriptionTextareaElement.setAttribute('name', 'form_description_' + locale.iso639_2);
          titleInputElement.setAttribute('name', 'form_title_' + locale.iso639_2);

          if (searchParams.getPathPart(3) != null) {
            let request = new Interactive('request', {
              method: 'GET',
              url: `/handler/form/${searchParams.getPathPart(3)}?locale=${window.CMSCore.locales.admin.name}`
            });
    
            request.target.showingNotification = false;
            
            request.target.send().then((data) => {
              if (data.statusCode == 1 && data.outputData.hasOwnProperty('form')) {
                let formConstrData = data.outputData.form;

                descriptionTextareaElement.value = formConstrData.description;
                titleInputElement.value = formConstrData.title;
              }

            }, (rejectionReason) => {
              this.page.showPopupNotification(rejectionReason, 0);
            });
          }
        }
      });

      interactiveLocaleChoices.assembly();

      let interactiveContainerElement = document.querySelector('[data-element="header-interactive"]');
      interactiveContainerElement.append(interactiveLocaleChoices.target.element);

      let interactiveMethodContainerElement = document.querySelector('[data-element="choice"][data-choice="method"]');
      if (interactiveMethodContainerElement != null) {
        let formConstrData;

        fetch(`/handler/form/${searchParams.getPathPart(3)}?locale=${window.CMSCore.locales.admin.name}`, {method: 'GET'}).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          formConstrData = data.outputData.form;

          return fetch(`/handler/forms/methods?locale=${window.CMSCore.locales.admin.name}`, {method: 'GET'});
        }, (rejectionReason) => {
          this.page.showPopupNotification(rejectionReason, 0);
        }).then((response) => {
          return response.ok ? response.json() : Promise.reject(response);
        }).then((data) => {
          if (data.outputData.hasOwnProperty('methods')) {
            if (data.outputData.methods.length > 0) {
              data.outputData.methods.forEach((method, methodIndex) => {
                let methodTitle = method.name.toUpperCase();
                interactiveMethodChoices.target.addItem(methodTitle, method.id);
                
                if (formConstrData !== undefined) {
                  if (formConstrData.methodID == method.id) {
                    interactiveMethodChoices.target.setItemSelectedIndex(methodIndex);
                  }
                }
              });

              interactiveMethodChoices.target.setName('form_method_id');
              interactiveMethodChoices.assembly();
            }
          }
          
          interactiveMethodContainerElement.append(interactiveMethodChoices.target.element);
        });
      }

      urlInputElement.addEventListener('input', (event) => {
        /** @var {String} */
        let inputValue = event.target.value;

        /** @var {Utils} */
        let utils = new Utils();
        /** @var {UString} */
        let uString = utils.createString(inputValue);
        uString.source = uString.translitToEN(true);
        uString.source = uString.source.toLowerCase();
        uString.source = uString.source.replace(/[^a-z0-9\-]/, '');

        event.target.value = uString.source;
      });

      this.buttons.addElement = new Interactive('button');
      this.buttons.save = new Interactive('button');
      this.buttons.delete = new Interactive('button');

      this.buttons.addElement.target.setLabel(localeData.BUTTON_NEW_ELEMENT_LABEL);
      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      
      this.buttons.addElement.target.setStyle('default');
      this.buttons.save.target.setStyle('green');
      this.buttons.delete.target.setStyle('red');

      this.buttons.addElement.target.setCallback((event) => {
        event.preventDefault();

        const formElementsU = document.querySelectorAll('[data-element="form-element"]');
        const anchorElement = formElementsU[formElementsU.length - 1] ?? null;
        this.addElement(localeData, anchorElement);
      });

      // Получаем все установленные языковые пакеты
      fetch('/handler/form/' + searchParams.getPathPart(3) + '?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'}).then((response) => {
        return response.ok ? response.json() : Promise.reject(response);
      }).then((data) => {
        const elements = data.outputData.form.elements;
        elements.forEach((element, elementIndex) => {
          let elementTexts = element['texts'][window.CMSCore.locales.admin.name];
          elementTexts = elementTexts !== undefined ? elementTexts : [];

          const elementTitle = elementTexts.title;
          const elementDescription = elementTexts.description;
          const elementPlaceholder = elementTexts.placeholder;
          
          const formElementsU = document.querySelectorAll('[data-element="form-element"]');
          const anchorElement = formElementsU[formElementsU.length - 1] ?? null;
          this.addElement(localeData, anchorElement, {
            index: elementIndex,
            type: element.type,
            required: element.required,
            title: elementTitle,
            description: elementDescription,
            placeholder: elementPlaceholder,
            name: element.name,
            sequenceNumber: element.sequenceNumber
          });
        });
      });

      const interactiveChoicesSelectElement = interactiveContainerElement.querySelector('select');
      interactiveChoicesSelectElement.addEventListener('change', (event) => {
        const formTitleInputElement = document.querySelector('[data-element="input-title"]');
        const formDescriptionTextareaElement = document.querySelector('[data-element="input-description"]');
        const formElementTitleInputElements = document.querySelectorAll('[name="form_element_title[]"]');
        const formElementDescriptionInputElements = document.querySelectorAll('[name="form_element_description[]"]');
        const formElementPlaceholderInputElements = document.querySelectorAll('[name="form_element_placeholder[]"]');
        
        locales.forEach((locale, localeIndex) => {
          if (locale.name === event.target.value) {
            formTitleInputElement.setAttribute('name', 'form_title_' + locale.iso639_2);
            formDescriptionTextareaElement.setAttribute('name', 'form_description_' + locale.iso639_2);

            if (searchParams.getPathPart(3) != null) {
              const request = new Interactive('request', {
                method: 'GET',
                url: '/handler/form/' + searchParams.getPathPart(3) + '?locale=' + locale.name + '&localeMessage=' + window.CMSCore.locales.admin.name
              });
      
              request.target.showingNotification = false;
      
              request.target.send().then((data) => {
                if (data.statusCode === 1 && data.outputData.hasOwnProperty('form')) {
                  formDescriptionTextareaElement.value = data.outputData.form.description;
                  formTitleInputElement.value = data.outputData.form.title;

                  formElementTitleInputElements.forEach((element, elementIndex) => {
                    const elementData = data.outputData.form.elements[elementIndex];
                    element.value = this.getLocalizedFormElementText(elementData, 'title', locale.name);
                  });

                  formElementDescriptionInputElements.forEach((element, elementIndex) => {
                    const elementData = data.outputData.form.elements[elementIndex];
                    element.value = this.getLocalizedFormElementText(elementData, 'description', locale.name);
                  });

                  formElementPlaceholderInputElements.forEach((element, elementIndex) => {
                    const elementData = data.outputData.form.elements[elementIndex];
                    element.value = this.getLocalizedFormElementText(elementData, 'placeholder', locale.name);
                  });
                }
              });
            }
          }
        });
      });

      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();

        elementForm = document.querySelector('[data-element="main-form"]');

        let form = new Interactive('form');
        form.target.replaceElement(elementForm);
        
        if (form.target.checkRequiredFields()) {
          let formData = new FormData(elementForm);
          formData.append('common_locale', interactiveLocaleChoices.target.getValue());

          let fetchLink = searchParams.getPathPart(3) === null
            ? '/handler/form?localeMessage=' + window.CMSCore.locales.admin.name
            : '/handler/form/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name;
          let fetchMethod = searchParams.getPathPart(3) === null ? 'PUT' : 'PATCH';

          let request = new Interactive('request', {
            method: fetchMethod,
            url: fetchLink
          });
  
          request.target.data = formData;
  
          request.target.send().then((data) => {
            if (data.statusCode == 1 && searchParams.getPathPart(3) == null) {
              let formConstrData = data.outputData.form;
              window.location.href = '/admin/form/' + formConstrData.id;
            }
          });
        } else {
          this.page.showPopupNotification(localeData.FORM_REQUIRED_FIELDS_IS_EMPTY, 0);
        }
      });

      this.buttons.delete.target.setCallback((event) => {
        event.preventDefault();

        let interactiveModal = new Interactive('modal', {
          title: localeData.MODAL_ENTRIES_CATEGORY_DELETE_TITLE,
          content: localeData.MODAL_ENTRIES_CATEGORY_DELETE_DESCRIPTION
        });
        
        interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
          let formData = new FormData();
          formData.append('form_id', searchParams.getPathPart(3));

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/form/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode == 1) {
              window.location.href = '/admin/forms';
            }
          });
        });

        interactiveModal.target.addButton(localeData.BUTTON_CANCEL_LABEL, () => {
          interactiveModal.target.close();
        });

        interactiveModal.assembly();
        document.body.appendChild(interactiveModal.target.element);
        interactiveModal.target.show();
      });

      this.buttons.addElement.assembly();
      this.buttons.save.assembly();
      this.buttons.delete.assembly();

      if (searchParams.getPathPart(3) === null) {
        this.buttons.delete.target.element.style.display = 'none';
        this.buttons.save.target.element.style.display = 'flex';
      } else {
        this.buttons.delete.target.element.style.display = 'flex';
        this.buttons.save.target.element.style.display = 'flex';
      }

      const interactiveContainer = document.querySelector('[data-element="panel"]');
      interactiveContainer.append(this.buttons.addElement.target.element);
      interactiveContainer.append(this.buttons.delete.target.element);
      interactiveContainer.append(this.buttons.save.target.element);
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }

  getLocalizedFormElementText(elementData, field, preferredLocale) {
    const texts = elementData?.texts;
    if (!texts) return '';
    
    if (texts[preferredLocale]?.[field]) {
      return texts[preferredLocale][field];
    }

    return '';
  }

  createRowElement(title, dataElement = null) {
    const rowElement = document.createElement('div');
    const cellInfoElement = document.createElement('div');
    const cellDataElement = document.createElement('div');
    const cellTitleElement = document.createElement('div');

    rowElement.classList.add('row');
    rowElement.classList.add('grid-table__row');
    rowElement.setAttribute('data-element', 'form-element');
    cellInfoElement.classList.add('cell');
    cellInfoElement.classList.add('grid-table__cell');
    cellInfoElement.classList.add('grid-table__cell_text');
    cellDataElement.classList.add('cell');
    cellDataElement.classList.add('grid-table__cell');
    cellDataElement.classList.add('grid-table__cell_data');
    cellTitleElement.classList.add('grid-table__cell-title');

    if (title !== null) {
      cellTitleElement.innerText = title;
      cellInfoElement.append(cellTitleElement);
      rowElement.append(cellInfoElement);
    }
    
    if (dataElement !== null) {
      cellDataElement.appendChild(dataElement);
      rowElement.appendChild(cellDataElement);
    }

    return rowElement;
  }

  addElement(localeData, anchorElement, data = {}) {
    const rowsElement = document.createElement('div');
    rowsElement.classList.add('grid-table__rows');

    const cellHeaderElement = document.createElement('div');
    const formElementInputTitle = document.createElement('input');
    const formElementInputName = document.createElement('input');
    const formElementInputDescription = document.createElement('textarea');
    const formElementInputPlaceholder = document.createElement('input');
    const formElementInputSequenceNumber = document.createElement('input');
    
    cellHeaderElement.classList.add('row');
    cellHeaderElement.classList.add('grid-table__row');
    cellHeaderElement.classList.add('grid-table__row_header');
    cellHeaderElement.innerText = data.title !== undefined
      ? `${localeData.PAGE_FORM_ELEMENT}: ${data.title}`
      : localeData.PAGE_FORM_NEW_ELEMENT;

    formElementInputTitle.setAttribute('type', 'text');
    formElementInputTitle.setAttribute('name', 'form_element_title[]');
    formElementInputTitle.setAttribute('placeholder', localeData.PAGE_FORM_ELEMENT_TITLE_PLACEHOLDER);
    formElementInputTitle.setAttribute('required', 'required');
    formElementInputPlaceholder.setAttribute('type', 'text');
    formElementInputPlaceholder.setAttribute('name', 'form_element_placeholder[]');
    formElementInputPlaceholder.setAttribute('placeholder', localeData.PAGE_FORM_ELEMENT_PLACEHOLDER_PLACEHOLDER);
    formElementInputName.setAttribute('pattern', '[a-zA-Z0-9_]+');
    formElementInputName.setAttribute('type', 'text');
    formElementInputName.setAttribute('name', 'form_element_name[]');
    formElementInputName.setAttribute('placeholder', 'my_field');
    formElementInputName.setAttribute('required', 'required');
    formElementInputDescription.setAttribute('name', 'form_element_description[]');
    formElementInputDescription.setAttribute('placeholder', localeData.PAGE_FORM_ELEMENT_DESCRIPTION_PLACEHOLDER);
    formElementInputSequenceNumber.setAttribute('type', 'number');
    formElementInputSequenceNumber.setAttribute('name', 'form_element_sequence_number[]');
    formElementInputSequenceNumber.setAttribute('placeholder', 4);
    formElementInputSequenceNumber.setAttribute('required', 'required');

    formElementInputTitle.classList.add('form__input');
    formElementInputTitle.classList.add('form__input_text');
    formElementInputPlaceholder.classList.add('form__input');
    formElementInputPlaceholder.classList.add('form__input_text');
    formElementInputName.classList.add('form__input');
    formElementInputName.classList.add('form__input_text');
    formElementInputDescription.classList.add('form__textarea');
    formElementInputSequenceNumber.classList.add('form__input');
    formElementInputSequenceNumber.classList.add('form__input_number');

    formElementInputName.addEventListener('change', (event) => {
      const selectOptionsElements = document.querySelectorAll('[data-select]');
      selectOptionsElements.forEach(element => {
        if (element.getAttribute('data-element') === 'select-option-label') {
          element.setAttribute('name', 'form_element_select_' + formElementInputName.value + '_option_label');
        }

        if (element.getAttribute('data-element') === 'select-option-value') {
          element.setAttribute('name', 'form_element_select_' + formElementInputName.value + '_option_value');
        }
      });
    });

    /* Выпадающий список с типами полей */

    const interactiveChoicesTypeField = new Interactive('choices');
    interactiveChoicesTypeField.target.addItem('Text', 'text');
    interactiveChoicesTypeField.target.addItem('Number', 'number');
    interactiveChoicesTypeField.target.addItem('Date', 'date');
    interactiveChoicesTypeField.target.addItem('Textarea', 'textarea');
    interactiveChoicesTypeField.target.addItem('EMail', 'email');
    interactiveChoicesTypeField.target.addItem('Phone', 'tel');
    interactiveChoicesTypeField.target.addItem('Checkbox', 'checkbox');
    interactiveChoicesTypeField.target.addItem('Select', 'select');
    interactiveChoicesTypeField.target.addItem('Button Submit', 'submit');
    interactiveChoicesTypeField.target.addItem('Button Reset', 'reset');
    interactiveChoicesTypeField.target.setName('form_element_type[]');
    
    if (data.type !== undefined) {
      switch (data.type) {
        case 'text': interactiveChoicesTypeField.target.setItemSelectedIndex(0); break;
        case 'number': interactiveChoicesTypeField.target.setItemSelectedIndex(1); break;
        case 'date': interactiveChoicesTypeField.target.setItemSelectedIndex(2); break;
        case 'textarea': interactiveChoicesTypeField.target.setItemSelectedIndex(3); break;
        case 'email': interactiveChoicesTypeField.target.setItemSelectedIndex(4); break;
        case 'tel': interactiveChoicesTypeField.target.setItemSelectedIndex(5); break;
        case 'checkbox': interactiveChoicesTypeField.target.setItemSelectedIndex(6); break;
        case 'select': interactiveChoicesTypeField.target.setItemSelectedIndex(7); break;
        case 'submit': interactiveChoicesTypeField.target.setItemSelectedIndex(8); break;
        case 'reset': interactiveChoicesTypeField.target.setItemSelectedIndex(9); break;
        default: interactiveChoicesTypeField.target.setItemSelectedIndex(0);
      }
    }

    interactiveChoicesTypeField.assembly();

    const cellElementsForType = this.createRowElement(
      localeData.PAGE_FORM_ELEMENT_TYPE_TITLE,
      interactiveChoicesTypeField.target.element
    );

    const checkboxID = Array(10).fill(0).map(() => Math.floor(Math.random() * 10)).join('');

    const checkboxContainerElement = document.createElement('div');
    checkboxContainerElement.classList.add('form__checkbox-container');
    checkboxContainerElement.classList.add('checkbox-container');

    const checkboxInputElement = document.createElement('input');
    checkboxInputElement.classList.add('checkbox-container__input');
    checkboxInputElement.classList.add('form__input');
    checkboxInputElement.classList.add('form__input_checkbox');
    checkboxInputElement.setAttribute('id', 'I' + checkboxID);
    checkboxInputElement.setAttribute('name', 'form_element_required[' + this.elementsCount + ']');
    checkboxInputElement.setAttribute('type', 'checkbox');
    checkboxInputElement.setAttribute('value', 'required');

    if (data.required) {
      checkboxInputElement.setAttribute('checked', 'checked');
    }
    
    const checkboxLabelElement = document.createElement('label');
    checkboxLabelElement.classList.add('checkbox-container__label');
    checkboxLabelElement.classList.add('form__label');
    checkboxLabelElement.setAttribute('for', 'I' + checkboxID);

    checkboxContainerElement.appendChild(checkboxInputElement);
    checkboxContainerElement.appendChild(checkboxLabelElement);

    const cellElementsForRequired = this.createRowElement(
      localeData.PAGE_FORM_ELEMENT_REQUIRED_TITLE,
      checkboxContainerElement
    );

    const cellElementsForTitle = this.createRowElement(
      localeData.PAGE_FORM_ELEMENT_TITLE_TITLE,
      formElementInputTitle
    );

    const cellElementsForPlaceholder = this.createRowElement(
      localeData.PAGE_FORM_ELEMENT_PLACEHOLDER_TITLE,
      formElementInputPlaceholder
    );

    const cellElementsForName = this.createRowElement(
      localeData.PAGE_FORM_ELEMENT_TECHNICAL_NAME_TITLE,
      formElementInputName
    );

    const cellElementsForDescription = this.createRowElement(
      localeData.PAGE_FORM_ELEMENT_DESCRIPTION_TITLE,
      formElementInputDescription
    );

    const cellElementsForSequenceNumber = this.createRowElement(
      localeData.PAGE_FORM_ELEMENT_SEQUENCE_NUMBER_TITLE,
      formElementInputSequenceNumber
    );

    const buttonRemoveField = new Interactive('button');
    buttonRemoveField.target.setLabel(localeData.BUTTON_DELETE_LABEL);
    buttonRemoveField.target.setStyle('red');

    buttonRemoveField.target.setCallback((event) => {
      event.preventDefault();
      this.elementsCount--;
      
      rowsElement.remove();

      buttonRemoveField.target.element.parentElement.previousElementSibling.remove();
      buttonRemoveField.target.element.parentElement.remove();
    });

    buttonRemoveField.assembly();

    const cellElementsForEvents = this.createRowElement(
      null, buttonRemoveField.target.element
    );
    
    cellElementsForEvents.classList.add('grid-table__cell_panel');

    interactiveChoicesTypeField.target.elementSelect.addEventListener('change', (event) => {
      if (interactiveChoicesTypeField.target.itemSelectedIndex === 7) {
        let rowOption = this.createRowSelectOption(localeData, formElementInputName, 0);
        rowsElement.children.item(rowsElement.children.length - 1).before(rowOption);

        const buttonAddOptionField = new Interactive('button');
        buttonAddOptionField.target.setLabel(localeData.BUTTON_NEW_OPTION_LABEL);
        buttonAddOptionField.target.setStyle('default');

        buttonAddOptionField.target.setCallback((event) => {
          event.preventDefault();

          let rowOptions = document.querySelectorAll('[data-element="select-option-label"][data-select="' + formElementInputName.value + '"]');
          let rowOption = this.createRowSelectOption(localeData, formElementInputName, rowOptions.length);
          rowsElement.children.item(rowsElement.children.length - 1).before(rowOption);
        });

        buttonAddOptionField.assembly();

        buttonRemoveField.target.element.before(buttonAddOptionField.target.element);
      } else {
        let rowOptions = document.querySelectorAll('[data-element="select-option-label"][data-select="' + formElementInputName.value + '"]');
        if (rowOptions.length > 0) {
          rowOptions.forEach(rowOption => {
            rowOption.parentElement.parentElement.parentElement.remove();
          });
        }
      }
    });

    rowsElement.append(cellHeaderElement);
    rowsElement.append(cellElementsForType);
    rowsElement.append(cellElementsForTitle);
    rowsElement.append(cellElementsForName);
    rowsElement.append(cellElementsForDescription);
    rowsElement.append(cellElementsForPlaceholder);
    rowsElement.append(cellElementsForSequenceNumber);
    rowsElement.append(cellElementsForRequired);
    rowsElement.append(cellElementsForEvents);

    const formElementsSectionHeader = document.querySelector('[data-element="form-elements-section-header"]');
    if (formElementsSectionHeader !== null) {
      if (anchorElement === null) {
        formElementsSectionHeader.after(rowsElement);
      } else {
        anchorElement.after(rowsElement);
      }

      formElementInputTitle.value = data.title !== undefined
        ? data.title
        : '';

      formElementInputName.value = data.name !== undefined
        ? data.name
        : '';

      formElementInputPlaceholder.value = data.placeholder !== undefined
        ? data.placeholder
        : '';

      formElementInputDescription.value = data.description !== undefined
        ? data.description
        : '';

      formElementInputSequenceNumber.value = data.sequenceNumber !== undefined
        ? data.sequenceNumber
        : 0;
    }
    
    this.elementsCount++;
  }

  createRowSelectOption(localeData, inputName, index) {
    const inputGroupElement = document.createElement('div');
    inputGroupElement.classList.add('grid-table__input-group');
    const inputOptionLabelElement = document.createElement('input');
    inputOptionLabelElement.classList.add('form__input');
    inputOptionLabelElement.classList.add('form__input_text');
    inputOptionLabelElement.setAttribute('type', 'text');
    inputOptionLabelElement.setAttribute('name', 'form_element_select_' + inputName.value + '_option_label[' + index + ']');
    inputOptionLabelElement.setAttribute('data-element', 'select-option-label');
    inputOptionLabelElement.setAttribute('data-select', inputName.value);
    inputOptionLabelElement.setAttribute('placeholder', localeData.PAGE_FORM_ELEMENT_OPTION_LABEL_PLACEHOLDER);
    
    const inputOptionValueElement = document.createElement('input');
    inputOptionValueElement.classList.add('form__input');
    inputOptionValueElement.classList.add('form__input_text');
    inputOptionValueElement.setAttribute('type', 'text');
    inputOptionValueElement.setAttribute('name', 'form_element_select_' + inputName.value + '_option_value[' + index + ']');
    inputOptionValueElement.setAttribute('data-element', 'select-option-value');
    inputOptionValueElement.setAttribute('data-select', inputName.value);
    inputOptionValueElement.setAttribute('placeholder', localeData.PAGE_FORM_ELEMENT_OPTION_VALUE_PLACEHOLDER);

    inputGroupElement.append(inputOptionLabelElement);
    inputGroupElement.append(inputOptionValueElement);

    return this.createRowElement(
      localeData.PAGE_FORM_ELEMENT_OPTION_TITLE + ' #' + (index + 1),
      inputGroupElement
    );
  }
}