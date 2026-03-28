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
        let elements = [];
        if (data.outputData.form !== undefined) {
          elements = data.outputData.form.elements ?? [];
        }

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

          if (element['options'].length > 0) {
           
          }

          element['options'].forEach(optionElement => {
            
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

  // Основной метод, который теперь работает как оркестратор
  addElement(localeData, anchorElement, data = {}) {
    const rowsElement = this.createRowsContainer();
    const formElements = this.createFormElements(localeData, data);
    
    this.setupElementValues(formElements, data);
    this.setupNameChangeListener(formElements.inputName);
    
    const typeSelect = this.createTypeSelect(localeData, data.type);
    const requiredCheckbox = this.createRequiredCheckbox(data.required, this.elementsCount);
    const actionButtons = this.createActionButtons(localeData, rowsElement, formElements.inputName);
    
    this.setupTypeChangeListener(typeSelect, rowsElement, formElements.inputName, actionButtons.addOptionButton, localeData);
    
    this.appendRows(rowsElement, localeData, data, formElements, typeSelect, requiredCheckbox, actionButtons.removeButton);
    this.insertIntoDOM(rowsElement, anchorElement, formElements, data);
    
    this.elementsCount++;
  }

  // Создание контейнера для строк
  createRowsContainer() {
    const rowsElement = document.createElement('div');
    rowsElement.classList.add('grid-table__rows');
    return rowsElement;
  }

  // Создание всех полей формы
  createFormElements(localeData, data) {
    return {
      inputTitle: this.createInputField('text', 'form_element_title[]', localeData.PAGE_FORM_ELEMENT_TITLE_PLACEHOLDER, true, ['form__input', 'form__input_text']),
      inputName: this.createInputField('text', 'form_element_name[]', 'my_field', true, ['form__input', 'form__input_text'], '[a-zA-Z0-9_]+'),
      inputDescription: this.createTextareaField('form_element_description[]', localeData.PAGE_FORM_ELEMENT_DESCRIPTION_PLACEHOLDER, ['form__textarea']),
      inputPlaceholder: this.createInputField('text', 'form_element_placeholder[]', localeData.PAGE_FORM_ELEMENT_PLACEHOLDER_PLACEHOLDER, false, ['form__input', 'form__input_text']),
      inputSequenceNumber: this.createInputField('number', 'form_element_sequence_number[]', 4, true, ['form__input', 'form__input_number'])
    };
  }

  // Общий метод создания input полей
  createInputField(type, name, placeholder, required, classes, pattern = null) {
    const input = document.createElement('input');
    input.setAttribute('type', type);
    input.setAttribute('name', name);
    input.setAttribute('placeholder', placeholder);
    
    if (required) {
      input.setAttribute('required', 'required');
    }
    
    if (pattern) {
      input.setAttribute('pattern', pattern);
    }
    
    classes.forEach(className => input.classList.add(className));
    
    return input;
  }

  // Создание textarea поля
  createTextareaField(name, placeholder, classes) {
    const textarea = document.createElement('textarea');
    textarea.setAttribute('name', name);
    textarea.setAttribute('placeholder', placeholder);
    classes.forEach(className => textarea.classList.add(className));
    return textarea;
  }

  // Создание заголовка секции
  createSectionHeader(localeData, data) {
    const header = document.createElement('div');
    header.classList.add('row', 'grid-table__row', 'grid-table__row_header');
    header.innerText = data.title !== undefined
      ? `${localeData.PAGE_FORM_ELEMENT}: ${data.title}`
      : localeData.PAGE_FORM_NEW_ELEMENT;
    return header;
  }

  // Создание выпадающего списка типов полей
  createTypeSelect(localeData, selectedType) {
    const typeField = new Interactive('choices');
    const typeMapping = this.getTypeMapping();
    
    // Добавление всех типов полей
    Object.entries(typeMapping).forEach(([label, value]) => {
      typeField.target.addItem(label, value);
    });
    
    typeField.target.setName('form_element_type[]');
    
    // Установка выбранного значения
    if (selectedType !== undefined) {
      const selectedIndex = Object.values(typeMapping).indexOf(selectedType);
      typeField.target.setItemSelectedIndex(selectedIndex !== -1 ? selectedIndex : 0);
    }
    
    typeField.assembly();
    return typeField;
  }

  // Получение маппинга типов полей
  getTypeMapping() {
    return {
      'Text': 'text',
      'Number': 'number',
      'Date': 'date',
      'Textarea': 'textarea',
      'EMail': 'email',
      'Phone': 'tel',
      'Checkbox': 'checkbox',
      'Select': 'select',
      'Button Submit': 'submit',
      'Button Reset': 'reset'
    };
  }

  // Создание чекбокса "обязательное поле"
  createRequiredCheckbox(isRequired, elementsCount) {
    const checkboxID = this.generateRandomId();
    const container = document.createElement('div');
    container.classList.add('form__checkbox-container', 'checkbox-container');
    
    const checkbox = document.createElement('input');
    checkbox.classList.add('checkbox-container__input', 'form__input', 'form__input_checkbox');
    checkbox.setAttribute('id', `I${checkboxID}`);
    checkbox.setAttribute('name', `form_element_required[${elementsCount}]`);
    checkbox.setAttribute('type', 'checkbox');
    checkbox.setAttribute('value', 'required');
    
    if (isRequired) {
      checkbox.setAttribute('checked', 'checked');
    }
    
    const label = document.createElement('label');
    label.classList.add('checkbox-container__label', 'form__label');
    label.setAttribute('for', `I${checkboxID}`);
    
    container.appendChild(checkbox);
    container.appendChild(label);
    
    return container;
  }

  // Генерация случайного ID
  generateRandomId() {
    return Array(10).fill(0).map(() => Math.floor(Math.random() * 10)).join('');
  }

  // Создание строки (ячейки) с заголовком и содержимым
  createRowElement(title, content) {
    const row = document.createElement('div');
    row.classList.add('row', 'grid-table__row');
    
    const titleCell = document.createElement('div');
    titleCell.classList.add('grid-table__cell', 'grid-table__cell_title');
    titleCell.innerText = title || '';
    
    const contentCell = document.createElement('div');
    contentCell.classList.add('grid-table__cell');
    contentCell.appendChild(content);
    
    row.appendChild(titleCell);
    row.appendChild(contentCell);
    
    return row;
  }

  // Создание кнопок управления
  createActionButtons(localeData, rowsElement, inputName) {
    const removeButton = this.createRemoveButton(localeData, rowsElement);
    const addOptionButton = this.createAddOptionButton(localeData, inputName, rowsElement);
    
    // Размещаем кнопку добавления опции перед кнопкой удаления
    removeButton.target.element.before(addOptionButton.target.element);
    addOptionButton.target.element.style.display = 'none';
    
    return {
      removeButton,
      addOptionButton
    };
  }

  // Создание кнопки удаления поля
  createRemoveButton(localeData, rowsElement) {
    const button = new Interactive('button');
    button.target.setLabel(localeData.BUTTON_DELETE_LABEL);
    button.target.setStyle('red');
    button.target.setCallback((event) => {
      event.preventDefault();
      this.elementsCount--;
      rowsElement.remove();
      button.target.element.parentElement.previousElementSibling.remove();
      button.target.element.parentElement.remove();
    });
    button.assembly();
    return button;
  }

  // Создание кнопки добавления опции для select
  createAddOptionButton(localeData, inputName, rowsElement) {
    const button = new Interactive('button');
    button.target.setLabel(localeData.BUTTON_NEW_OPTION_LABEL);
    button.target.setStyle('default');
    button.target.setCallback((event) => {
      event.preventDefault();
      const rowOptions = document.querySelectorAll(`[data-element="select-option-label"][data-select="${inputName.value}"]`);
      const rowOption = this.createRowSelectOption(localeData, inputName, rowOptions.length);
      rowsElement.children.item(rowsElement.children.length - 1).before(rowOption);
    });
    button.assembly();
    return button;
  }

  // Настройка слушателя изменения имени поля
  setupNameChangeListener(inputName) {
    inputName.addEventListener('change', (event) => {
      const selectOptionsElements = document.querySelectorAll('[data-select]');
      selectOptionsElements.forEach(element => {
        const match = element.getAttribute('name').match(/\[(\d+)\]/);
        const number = match ? parseInt(match[1], 10) : 0;
        
        if (element.getAttribute('data-element') === 'select-option-label') {
          element.setAttribute('name', `form_element_select_${inputName.value}_option_label[${number}]`);
        }
        
        if (element.getAttribute('data-element') === 'select-option-value') {
          element.setAttribute('name', `form_element_select_${inputName.value}_option_value[${number}]`);
        }
      });
    });
  }

  // Настройка слушателя изменения типа поля
  setupTypeChangeListener(typeSelect, rowsElement, inputName, addOptionButton, localeData) {
    typeSelect.target.elementSelect.addEventListener('change', (event) => {
      const isSelectType = typeSelect.target.itemSelectedIndex === 7; // Индекс типа "Select"
      
      if (isSelectType) {
        const rowOption = this.createRowSelectOption(localeData, inputName, 0);
        rowsElement.children.item(rowsElement.children.length - 1).before(rowOption);
        addOptionButton.target.element.style.display = 'flex';
      } else {
        const rowOptions = document.querySelectorAll(`[data-element="select-option-label"][data-select="${inputName.value}"]`);
        if (rowOptions.length > 0) {
          rowOptions.forEach(rowOption => {
            rowOption.parentElement.parentElement.parentElement.remove();
          });
        }
        addOptionButton.target.element.style.display = 'none';
      }
    });
  }

  // Установка значений полей из переданных данных
  setupElementValues(formElements, data) {
    formElements.inputTitle.value = data.title !== undefined ? data.title : '';
    formElements.inputName.value = data.name !== undefined ? data.name : '';
    formElements.inputPlaceholder.value = data.placeholder !== undefined ? data.placeholder : '';
    formElements.inputDescription.value = data.description !== undefined ? data.description : '';
    formElements.inputSequenceNumber.value = data.sequenceNumber !== undefined ? data.sequenceNumber : 0;
  }

  // Добавление всех строк в контейнер
  appendRows(rowsElement, localeData, data, formElements, typeSelect, requiredCheckbox, removeButton) {
    const header = this.createSectionHeader(localeData, data);
    const typeRow = this.createRowElement(localeData.PAGE_FORM_ELEMENT_TYPE_TITLE, typeSelect.target.element);
    const titleRow = this.createRowElement(localeData.PAGE_FORM_ELEMENT_TITLE_TITLE, formElements.inputTitle);
    const nameRow = this.createRowElement(localeData.PAGE_FORM_ELEMENT_TECHNICAL_NAME_TITLE, formElements.inputName);
    const descriptionRow = this.createRowElement(localeData.PAGE_FORM_ELEMENT_DESCRIPTION_TITLE, formElements.inputDescription);
    const placeholderRow = this.createRowElement(localeData.PAGE_FORM_ELEMENT_PLACEHOLDER_TITLE, formElements.inputPlaceholder);
    const sequenceRow = this.createRowElement(localeData.PAGE_FORM_ELEMENT_SEQUENCE_NUMBER_TITLE, formElements.inputSequenceNumber);
    const requiredRow = this.createRowElement(localeData.PAGE_FORM_ELEMENT_REQUIRED_TITLE, requiredCheckbox);
    
    const buttonsRow = this.createRowElement(null, removeButton.target.element);
    buttonsRow.classList.add('grid-table__cell_panel');
    
    rowsElement.append(
      header, typeRow, titleRow, nameRow, descriptionRow, 
      placeholderRow, sequenceRow, requiredRow, buttonsRow
    );
  }

  // Вставка в DOM
  insertIntoDOM(rowsElement, anchorElement, formElements, data) {
    const formElementsSectionHeader = document.querySelector('[data-element="form-elements-section-header"]');
    
    if (formElementsSectionHeader !== null) {
      if (anchorElement === null) {
        formElementsSectionHeader.after(rowsElement);
      } else {
        anchorElement.after(rowsElement);
      }
      
      this.setupElementValues(formElements, data);
    }
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