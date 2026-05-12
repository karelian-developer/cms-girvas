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

    this.reindexSelectOptions = this.reindexSelectOptions.bind(this);
  }

  init() {
    this.searchParams = new URLParser();
    this.elementForm = document.querySelector('[data-element="main-form"]');
    
    this.loadLocalesAndInitialize();
  }

  // Основные методы загрузки и инициализации
  loadLocalesAndInitialize() {
    fetch('/handler/locales', {method: 'GET'})
      .then((response) => this.handleResponse(response))
      .then((data) => {
        this.locales = data.outputData.locales;
        return window.CMSCore.locales.admin.getData();
      })
      .then((localeData) => this.initializeWithLocaleData(localeData))
      .catch((rejectionReason) => this.page.showPopupNotification(rejectionReason, 0));
  }

  handleResponse(response) {
    return response.ok ? response.json() : Promise.reject(response);
  }

  initializeWithLocaleData(localeData) {
    this.localeData = localeData;
    this.formElements = this.getFormInputElements();
    
    this.initializeLocaleChoices();
    this.initializeMethodChoices();
    this.setupUrlInputListener();
    this.initializeButtons();
    this.loadExistingFormData();
    this.setupLocaleChangeListener();
    this.setupButtonsUI();
  }

  reindexSelectOptions(selectElementName, rowsElement) {
    // Если передан rowsElement, ищем только в нём
    const container = rowsElement || document;
    const options = container.querySelectorAll(`[data-element="select-option-label"][data-select="${selectElementName}"]`);
    
    options.forEach((option, newIndex) => {
      const row = option.closest('.row');
      
      const oldLabelName = option.getAttribute('name');
      const newLabelName = oldLabelName.replace(/\[(\d+)\]/, `[${newIndex}]`);
      option.setAttribute('name', newLabelName);
      
      const valueInput = row.querySelector('[data-element="select-option-value"]');
      if (valueInput) {
        const oldValueName = valueInput.getAttribute('name');
        const newValueName = oldValueName.replace(/\[(\d+)\]/, `[${newIndex}]`);
        valueInput.setAttribute('name', newValueName);
      }
      
      const titleElement = row.querySelector('.grid-table__cell_title');
      if (titleElement) {
        titleElement.innerText = `Option #${newIndex + 1}`;
      }
    });
  }

  // Получение DOM элементов формы
  getFormInputElements() {
    return {
      urlInput: document.querySelector('[data-element="input-name"]'),
      titleInput: document.querySelector('[data-element="input-title"]'),
      descriptionTextarea: document.querySelector('[data-element="input-description"]')
    };
  }

  // Инициализация выбора языка
  initializeLocaleChoices() {
    this.interactiveLocaleChoices = new Interactive('choices');
    this.interactiveLocaleChoices.target.setName('common_locale');
    
    this.populateLocaleChoices();
    this.setDefaultLocale();
    this.interactiveLocaleChoices.assembly();
    
    const interactiveContainer = document.querySelector('[data-element="header-interactive"]');
    interactiveContainer.append(this.interactiveLocaleChoices.target.element);
  }

  populateLocaleChoices() {
    this.locales.forEach((locale) => {
      const localeTemplate = this.createLocaleTemplate(locale);
      this.interactiveLocaleChoices.target.addItem(localeTemplate, locale.name);
    });
  }

  createLocaleTemplate(locale) {
    const iconElement = this.createLocaleIcon(locale.iconURL, locale.title);
    const labelElement = this.createLocaleLabel(locale.title);
    return iconElement.outerHTML + labelElement.outerHTML;
  }

  createLocaleIcon(iconURL, alt) {
    const img = document.createElement('img');
    img.setAttribute('src', iconURL);
    img.setAttribute('alt', alt);
    return img;
  }

  createLocaleLabel(title) {
    const span = document.createElement('span');
    span.innerText = title;
    return span;
  }

  setDefaultLocale() {
    this.locales.forEach((locale, index) => {
      if (locale.name === window.CMSCore.locales.admin.name) {
        this.interactiveLocaleChoices.target.setItemSelectedIndex(index);
        this.setupFormFieldNames(locale);
      }
    });
  }

  setupFormFieldNames(locale) {
    this.formElements.descriptionTextarea.setAttribute('name', `form_description_${locale.iso639_2}`);
    this.formElements.titleInput.setAttribute('name', `form_title_${locale.iso639_2}`);
  }

  // Инициализация выбора метода
  initializeMethodChoices() {
    const methodContainer = document.querySelector('[data-element="choice"][data-choice="method"]');
    if (!methodContainer) return;
    
    this.interactiveMethodChoices = new Interactive('choices');
    this.loadMethodsData();
  }

  loadMethodsData() {
    let formConstrData;
    const formId = this.searchParams.getPathPart(3);
    
    fetch(`/handler/form/${formId}?locale=${window.CMSCore.locales.admin.name}`, {method: 'GET'})
      .then((response) => this.handleResponse(response))
      .then((data) => {
        formConstrData = data.outputData.form;
        return fetch(`/handler/forms/methods?locale=${window.CMSCore.locales.admin.name}`, {method: 'GET'});
      })
      .then((response) => this.handleResponse(response))
      .then((data) => this.populateMethodChoices(data, formConstrData))
      .catch((rejectionReason) => this.page.showPopupNotification(rejectionReason, 0));
  }

  populateMethodChoices(data, formConstrData) {
    if (!data.outputData?.methods?.length) return;
    
    data.outputData.methods.forEach((method, index) => {
      this.interactiveMethodChoices.target.addItem(method.name.toUpperCase(), method.id);
      
      if (formConstrData?.methodID === method.id) {
        this.interactiveMethodChoices.target.setItemSelectedIndex(index);
      }
    });
    
    this.interactiveMethodChoices.target.setName('form_method_id');
    this.interactiveMethodChoices.assembly();
    
    const methodContainer = document.querySelector('[data-element="choice"][data-choice="method"]');
    methodContainer.append(this.interactiveMethodChoices.target.element);
  }

  // Настройка слушателя для поля URL
  setupUrlInputListener() {
    this.formElements.urlInput.addEventListener('input', (event) => {
      const utils = new Utils();
      const uString = utils.createString(event.target.value);
      uString.source = uString.translitToEN(true);
      uString.source = uString.source.toLowerCase();
      uString.source = uString.source.replace(/[^a-z0-9\-]/, '');
      event.target.value = uString.source;
    });
  }

  // Инициализация кнопок
  initializeButtons() {
    this.buttons = {
      addElement: new Interactive('button'),
      save: new Interactive('button'),
      delete: new Interactive('button')
    };
    
    this.setupButtonLabels();
    this.setupButtonStyles();
    this.setupButtonCallbacks();
  }

  setupButtonLabels() {
    this.buttons.addElement.target.setLabel(this.localeData.BUTTON_NEW_ELEMENT_LABEL);
    this.buttons.save.target.setLabel(this.localeData.BUTTON_SAVE_LABEL);
    this.buttons.delete.target.setLabel(this.localeData.BUTTON_DELETE_LABEL);
  }

  setupButtonStyles() {
    this.buttons.addElement.target.setStyle('default');
    this.buttons.save.target.setStyle('green');
    this.buttons.delete.target.setStyle('red');
  }

  setupButtonCallbacks() {
    // В методе setupButtonCallbacks или в addElement
    this.buttons.addElement.target.setCallback((event) => {
      event.preventDefault();
      
      // Получаем все элементы формы
      const formElements = document.querySelectorAll('[data-element="form-element"]');
      
      let anchorElement = null;
      
      if (formElements.length > 0) {
        // Если есть существующие элементы, берем последний
        anchorElement = formElements[formElements.length - 1];
      } else {
        // Если нет элементов, берем заголовок секции
        anchorElement = document.querySelector('[data-element="form-elements-section-header"]');
      }
      
      this.addElement(this.localeData, anchorElement);
    });
    
    this.buttons.save.target.setCallback((event) => this.handleSave(event));
    this.buttons.delete.target.setCallback((event) => this.handleDelete(event));
  }

  // Обработка сохранения формы
  handleSave(event) {
    event.preventDefault();
    this.elementForm = document.querySelector('[data-element="main-form"]');
    
    const form = new Interactive('form');
    form.target.replaceElement(this.elementForm);
    
    if (!form.target.checkRequiredFields()) {
      this.page.showPopupNotification(this.localeData.FORM_REQUIRED_FIELDS_IS_EMPTY, 0);
      return;
    }
    
    const formData = new FormData(this.elementForm);
    
    formData.append('common_locale', this.interactiveLocaleChoices.target.getValue());
    
    const formId = this.searchParams.getPathPart(3);
    const fetchLink = formId === null 
      ? `/handler/form?localeMessage=${window.CMSCore.locales.admin.name}`
      : `/handler/form/${formId}?localeMessage=${window.CMSCore.locales.admin.name}`;
    const fetchMethod = formId === null ? 'PUT' : 'PATCH';
    
    const request = new Interactive('request', {
      method: fetchMethod,
      url: fetchLink
    });

    request.target.data = formData;
    
    request.target.send().then((data) => {
      if (data.statusCode === 1 && formId === null) {
        window.location.href = `/admin/form/${data.outputData.form.id}`;
      }
    });
  }

  // Обработка удаления формы
  handleDelete(event) {
    const modal = new Interactive('modal', {
      title: this.localeData.MODAL_ENTRIES_CATEGORY_DELETE_TITLE,
      content: this.localeData.MODAL_ENTRIES_CATEGORY_DELETE_DESCRIPTION
    });
    
    modal.target.addButton(this.localeData.BUTTON_DELETE_LABEL, () => {
      const formData = new FormData();
      formData.append('form_id', this.searchParams.getPathPart(3));
      
      const request = new Interactive('request', {
        method: 'DELETE',
        url: `/handler/form/${this.searchParams.getPathPart(3)}?localeMessage=${window.CMSCore.locales.admin.name}`,
        data: formData
      });
      
      request.target.send().then((data) => {
        if (data.statusCode === 1) {
          window.location.href = '/admin/forms';
        }
      });
    });
    
    modal.target.addButton(this.localeData.BUTTON_CANCEL_LABEL, () => modal.target.close());
    modal.assembly();
    document.body.appendChild(modal.target.element);
    modal.target.show();
  }

  // Загрузка существующих данных формы
  loadExistingFormData() {
    const formId = this.searchParams.getPathPart(3);
    if (!formId) return;
    
    fetch(`/handler/form/${formId}?locale=${window.CMSCore.locales.admin.name}&localeMessage=${window.CMSCore.locales.admin.name}`, {method: 'GET'})
      .then((response) => this.handleResponse(response))
      .then((data) => this.renderExistingFormElements(data))
      .catch((rejectionReason) => this.page.showPopupNotification(rejectionReason, 0));
  }

  renderExistingFormElements(data) {
    const elements = data.outputData.form?.elements ?? [];
    
    elements.forEach((element, index) => {
      const elementTexts = element.texts[window.CMSCore.locales.admin.name] ?? {};
      const formElements = document.querySelectorAll('[data-element="form-element"]');
      const anchorElement = formElements[formElements.length - 1] ?? null;
      
      const optionsWithTexts = element.options?.map(option => ({
        value: option.value,
        label: option.texts?.[window.CMSCore.locales.admin.name]?.label || ''
      })) || [];

      this.addElement(this.localeData, anchorElement, {
        index: index,
        type: element.type,
        required: element.required,
        title: elementTexts.title,
        description: elementTexts.description,
        placeholder: elementTexts.placeholder,
        name: element.name,
        sequenceNumber: element.sequenceNumber,
        options: optionsWithTexts
      });
    });
  }

  // Настройка слушателя смены локали
  setupLocaleChangeListener() {
    const selectElement = document.querySelector('[data-element="header-interactive"] select');
    selectElement.addEventListener('change', (event) => this.handleLocaleChange(event));
  }

  handleLocaleChange(event) {
    const selectedLocale = this.locales.find(locale => locale.name === event.target.value);
    if (!selectedLocale) return;
    
    this.updateFormFieldNames(selectedLocale);
    this.updateFormElementsTextsForLocale(selectedLocale);
    
    const formId = this.searchParams.getPathPart(3);
    if (formId) {
      this.loadFormDataForLocale(selectedLocale);
    }
  }

  updateFormElementsTextsForLocale(locale) {
    const formRows = document.querySelectorAll('.grid-table__rows');
    const formId = this.searchParams.getPathPart(3);
    
    if (!formId) return;
    
    formRows.forEach((row) => {
      const nameInput = row.querySelector('[name="form_element_name[]"]');
      if (!nameInput) return;
      
      const elementName = nameInput.value;
      const optionLabels = row.querySelectorAll('[data-element="select-option-label"]');
      
      if (optionLabels.length > 0) {
        this.loadSelectOptionsForLocale(formId, elementName, locale, optionLabels);
      }
    });
  }

  loadSelectOptionsForLocale(formId, elementName, locale, optionLabels) {
    const request = new Interactive('request', {
      method: 'GET',
      url: `/handler/form/${formId}?locale=${locale.name}&localeMessage=${window.CMSCore.locales.admin.name}`,
    });

    request.target.showingNotification = false;
    
    request.target.send().then((data) => {
      if (data.statusCode === 1 && data.outputData.form?.elements) {
        const element = data.outputData.form.elements.find(el => el.name === elementName);
        
        if (element && element.options) {
          optionLabels.forEach((labelInput, optionIndex) => {
            const option = element.options[optionIndex];
            const labelKey = locale.name;
            
            if (option && option.texts?.[labelKey]?.label) {
              labelInput.value = option.texts[labelKey].label;
            } else {
              labelInput.value = '';
            }
          });
        }
      }
    });
  }

  updateFormFieldNames(locale) {
    const titleInput = document.querySelector('[data-element="input-title"]');
    const descriptionTextarea = document.querySelector('[data-element="input-description"]');
    
    titleInput.setAttribute('name', `form_title_${locale.iso639_2}`);
    descriptionTextarea.setAttribute('name', `form_description_${locale.iso639_2}`);
  }

  loadFormDataForLocale(locale) {
    const request = new Interactive('request', {
      method: 'GET',
      url: `/handler/form/${this.searchParams.getPathPart(3)}?locale=${locale.name}&localeMessage=${window.CMSCore.locales.admin.name}`,
      showingNotification: false
    });
    
    request.target.send().then((data) => {
      if (data.statusCode === 1 && data.outputData.form) {
        this.updateFormWithLocaleData(data.outputData.form, locale);
      }
    });
  }

  updateFormWithLocaleData(formData, locale) {
    const titleInput = document.querySelector('[data-element="input-title"]');
    const descriptionTextarea = document.querySelector('[data-element="input-description"]');
    
    titleInput.value = formData.title;
    descriptionTextarea.value = formData.description;
    
    this.updateFormElementsTexts(formData.elements, locale);
  }

  updateFormElementsTexts(elements, locale) {
    const titleInputs = document.querySelectorAll('[name="form_element_title[]"]');
    const descriptionInputs = document.querySelectorAll('[name="form_element_description[]"]');
    const placeholderInputs = document.querySelectorAll('[name="form_element_placeholder[]"]');
    
    elements.forEach((element, index) => {
      titleInputs[index].value = this.getLocalizedFormElementText(element, 'title', locale.name);
      descriptionInputs[index].value = this.getLocalizedFormElementText(element, 'description', locale.name);
      placeholderInputs[index].value = this.getLocalizedFormElementText(element, 'placeholder', locale.name);
    });
  }

  // Настройка UI кнопок
  setupButtonsUI() {
    this.buttons.addElement.assembly();
    this.buttons.save.assembly();
    this.buttons.delete.assembly();
    
    const isNewForm = this.searchParams.getPathPart(3) === null;
    this.buttons.delete.target.element.style.display = isNewForm ? 'none' : 'flex';
    this.buttons.save.target.element.style.display = 'flex';
    
    const panelContainer = document.querySelector('[data-element="panel"]');
    panelContainer.append(
      this.buttons.addElement.target.element,
      this.buttons.delete.target.element,
      this.buttons.save.target.element
    );
  }

  getLocalizedFormElementText(elementData, field, preferredLocale) {
    const texts = elementData?.texts;
    if (!texts) return '';
    
    if (texts[preferredLocale]?.[field]) {
      return texts[preferredLocale][field];
    }

    return '';
  }

  addElement(localeData, anchorElement, data = {}) {
    const rowsElement = this.createRowsContainer();
    rowsElement.setAttribute('data-element', 'form-element');

    const formElements = this.createFormElements(localeData, data);
    
    this.setupElementValues(formElements, data);
    
    // КЛЮЧЕВОЕ ИЗМЕНЕНИЕ: Настраиваем автоматическое обновление всех option'ов
    // при изменении имени поля
    this.setupRealTimeNameUpdate(formElements.inputName, rowsElement);
    
    const typeSelect = this.createTypeSelect(localeData, data.type);
    const requiredCheckbox = this.createRequiredCheckbox(data.required, this.elementsCount);
    const actionButtons = this.createActionButtons(localeData, rowsElement, formElements.inputName);
    
    this.setupTypeChangeListener(typeSelect, rowsElement, formElements.inputName, actionButtons.addOptionButton, localeData);
    
    this.appendRows(rowsElement, localeData, data, formElements, typeSelect, requiredCheckbox, actionButtons.removeButton);
    actionButtons.removeButton.target.element.before(actionButtons.addOptionButton.target.element);

    this.insertIntoDOM(rowsElement, anchorElement, formElements, data);

    const formElementsSectionHeader = document.querySelector('[data-element="form-elements-section-header"]');

    if (formElementsSectionHeader !== null) {
      if (anchorElement === null) {
        formElementsSectionHeader.after(rowsElement);
      } else {
        anchorElement.after(rowsElement);
      }
    }
    
    if (data.type === 'select' && data.options && data.options.length > 0) {
      data.options.forEach((option, optionIndex) => {
        const rowOption = this.createRowSelectOption(
          localeData,
          formElements.inputName,
          optionIndex,
          option.label,
          option.value
        );
        rowsElement.children.item(rowsElement.children.length - 1).before(rowOption);
      });
      actionButtons.addOptionButton.target.element.style.display = 'flex';
    }

    this.elementsCount++;
  }

  createRowsContainer() {
    const rowsElement = document.createElement('div');
    rowsElement.classList.add('grid-table__rows');
    return rowsElement;
  }

  setupRealTimeNameUpdate(inputName, rowsElement) {
    // Функция для обновления всех option'ов в контейнере
    const updateAllOptions = () => {
      const currentName = inputName.value;
      if (!currentName) return; // Не обновляем, если имя пустое
      
      const optionLabels = rowsElement.querySelectorAll('[data-element="select-option-label"]');
      const optionValues = rowsElement.querySelectorAll('[data-element="select-option-value"]');
      
      optionLabels.forEach(label => {
        const match = label.getAttribute('name').match(/\[(\d+)\]/);
        const index = match ? parseInt(match[1], 10) : 0;
        
        const newName = `form_element_select_${currentName}_option_label[${index}]`;
        if (label.getAttribute('name') !== newName) {
          label.setAttribute('name', newName);
          label.setAttribute('data-select', currentName);
        }
      });
      
      optionValues.forEach(value => {
        const match = value.getAttribute('name').match(/\[(\d+)\]/);
        const index = match ? parseInt(match[1], 10) : 0;
        
        const newName = `form_element_select_${currentName}_option_value[${index}]`;
        if (value.getAttribute('name') !== newName) {
          value.setAttribute('name', newName);
          value.setAttribute('data-select', currentName);
        }
      });
    };
    
    // Слушаем изменения имени поля
    inputName.addEventListener('input', updateAllOptions);
    inputName.addEventListener('change', updateAllOptions);
    
    // Также обновляем при создании новых option'ов
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'childList') {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1) { // Element node
              // Проверяем, есть ли в добавленном элементе option'ы
              if (node.querySelector && (
                node.querySelector('[data-element="select-option-label"]') ||
                node.matches('[data-element="select-option-label"]')
              )) {
                setTimeout(updateAllOptions, 0);
              }
            }
          });
        }
      });
    });
    
    observer.observe(rowsElement, {
      childList: true,
      subtree: true
    });
  }

  createRowSelectOption(localeData, inputName, index, label = '', value = '') {
    const inputGroupElement = document.createElement('div');
    inputGroupElement.classList.add('grid-table__input-group');
    
    const fieldName = inputName.value || '';
    
    const inputOptionLabelElement = document.createElement('input');
    inputOptionLabelElement.classList.add('form__input', 'form__input_text');
    inputOptionLabelElement.setAttribute('type', 'text');
    inputOptionLabelElement.setAttribute('name', `form_element_select_${fieldName}_option_label[${index}]`);
    inputOptionLabelElement.setAttribute('data-element', 'select-option-label');
    inputOptionLabelElement.setAttribute('data-select', fieldName);
    inputOptionLabelElement.setAttribute('placeholder', localeData.PAGE_FORM_ELEMENT_OPTION_LABEL_PLACEHOLDER);
    
    if (label) {
      inputOptionLabelElement.value = label;
    }
    
    const inputOptionValueElement = document.createElement('input');
    inputOptionValueElement.classList.add('form__input', 'form__input_text');
    inputOptionValueElement.setAttribute('type', 'text');
    inputOptionValueElement.setAttribute('name', `form_element_select_${fieldName}_option_value[${index}]`);
    inputOptionValueElement.setAttribute('data-element', 'select-option-value');
    inputOptionValueElement.setAttribute('data-select', fieldName);
    inputOptionValueElement.setAttribute('placeholder', localeData.PAGE_FORM_ELEMENT_OPTION_VALUE_PLACEHOLDER);
    
    if (value) {
      inputOptionValueElement.value = value;
    }

    inputGroupElement.append(inputOptionLabelElement);
    inputGroupElement.append(inputOptionValueElement);
    
    const removeOptionButton = new Interactive('button');
    removeOptionButton.target.setLabel(localeData.BUTTON_DELETE_LABEL);
    removeOptionButton.target.setStyle('red');
    removeOptionButton.target.setCallback((event) => {
      event.preventDefault();
      const row = removeOptionButton.target.element.closest('.row');
      if (row) {
        const selectName = inputName.value; // Используем актуальное имя
        row.remove();
        this.reindexSelectOptions(selectName);
      }
    });

    removeOptionButton.assembly();
    
    inputGroupElement.appendChild(removeOptionButton.target.element);

    // Планируем обновление имени сразу после вставки в DOM
    setTimeout(() => {
      if (inputName.value) {
        const newLabelName = `form_element_select_${inputName.value}_option_label[${index}]`;
        const newValueName = `form_element_select_${inputName.value}_option_value[${index}]`;
        
        inputOptionLabelElement.setAttribute('name', newLabelName);
        inputOptionLabelElement.setAttribute('data-select', inputName.value);
        inputOptionValueElement.setAttribute('name', newValueName);
        inputOptionValueElement.setAttribute('data-select', inputName.value);
      }
    }, 0);

    return this.createRowElement(
      localeData.PAGE_FORM_ELEMENT_OPTION_TITLE + ' #' + (index + 1),
      inputGroupElement
    );
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
    
    return {
      removeButton,
      addOptionButton
    };
  }

  // Создание кнопки удаления поля
  createRemoveButton(localeData, rowsElement, inputName) {
    const button = new Interactive('button');
    button.target.setLabel(localeData.BUTTON_DELETE_LABEL);
    button.target.setStyle('red');
    button.target.setCallback((event) => {
      event.preventDefault();
      this.elementsCount--;
      rowsElement.remove();
      // Находим и удаляем связанные элементы (заголовок и панель кнопок)
      const previousRow = rowsElement.previousElementSibling;
      const nextRow = rowsElement.nextElementSibling;
      if (previousRow && previousRow.classList.contains('row')) {
        previousRow.remove();
      }
      if (nextRow && nextRow.classList.contains('row')) {
        nextRow.remove();
      }
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
      // Ищем option'ы только внутри текущего rowsElement
      const rowOptions = rowsElement.querySelectorAll(`[data-element="select-option-label"]`);
      const rowOption = this.createRowSelectOption(localeData, inputName, rowOptions.length);
      rowsElement.children.item(rowsElement.children.length - 1).before(rowOption);
    });

    button.assembly();
    return button;
  }

  // Настройка слушателя изменения имени поля
  // Настройка слушателя изменения имени поля
  setupNameChangeListener(inputName, rowsElement) {
    // Сохраняем начальное значение
    inputName.dataset.oldName = inputName.value;
    
    inputName.addEventListener('change', (event) => {
      const oldName = inputName.dataset.oldName || '';
      const newName = inputName.value;
      
      // Ищем ВСЕ option'ы, связанные с этим полем (включая с пустым именем)
      const allSelectOptions = rowsElement.querySelectorAll('[data-element="select-option-label"]');
      const allSelectValues = rowsElement.querySelectorAll('[data-element="select-option-value"]');
      
      // Обновляем option labels
      allSelectOptions.forEach(element => {
        const currentDataSelect = element.getAttribute('data-select');
        
        // Обновляем, если это наш элемент (по старому имени или если имя пустое)
        if (currentDataSelect === oldName || currentDataSelect === '' || currentDataSelect === newName) {
          const match = element.getAttribute('name').match(/\[(\d+)\]/);
          const number = match ? parseInt(match[1], 10) : 0;
          
          element.setAttribute('data-select', newName);
          element.setAttribute('name', `form_element_select_${newName}_option_label[${number}]`);
        }
      });
      
      // Обновляем option values
      allSelectValues.forEach(element => {
        const currentDataSelect = element.getAttribute('data-select');
        
        if (currentDataSelect === oldName || currentDataSelect === '' || currentDataSelect === newName) {
          const match = element.getAttribute('name').match(/\[(\d+)\]/);
          const number = match ? parseInt(match[1], 10) : 0;
          
          element.setAttribute('data-select', newName);
          element.setAttribute('name', `form_element_select_${newName}_option_value[${number}]`);
        }
      });
      
      // Обновляем data-select у кнопок удаления опций
      const removeButtons = rowsElement.querySelectorAll('button');
      removeButtons.forEach(button => {
        const onclick = button.getAttribute('onclick');
        if (onclick && onclick.includes('reindexSelectOptions')) {
          // Обновляем если нужно (сложно обновить колбэк, но можно пересоздать)
        }
      });
      
      // Сохраняем новое имя как старое для следующего изменения
      inputName.dataset.oldName = newName;
    });
    
    // Добавляем также обработчик input для实时ного обновления
    inputName.addEventListener('input', (event) => {
      const newName = inputName.value;
      const oldName = inputName.dataset.oldName || '';
      
      if (newName && newName !== oldName) {
        // Обновляем data-select у существующих option'ов
        const allOptions = rowsElement.querySelectorAll('[data-element="select-option-label"], [data-element="select-option-value"]');
        allOptions.forEach(element => {
          element.setAttribute('data-select', newName);
        });
        
        // Обновляем имена, сохраняя индексы
        const optionLabels = rowsElement.querySelectorAll('[data-element="select-option-label"]');
        optionLabels.forEach(element => {
          const match = element.getAttribute('name').match(/\[(\d+)\]/);
          const number = match ? parseInt(match[1], 10) : 0;
          element.setAttribute('name', `form_element_select_${newName}_option_label[${number}]`);
        });
        
        const optionValues = rowsElement.querySelectorAll('[data-element="select-option-value"]');
        optionValues.forEach(element => {
          const match = element.getAttribute('name').match(/\[(\d+)\]/);
          const number = match ? parseInt(match[1], 10) : 0;
          element.setAttribute('name', `form_element_select_${newName}_option_value[${number}]`);
        });
      }
      
      inputName.dataset.oldName = newName;
    });
  }

  // Настройка слушателя изменения типа поля
  setupTypeChangeListener(typeSelect, rowsElement, inputName, addOptionButton, localeData) {
    typeSelect.target.elementSelect.addEventListener('change', (event) => {
      const isSelectType = typeSelect.target.itemSelectedIndex === 7;
      
      if (isSelectType) {
        // Проверяем option'ы только внутри текущего rowsElement
        const existingOptions = rowsElement.querySelectorAll('[data-element="select-option-label"]');
        if (existingOptions.length === 0) {
          const rowOption = this.createRowSelectOption(localeData, inputName, 0);
          rowsElement.children.item(rowsElement.children.length - 1).before(rowOption);
        }
        addOptionButton.target.element.style.display = 'flex';
      } else {
        // Удаляем option'ы только из текущего rowsElement
        const rowOptions = rowsElement.querySelectorAll('[data-element="select-option-label"]');
        if (rowOptions.length > 0) {
          rowOptions.forEach(rowOption => {
            const parentRow = rowOption.closest('.row');
            if (parentRow) {
              parentRow.remove();
            }
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
}