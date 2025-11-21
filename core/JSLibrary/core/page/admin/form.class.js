/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from "../../../interactive.class.js";
import {URLParser} from "../../../urlParser.class.js";
import {Utils} from "../../../utils.class.js";

export class PageForm {
  constructor(page, params = {}) {
    this.page = page;
    
    this.buttons = {save: null, delete: null, addElement: null};
  }

  init() {
    let searchParams = new URLParser();
    let elementForm = document.querySelector('[data-element="main-form"]');

    let locales;
    const interactiveLocaleChoices = new Interactive('choices');
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

      let tableFormElementsButtonContainer = document.querySelector('[data-element="button-add-element"]');

      this.buttons.addElement = new Interactive('button');
      this.buttons.addElement.target.setLabel(localeData.BUTTON_NEW_ELEMENT_LABEL);
      this.buttons.addElement.target.setCallback((event) => {
        event.preventDefault();

        this.addElement(localeData, tableFormElementsButtonContainer);
      });
      this.buttons.addElement.assembly();

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
          
          this.addElement(localeData, tableFormElementsButtonContainer, {
            type: element.type,
            title: elementTitle,
            description: elementDescription,
            placeholder: elementPlaceholder,
            name: element.name,
            sequenceNumber: element.sequenceNumber
          });
        });
      });

      tableFormElementsButtonContainer.append(this.buttons.addElement.target.element);
      
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

      this.buttons.save = new Interactive('button');
      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();

        elementForm = document.querySelector('[data-element="main-form"]');

        let form = new Interactive('form');
        form.target.replaceElement(elementForm);
        
        if (form.target.checkRequiredFields()) {
          let formData = new FormData(elementForm);
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
      this.buttons.save.assembly();

      this.buttons.delete = new Interactive('button');
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
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
      this.buttons.delete.assembly();

      if (searchParams.getPathPart(3) === null) {
        this.buttons.delete.target.element.style.display = 'none';
        this.buttons.save.target.element.style.display = 'flex';
      } else {
        this.buttons.delete.target.element.style.display = 'flex';
        this.buttons.save.target.element.style.display = 'flex';
      }

      let interactiveContainer = document.querySelector('[data-element="panel"]');
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

  createCellFormElementElements(title, dataElement = null) {
    const cellTextElement = document.createElement('div');
    const cellTextTitleElement = document.createElement('div');
    const cellDataElement = document.createElement('div');

    cellTextElement.classList.add('cell');
    cellTextElement.classList.add('grid-table__cell');
    cellTextElement.classList.add('grid-table__cell_text');

    cellDataElement.classList.add('cell');
    cellDataElement.classList.add('grid-table__cell');
    cellDataElement.classList.add('grid-table__cell_data');

    cellTextElement.setAttribute('data-element', 'form-element-part-element');
    cellDataElement.setAttribute('data-element', 'form-element-part-element');
    
    cellTextTitleElement.classList.add('cell__title');
    cellTextTitleElement.innerText = title;
    
    cellTextElement.appendChild(cellTextTitleElement);

    if (dataElement !== null) {
      cellDataElement.appendChild(dataElement);
    }

    return [
      cellTextElement,
      cellDataElement
    ];
  }

  addElement(localeData, container, data = {}) {
    const cellHeaderElement = document.createElement('div');
    const formElementInputTitle = document.createElement('input');
    const formElementInputName = document.createElement('input');
    const formElementInputDescription = document.createElement('textarea');
    const formElementInputPlaceholder = document.createElement('input');
    const formElementInputSequenceNumber = document.createElement('input');
    
    cellHeaderElement.classList.add('cell');
    cellHeaderElement.classList.add('grid-table__cell');
    cellHeaderElement.classList.add('grid-table__cell_header');
    cellHeaderElement.innerText = data.title !== undefined
      ? `${localeData.PAGE_FORM_ELEMENT}: ${data.title}`
      : localeData.PAGE_FORM_NEW_ELEMENT;

    cellHeaderElement.setAttribute('data-element', 'form-element-part-element');

    formElementInputTitle.setAttribute('type', 'text');
    formElementInputTitle.setAttribute('name', 'form_element_title[]');
    formElementInputTitle.setAttribute('placeholder', localeData.PAGE_FORM_ELEMENT_TITLE_PLACEHOLDER);
    formElementInputTitle.setAttribute('required', 'required');
    formElementInputPlaceholder.setAttribute('type', 'text');
    formElementInputPlaceholder.setAttribute('name', 'form_element_placeholder[]');
    formElementInputPlaceholder.setAttribute('placeholder', localeData.PAGE_FORM_ELEMENT_PLACEHOLDER_PLACEHOLDER);
    formElementInputName.setAttribute('pattern', '[a-z0-9_]+');
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

    const cellElementsForType = this.createCellFormElementElements(
      localeData.PAGE_FORM_ELEMENT_TYPE_TITLE
    );

    /* Выпадающий список с типами полей */

    const interactiveChoicesTypeField = new Interactive('choices');
    interactiveChoicesTypeField.target.addItem('Text', 'text');
    interactiveChoicesTypeField.target.addItem('Number', 'number');
    interactiveChoicesTypeField.target.addItem('Date', 'date');
    interactiveChoicesTypeField.target.addItem('Textarea', 'textarea');
    interactiveChoicesTypeField.target.addItem('EMail', 'email');
    interactiveChoicesTypeField.target.addItem('Phone', 'tel');
    interactiveChoicesTypeField.target.addItem('Checkbox', 'checkbox');
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
        case 'submit': interactiveChoicesTypeField.target.setItemSelectedIndex(7); break;
        case 'reset': interactiveChoicesTypeField.target.setItemSelectedIndex(8); break;
        default: interactiveChoicesTypeField.target.setItemSelectedIndex(0);
      }
    }

    interactiveChoicesTypeField.assembly();

    cellElementsForType[1].append(interactiveChoicesTypeField.target.element);

    const cellElementsForTitle = this.createCellFormElementElements(
      localeData.PAGE_FORM_ELEMENT_TITLE_TITLE,
      formElementInputTitle
    );

    const cellElementsForPlaceholder = this.createCellFormElementElements(
      localeData.PAGE_FORM_ELEMENT_PLACEHOLDER_TITLE,
      formElementInputPlaceholder
    );

    const cellElementsForName = this.createCellFormElementElements(
      localeData.PAGE_FORM_ELEMENT_TECHNICAL_NAME_TITLE,
      formElementInputName
    );

    const cellElementsForDescription = this.createCellFormElementElements(
      localeData.PAGE_FORM_ELEMENT_DESCRIPTION_TITLE,
      formElementInputDescription
    );

    const cellElementsForSequenceNumber = this.createCellFormElementElements(
      localeData.PAGE_FORM_ELEMENT_SEQUENCE_NUMBER_TITLE,
      formElementInputSequenceNumber
    );

    const buttonRemoveField = new Interactive('button');
    buttonRemoveField.target.setLabel(localeData.BUTTON_DELETE_LABEL);
    buttonRemoveField.target.setCallback((event) => {
      event.preventDefault();
      
      cellElementsForType.forEach(element => {
        element.remove();
      });

      cellElementsForTitle.forEach(element => {
        element.remove();
      });

      cellElementsForPlaceholder.forEach(element => {
        element.remove();
      });

      cellElementsForName.forEach(element => {
        element.remove();
      });

      cellElementsForDescription.forEach(element => {
        element.remove();
      });

      cellElementsForSequenceNumber.forEach(element => {
        element.remove();
      });

      buttonRemoveField.target.element.parentElement.previousElementSibling.remove();
      buttonRemoveField.target.element.parentElement.remove();

      cellHeaderElement.remove();
    });

    buttonRemoveField.assembly();

    const cellElementsForEvents = this.createCellFormElementElements(
      '', buttonRemoveField.target.element
    );

    container.parentElement.parentElement.insertBefore(
      cellHeaderElement,
      container.parentElement.previousElementSibling
    );

    cellElementsForType.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForTitle.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForName.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForDescription.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForPlaceholder.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForSequenceNumber.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForEvents.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

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
}