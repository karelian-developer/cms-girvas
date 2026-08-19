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
import {SEOAnalyzer} from "../../../utils/SEOAlalyzer.class.js";

export class PageEntry {
  constructor(page, params = {}) {
    this.buttons = {save: null, delete: null, publish: null, unpublish: null, SEOAnalyze: null};
    this.analyzer = null;
    this.page = page;
    this.statusCode = this.page.getPageStatusCode();
  }

  SEOAnalyze(data) {
    data.title = data.title ?? '';
    data.SEOTitle = data.SEOTitle ?? '';
    data.description = data.description ?? '';
    data.SEODescription = data.SEODescription ?? '';
    data.keywords = data.keywords ?? '';
    data.name = data.name ?? '';
    data.content = data.content ?? '';

    const analysis = this.analyzer.analyze(data);
    const report = this.analyzer.generateReport(analysis);

    return {
      analysis: analysis,
      report: report
    };
  }

  /**
   * Отрисовка SEO-результатов в сайдбар-блоке через DOM
   */
  renderSEOResults(SEOData) {
    const sidebarBlock = document.querySelector('[data-element="aside-block-seo-analyzer"]');
    if (!sidebarBlock) return;

    const blockContent = sidebarBlock.querySelector('.block-content');
    if (!blockContent) return;

    const report = SEOData.report;
    const rating = report.rating;

    blockContent.innerHTML = '';

    const analyzer = this._createElement('div', { class: 'seo-analyzer' });

    // --- Общая оценка ---
    const totalBlock = this._createElement('div', { class: 'seo-analyzer__total' });

    const scoreDiv = this._createElement('div', {
      class: 'seo-analyzer__score',
      style: `color: ${rating.color};`
    });
    scoreDiv.textContent = report.score;

    const scoreLabel = this._createElement('span', { class: 'seo-analyzer__score-label' });
    scoreLabel.textContent = '/100';
    scoreDiv.appendChild(scoreLabel);
    totalBlock.appendChild(scoreDiv);

    const ratingDiv = this._createElement('div', {
      class: 'seo-analyzer__rating',
      style: `color: ${rating.color};`
    });
    ratingDiv.textContent = rating.level;
    totalBlock.appendChild(ratingDiv);

    analyzer.appendChild(totalBlock);

    // --- Секции ---
    const sections = this._createElement('div', { class: 'seo-analyzer__sections' });

    const sectionDefs = [
      { title: 'Заголовок', data: report.details.title },
      { title: 'Описание', data: report.details.description },
      { title: 'Ключевые слова', data: report.details.keywords },
      { title: 'Контент', data: report.details.content },
      { title: 'URL', data: report.details.url }
    ];

    sectionDefs.forEach(def => {
      sections.appendChild(this._buildSEOSectionDOM(def.title, def.data));
    });

    analyzer.appendChild(sections);

    // --- Рекомендации ---
    if (report.recommendations.length > 0) {
      const recommendations = this._createElement('div', { class: 'seo-analyzer__recommendations' });

      const recTitle = this._createElement('div', { class: 'seo-analyzer__recommendations-title' });
      recTitle.textContent = 'Рекомендации по улучшению';
      recommendations.appendChild(recTitle);

      report.recommendations.forEach(recText => {
        const item = this._createElement('div', { class: 'seo-analyzer__recommendation-item' });
        item.textContent = recText;
        recommendations.appendChild(item);
      });

      analyzer.appendChild(recommendations);
    }

    blockContent.appendChild(analyzer);
  }

  /**
   * Сборка DOM-элемента для секции анализа
   */
  _buildSEOSectionDOM(title, section) {
    const score = section.score;
    const color = score >= 75 ? 'var(--color-green, #28a745)'
      : score >= 50 ? 'var(--color-yellow, #ffc107)'
      : 'var(--color-red, #dc3545)';

    const container = this._createElement('div', { class: 'seo-analyzer__section' });

    // Заголовок
    const header = this._createElement('div', { class: 'seo-analyzer__section-header' });

    const titleSpan = this._createElement('span', { class: 'seo-analyzer__section-title' });
    titleSpan.textContent = title;
    header.appendChild(titleSpan);

    const scoreSpan = this._createElement('span', {
      class: 'seo-analyzer__section-score',
      style: `color: ${color};`
    });
    scoreSpan.textContent = `${score}%`;
    header.appendChild(scoreSpan);

    container.appendChild(header);

    // Полоса прогресса
    const bar = this._createElement('div', { class: 'seo-analyzer__section-bar' });
    const barFill = this._createElement('div', {
      class: 'seo-analyzer__section-bar-fill',
      style: `width: ${score}%; background: ${color};`
    });
    bar.appendChild(barFill);
    container.appendChild(bar);

    // Детали
    const details = this._createElement('div', { class: 'seo-analyzer__section-details' });

    (section.issues || []).forEach(text => {
      details.appendChild(this._createIssueElement(text, 'error'));
    });

    (section.warnings || []).forEach(text => {
      details.appendChild(this._createIssueElement(text, 'warning'));
    });

    (section.success || []).forEach(text => {
      details.appendChild(this._createIssueElement(text, 'success'));
    });

    container.appendChild(details);

    return container;
  }

  /**
   * Элемент строки с иконкой
   */
  _createIssueElement(text, type) {
    const div = this._createElement('div', {
      class: `seo-analyzer__issue seo-analyzer__issue--${type}`
    });

    const icons = { error: '❌', warning: '⚠️', success: '✅' };
    div.textContent = `${icons[type] || ''} ${text}`;

    return div;
  }

  /**
   * Хелпер создания элемента
   */
  _createElement(tag, attrs = {}) {
    const el = document.createElement(tag);
    Object.entries(attrs).forEach(([key, value]) => {
      if (key === 'class') {
        el.className = value;
      } else if (key === 'style') {
        el.style.cssText = value;
      } else {
        el.setAttribute(key, value);
      }
    });
    return el;
  }

  init() {
    const searchParams = new URLParser();
    const elementForm = document.querySelector('[data-element="main-form"]');

    const interactiveLocaleChoices = new Interactive('choices');
    const interactiveCategoriesChoices = new Interactive('choices');

    this.page.core.locales.admin.getData().then((localeData) => {
      this.analyzer = new SEOAnalyzer(localeData);

      const contentTextareaElement = document.querySelector('[data-element="input-content"]');
      const descriptionTextareaElement = document.querySelector('[data-element="input-description"]');
      const SEODescriptionTextareaElement = document.querySelector('[data-element="input-seo-description"]');
      const titleInputElement = document.querySelector('[data-element="input-title"]');
      const SEOTitleInputElement = document.querySelector('[data-element="input-seo-title"]');
      const keywordsInputElement = document.querySelector('[data-element="input-keywords"]');
      const urlInputElement = document.querySelector('[data-element="input-url"]');

      console.log(contentTextareaElement);

      Object.keys(this.page.core.locales).forEach((locale, localeIndex) => {
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

      Object.keys(this.page.core.locales).forEach((locale, localeIndex) => {
        if (locale.name === this.page.core.locales.admin.name) {
          interactiveLocaleChoices.target.setItemSelectedIndex(localeIndex);
        }

        if (locale.name === this.page.core.locales.admin.name) {
          contentTextareaElement.setAttribute('name', 'entry_content_' + locale.iso639_2);
          descriptionTextareaElement.setAttribute('name', 'entry_description_' + locale.iso639_2);
          SEODescriptionTextareaElement.setAttribute('name', 'entry_seo_description_' + locale.iso639_2);
          titleInputElement.setAttribute('name', 'entry_title_' + locale.iso639_2);
          SEOTitleInputElement.setAttribute('name', 'entry_seo_title_' + locale.iso639_2);
          keywordsInputElement.setAttribute('name', 'entry_keywords_' + locale.iso639_2);

          if (searchParams.getPathPart(3) !== null) {
            let request = new Interactive('request', {
              method: 'GET',
              url: '/handler/entry/' + searchParams.getPathPart(3) + '?locale=' + locale.name
            });

            request.target.showingNotification = false;
    
            request.target.send().then((data) => {
              if (data.statusCode === 1) {
                contentTextareaElement.value = data.outputData.entry.content;
                descriptionTextareaElement.value = data.outputData.entry.description;
                SEODescriptionTextareaElement.value = data.outputData.entry.SEODescription;
                titleInputElement.value = data.outputData.entry.title;
                SEOTitleInputElement.value = data.outputData.entry.SEOTitle;
                keywordsInputElement.value = data.outputData.entry.keywords.join(', ');
              }
            });
          }
        }
      });

      interactiveLocaleChoices.assembly();

      const interactiveHeaderContainerElement = document.querySelector('[data-element="header-interactive"]');
      interactiveHeaderContainerElement.append(interactiveLocaleChoices.target.element);

      urlInputElement.addEventListener('input', (event) => {
        let oldValue = event.target.value;
        let cursorPos = event.target.selectionStart;

        let utils = new Utils();
        let uString = utils.createString(oldValue);
        uString.source = uString.translitToEN(true);
        uString.source = uString.source.toLowerCase();
        uString.source = uString.source.replace(/[^a-z0-9\-]/g, '');

        let newValue = uString.source;

        if (oldValue === newValue) return;

        if (Math.abs(newValue.length - oldValue.length) > 1) {
          event.target.value = newValue;
          event.target.setSelectionRange(newValue.length, newValue.length);

          return;
        }

        let removedBefore = 0;
        for (let i = 0; i < cursorPos; i++) {
          if (!/[a-z0-9\-]/.test(oldValue[i].toLowerCase())) {
            removedBefore++;
          }
        }

        event.target.value = newValue;

        let newCursorPos = cursorPos - removedBefore;
        
        if (newValue.length >= oldValue.length) {
          newCursorPos++;
        }
        
        if (newCursorPos < 0) newCursorPos = 0;
        if (newCursorPos > newValue.length) newCursorPos = newValue.length;
        
        event.target.setSelectionRange(newCursorPos, newCursorPos);
      });

      let interactiveChoicesSelectElement = interactiveHeaderContainerElement.querySelector('select');
      interactiveChoicesSelectElement.addEventListener('change', (event) => {
        Object.keys(this.page.core.locales).forEach((locale, localeIndex) => {
          if (locale.name === event.target.value) {
            contentTextareaElement.setAttribute('name', 'entry_content_' + locale.iso639_2);
            descriptionTextareaElement.setAttribute('name', 'entry_description_' + locale.iso639_2);
            SEODescriptionTextareaElement.setAttribute('name', 'entry_seo_description_' + locale.iso639_2);
            titleInputElement.setAttribute('name', 'entry_title_' + locale.iso639_2);
            SEOTitleInputElement.setAttribute('name', 'entry_seo_title_' + locale.iso639_2);
            keywordsInputElement.setAttribute('name', 'entry_keywords_' + locale.iso639_2);
            
            if (searchParams.getPathPart(3) !== null) {
              let request = new Interactive('request', {
                method: 'GET',
                url: '/handler/entry/' + searchParams.getPathPart(3) + '?locale=' + locale.name
              });
      
              request.target.send().then((data) => {
                if (data.statusCode === 1) {
                  contentTextareaElement.value = data.outputData.entry.content;
                  descriptionTextareaElement.value = data.outputData.entry.description;
                  SEODescriptionTextareaElement.value = data.outputData.entry.SEODescription;
                  titleInputElement.value = data.outputData.entry.title;
                  SEOTitleInputElement.value = data.outputData.entry.SEOTitle;
                  keywordsInputElement.value = data.outputData.entry.keywords.join(', ');
                }
              });
            }
          }
        });
      });

      this.buttons.viewOnSite = new Interactive('button');
      this.buttons.save = new Interactive('button');
      this.buttons.delete = new Interactive('button');
      this.buttons.publish = new Interactive('button');
      this.buttons.unpublish = new Interactive('button');
      this.buttons.SEOAnalyze = new Interactive('button');

      this.buttons.viewOnSite.target.setLabel(localeData.BUTTON_VIEW_ON_SITE_LABEL);
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      this.buttons.publish.target.setLabel(localeData.BUTTON_PUBLISH_LABEL);
      this.buttons.unpublish.target.setLabel(localeData.BUTTON_UNPUBLISH_LABEL);
      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.SEOAnalyze.target.setLabel(localeData.BUTTON_SEO_ANALYZE_LABEL);

      this.buttons.viewOnSite.target.setStyle('default');
      this.buttons.unpublish.target.setStyle('red');
      this.buttons.publish.target.setStyle('green');
      this.buttons.delete.target.setStyle('red');
      this.buttons.save.target.setStyle('green');
      this.buttons.SEOAnalyze.target.setStyle('default');

      this.buttons.viewOnSite.target.setCallback((event) => {
        event.preventDefault();

        let entryURL = urlInputElement.value;
        let entryLocaleName = interactiveChoicesSelectElement.value;

        window.open(`/entry/${entryURL}?locale=${entryLocaleName}`, '_blank');
      });

      this.buttons.SEOAnalyze.target.setCallback((event) => {
        event.preventDefault();

        const SEOAnalyze = this.SEOAnalyze({
          title: titleInputElement.value,
          SEOTitle: SEOTitleInputElement.value,
          description: descriptionTextareaElement.value,
          SEODescription: SEODescriptionTextareaElement.value,
          keywords: keywordsInputElement.value,
          name: urlInputElement.value,
          content: contentTextareaElement.value
        });

        this.renderSEOResults(SEOAnalyze);
      });

      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();

        let form = new Interactive('form');
        form.target.replaceElement(elementForm);
        
        if (form.target.checkRequiredFields()) {
          const formData = new FormData(elementForm);

          const publishedDateInputElement = document.querySelector('[data-element="published-date-input"]');
          if (publishedDateInputElement !== null) {
            formData.append(publishedDateInputElement.name, publishedDateInputElement.value);
          }

          const additionalDataContainerElement = document.querySelector('[data-element="additional-data"]');
          if (additionalDataContainerElement !== null) {
            const additionalDataFields = additionalDataContainerElement.querySelectorAll('input,textarea');
            additionalDataFields.forEach(element => {
              formData.append(element.name, element.value);
            });
          }

          let request = new Interactive('request', {
            method: searchParams.getPathPart(3) === null || this.statusCode === 404
              ? 'PUT'
              : 'PATCH',
            url: '/handler/entry?localeMessage=' + this.page.core.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1 && searchParams.getPathPart(3) === null) {
              if (data.outputData.hasOwnProperty('entry')) {
                let entryData = data.outputData.entry;
                window.location.href = '/admin/entry/' + entryData.id;
              }
            }
          });
        } else {
          this.page.showPopupNotification(rejectionReason, 0);
        }
      });

      this.buttons.delete.target.setCallback((event) => {
        event.preventDefault();

        let interactiveModal = new Interactive('modal', {
          title: localeData.MODAL_ENTRY_DELETE_TITLE,
          content: localeData.MODAL_ENTRY_DELETE_DESCRIPTION
        });
        
        interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
          let formData = new FormData();
          formData.append('entry_id', searchParams.getPathPart(3));

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/entry/' + searchParams.getPathPart(3) + '?localeMessage=' + this.page.core.locales.admin.name,
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              window.location.href = '/admin/entries';
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

      this.buttons.publish.target.setCallback((event) => {
        event.preventDefault();

        let formData = new FormData();
        formData.append('entry_id', searchParams.getPathPart(3));
        formData.append('entry_is_published', 1);

        let request = new Interactive('request', {
          method: 'PATCH',
          url: '/handler/entry/' + searchParams.getPathPart(3) + '?localeMessage=' + this.page.core.locales.admin.name,
        });

        request.target.data = formData;

        request.target.send().then((data) => {
          if (data.statusCode === 1) {
            this.buttons.unpublish.target.element.style.display = 'flex';
            this.buttons.publish.target.element.style.display = 'none';
          }
        });
      });

      this.buttons.unpublish.target.setCallback((event) => {
        event.preventDefault();

        let formData = new FormData();
        formData.append('entry_id', searchParams.getPathPart(3));
        formData.append('entry_is_published', 0);

        let request = new Interactive('request', {
          method: 'PATCH',
          url: '/handler/entry/' + searchParams.getPathPart(3) + '?localeMessage=' + this.page.core.locales.admin.name,
        });

        request.target.data = formData;

        request.target.send().then((data) => {
          if (data.statusCode === 1) {
            this.buttons.unpublish.target.element.style.display = 'none';
            this.buttons.publish.target.element.style.display = 'flex';
          }
        });
      });
      
      this.buttons.viewOnSite.assembly();
      this.buttons.save.assembly();
      this.buttons.delete.assembly();
      this.buttons.publish.assembly();
      this.buttons.unpublish.assembly();
      this.buttons.SEOAnalyze.assembly();

      if (searchParams.getPathPart(3) === null) {
        let request = new Interactive('request', {
          method: 'GET',
          url: '/handler/entry/categories' + '?locale=' + this.page.core.locales.admin.name + '&localeMessage=' + this.page.core.locales.admin.name,
        });

        request.target.showingNotification = false;

        request.target.send().then((responseEntryCategories) => {
          if (responseEntryCategories.statusCode === 1 && responseEntryCategories.outputData.hasOwnProperty('entriesCategories')) {
            let entriesCategories = responseEntryCategories.outputData.entriesCategories;
            
            entriesCategories.forEach((entriesCategory, entriesCategoryIndex) => {
              interactiveCategoriesChoices.target.addItem(entriesCategory.title, entriesCategory.id);
            });
            
            interactiveCategoriesChoices.target.setName('entry_category_id');
            interactiveCategoriesChoices.assembly();
            
            let interactiveContainer = document.querySelector('[data-element="choice"][data-choice="category"]');
            interactiveContainer.append(interactiveCategoriesChoices.target.element);

            let interactiveCategoriesChoicesSelectElement = interactiveCategoriesChoices.target.element.querySelector('select');
            interactiveCategoriesChoicesSelectElement.addEventListener('change', (event) => {
              fetch('/handler/entries/additional-fields?locale=' + this.page.core.locales.admin.name + '&localeMessage=' + this.page.core.locales.admin.name, {method: 'GET'}).then((response) => {
                return (response.ok) ? response.json() : Promise.reject(response);
              }).then((responseEntryAdditionalFields) => {
                let fields = responseEntryAdditionalFields.outputData.additionalFields;
                
                fields.forEach((field) => {
                  let sidebarBlockAdditionaFieldsElement = document.querySelector('[data-element="aside-block-additional-fields"]');
                  if (sidebarBlockAdditionaFieldsElement !== null) {
                    
                    let fieldsArrayElements = sidebarBlockAdditionaFieldsElement.querySelectorAll('[data-element="additional-field"]');
                    fieldsArrayElements.forEach((element) => {
                      let fieldInputElement = element.querySelector('input, textarea');

                      if (fieldInputElement.getAttribute('data-category-id') === interactiveCategoriesChoices.target.getValue()) {
                        element.style.display = 'block';
                      } else {
                        element.style.display = 'none';
                      }
                    });
                  }
                });
              });
            });

            fetch('/handler/entries/additional-fields?locale=' + this.page.core.locales.admin.name + '&localeMessage=' + this.page.core.locales.admin.name, {method: 'GET'}).then((response) => {
              return (response.ok) ? response.json() : Promise.reject(response);
            }).then((responseEntryAdditionalFields) => {
              let fields = responseEntryAdditionalFields.outputData.additionalFields;
              
              fields.forEach((field) => {
                let sidebarBlockAdditionaFieldsElement = document.querySelector('[data-element="aside-block-additional-fields"]');
                if (sidebarBlockAdditionaFieldsElement !== null) {
                  
                  let fieldsArrayElements = sidebarBlockAdditionaFieldsElement.querySelectorAll('[data-element="additional-field"]');
                  fieldsArrayElements.forEach((element) => {
                    let fieldInputElement = element.querySelector('input, textarea');

                    if (fieldInputElement.getAttribute('data-category-id') === interactiveCategoriesChoices.target.getValue()) {
                      element.style.display = 'block';
                    } else {
                      element.style.display = 'none';
                    }
                  });
                }
              });
            });
          }
        });

        this.buttons.viewOnSite.target.element.style.display = 'none';
        this.buttons.unpublish.target.element.style.display = 'none';
        this.buttons.publish.target.element.style.display = 'none';
        this.buttons.delete.target.element.style.display = 'none';
        this.buttons.save.target.element.style.display = 'flex';
        this.buttons.SEOAnalyze.target.element.style.display = 'flex';
      } else {
        const interactiveButtonPreviewUpload = new Interactive('button');

        const previewBlockElement = document.querySelector('[data-element="aside-block-cover"]');
        const previewBlockContentContainerElement = previewBlockElement.querySelector('.page-aside__block-content');
        const previewBlockPanelContainerElement = previewBlockElement.querySelector('.page-aside__block-panel');
        
        const previewFormElement = document.createElement('form');
        previewFormElement.setAttribute('formmethod', 'PATCH');
        previewFormElement.classList.add('form');
        previewFormElement.classList.add('form-entry-preview');

        const previewFormInputFileElement = document.createElement('input');
        previewFormInputFileElement.setAttribute('type', 'file');
        previewFormInputFileElement.setAttribute('name', 'entry_preview');
        previewFormInputFileElement.style.display = 'none';

        previewFormInputFileElement.addEventListener('change', (event) => {
          event.preventDefault();

          let file = event.target.files[0], fileReader = new FileReader();

          if (!fileReader) {
            console.error(`[CMSCore] ${localeData.REPORT_JS_CMSCORE_ERROR_FILEREADER_IS_NOT_SUPPORTED}.`);
            return;
          }

          if (event.target.files.length === 0) {
            console.error(`[CMSCore] ${localeData.REPORT_JS_CMSCORE_ERROR_IMAGES_WHERE_NOT_LOADED}.`);
            return;
          }

          fileReader.onload = (event) => {
            let imageElement = document.createElement('img');
            imageElement.setAttribute('src', fileReader.result);
            imageElement.style.width = '100%';
            previewImageContainerElement.innerHTML = '';
            previewImageContainerElement.appendChild(imageElement);
    
            let formData = new FormData();
            formData.append('entry_event_save', true);
            formData.append('entry_id', searchParams.getPathPart(3));
            formData.append('entry_preview', fileReader.result);

            let request = new Interactive('request', {
              method: 'PATCH',
              url: '/handler/entry?localeMessage=' + this.page.core.locales.admin.name
            });
    
            request.target.data = formData;
            request.target.send();
          };

          fileReader.onerror = (event) => {
            console.error(fileReader.result);
          };

          fileReader.readAsDataURL(file);
        });

        interactiveButtonPreviewUpload.target.setLabel(localeData.BUTTON_UPLOAD_COVER_LABEL);
        interactiveButtonPreviewUpload.target.setCallback((event) => {
          event.preventDefault();
          previewFormInputFileElement.click();
        });
        interactiveButtonPreviewUpload.assembly();

        let previewImageContainerElement = document.createElement('div');
        previewImageContainerElement.classList.add('form-entry-preview__container-image');

        let entryData;

        fetch('/handler/entry/' + searchParams.getPathPart(3) + '?localeMessage=' + this.page.core.locales.admin.name, {
          method: 'GET'
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          if (data1.statusCode === 1) {
            entryData = data1.outputData.entry;
            
            if (entryData.previewURL !== '') {
              let imageElement = document.createElement('img');
              imageElement.setAttribute('src', entryData.previewURL);
              imageElement.style.width = '100%';

              previewImageContainerElement.innerHTML = '';
              previewImageContainerElement.appendChild(imageElement);
            }

            previewFormElement.appendChild(previewFormInputFileElement);
            previewFormElement.appendChild(interactiveButtonPreviewUpload.target.element);
            previewBlockContentContainerElement.appendChild(previewImageContainerElement);
            previewBlockPanelContainerElement.appendChild(previewFormElement);

            this.buttons.viewOnSite.target.element.style.display = 'flex';
            this.buttons.unpublish.target.element.style.display = (entryData.isPublished) ? 'flex' : 'none';
            this.buttons.publish.target.element.style.display = (entryData.isPublished) ? 'none' : 'flex';
            this.buttons.delete.target.element.style.display = 'flex';
            this.buttons.save.target.element.style.display = 'flex';
            this.buttons.SEOAnalyze.target.element.style.display = 'flex';
          } else {
            this.buttons.viewOnSite.target.element.style.display = 'none';
            this.buttons.unpublish.target.element.style.display = 'none';
            this.buttons.publish.target.element.style.display = 'none';
            this.buttons.delete.target.element.style.display = 'none';
            this.buttons.save.target.element.style.display = 'flex';
            this.buttons.SEOAnalyze.target.element.style.display = 'flex';
          }
          
          return fetch('/handler/entry/categories' + '?locale=' + this.page.core.locales.admin.name + '&localeMessage=' + this.page.core.locales.admin.name, {method: 'GET'});
        }, (rejectionReason) => {
          this.page.showPopupNotification(rejectionReason, 0);
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          if (data1.statusCode === 1) {
            let entriesCategories = data1.outputData.entriesCategories;
            
            entriesCategories.forEach((entriesCategory, entriesCategoryIndex) => {
              interactiveCategoriesChoices.target.addItem(entriesCategory.title, entriesCategory.id);
            });

            if (this.statusCode !== 404) {
              entriesCategories.forEach((entriesCategory, entriesCategoryIndex) => {
                if (entriesCategory.id === entryData.categoryID) {
                  interactiveCategoriesChoices.target.setItemSelectedIndex(entriesCategoryIndex);
                }
              });
            }
            
            interactiveCategoriesChoices.target.setName('entry_category_id');
            interactiveCategoriesChoices.assembly();
    
            const interactiveContainer = document.querySelector('[data-element="choice"][data-choice="category"]');
            interactiveContainer.append(interactiveCategoriesChoices.target.element);
            interactiveHeaderContainerElement.append(this.buttons.viewOnSite.target.element);

            const interactiveCategoriesChoicesSelectElement = interactiveCategoriesChoices.target.element.querySelector('select');
            interactiveCategoriesChoicesSelectElement.addEventListener('change', (event) => {
              fetch('/handler/entries/additional-fields?locale=' + this.page.core.locales.admin.name + '&localeMessage=' + this.page.core.locales.admin.name, {method: 'GET'}).then((response) => {
                return (response.ok) ? response.json() : Promise.reject(response);
              }).then((responseEntryAdditionalFields) => {
                let fields = responseEntryAdditionalFields.outputData.additionalFields;
                
                fields.forEach((field) => {
                  let sidebarBlockAdditionaFieldsElement = document.querySelector('[data-element="aside-block-additional-fields"]');
                  if (sidebarBlockAdditionaFieldsElement !== null) {
                    
                    let fieldsArrayElements = sidebarBlockAdditionaFieldsElement.querySelectorAll('[data-element="additional-field"]');
                    fieldsArrayElements.forEach((element) => {
                      let fieldInputElement = element.querySelector('input, textarea');

                      if (fieldInputElement.getAttribute('data-category-id') === interactiveCategoriesChoices.target.getValue()) {
                        element.style.display = 'block';
                      } else {
                        element.style.display = 'none';
                      }
                    });
                  }
                });
              });
            });

            fetch('/handler/entries/additional-fields?locale=' + this.page.core.locales.admin.name + '&localeMessage=' + this.page.core.locales.admin.name, {method: 'GET'}).then((response) => {
              return (response.ok) ? response.json() : Promise.reject(response);
            }).then((responseEntryAdditionalFields) => {
              let fields = responseEntryAdditionalFields.outputData.additionalFields;
              
              fields.forEach((field) => {
                let sidebarBlockAdditionaFieldsElement = document.querySelector('[data-element="aside-block-additional-fields"]');
                if (sidebarBlockAdditionaFieldsElement !== null) {
                  
                  let fieldsArrayElements = sidebarBlockAdditionaFieldsElement.querySelectorAll('[data-element="additional-field"]');
                  fieldsArrayElements.forEach((element) => {
                    let fieldInputElement = element.querySelector('input, textarea');

                    if (fieldInputElement.getAttribute('data-category-id') === interactiveCategoriesChoices.target.getValue()) {
                      element.style.display = 'block';
                    } else {
                      element.style.display = 'none';
                    }
                  });
                }
              });
            });
          }
        }, (rejectionReason) => {
          this.page.showPopupNotification(rejectionReason, 0);
        });
      }

      const interactiveFooterContainer = document.querySelector('[data-element="panel"]');
      const sidebarSEOAnalyzerBlockPanelElement = document.querySelector('[data-element="aside-block-seo-analyzer"] .page-aside__block-panel');
      interactiveFooterContainer.append(this.buttons.delete.target.element);
      interactiveFooterContainer.append(this.buttons.unpublish.target.element);
      interactiveFooterContainer.append(this.buttons.publish.target.element);
      interactiveFooterContainer.append(this.buttons.save.target.element);
      sidebarSEOAnalyzerBlockPanelElement.append(this.buttons.SEOAnalyze.target.element);
    });
  }
}