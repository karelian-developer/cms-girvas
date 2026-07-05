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

import {Toolbar} from './nadvoTE/toolbar.class.js';
import {Textarea} from './nadvoTE/textarea.class.js';
import {TextareaVisual} from './nadvoTE/textareaVisual.class.js';

export class NadvoTE {
  constructor(element, options = {}) {
    this.element = element;
    this.options = options;
    this.localeData = {};
    this.selection = '';
    console.log(`[NADVO TE] Object created.`);
  }

  init() {
    this.options.locale.then((localeData) => {
      this.localeData = localeData;
      console.log(this.localeData);

      this.element.classList.add('nadvo-te');
      this.initEditorTextarea();
      this.initEditorToolbar();
      this.initEditorTextareaVisual();

      this.element.appendChild(this.toolbar.element);
      this.element.appendChild(this.textarea.element);
      this.element.appendChild(this.textareaVisual.element);

      // Сохраняем выделение при каждом выделении текста в textarea
      this.textarea.element.addEventListener('mouseup', () => {
        this.saveTextareaSelection();
      });
      
      // Для выделения с клавиатуры (Shift + стрелки)
      this.textarea.element.addEventListener('keyup', (e) => {
        if (e.shiftKey || e.key.startsWith('Arrow')) {
          this.saveTextareaSelection();
        }
      });

      const copyright = this.createElementDiv();
      copyright.classList.add('nadvo-te__copyright');
      copyright.innerHTML = 'Визуальный редактор &laquo;NadvoTE&raquo; разработан компанией &laquo;Карельский разработчик&raquo; специально для CMS &laquo;ГИРВАС&raquo;.';
      this.element.appendChild(copyright);
    });
  }

  // Новый метод для сохранения выделения
  saveTextareaSelection() {
    const textarea = this.textarea.element;
    
    // Сохраняем только если textarea в фокусе
    if (document.activeElement === textarea) {
      const selectedText = textarea.value.substring(
        textarea.selectionStart,
        textarea.selectionEnd
      );
      
      // Сохраняем только если есть выделенный текст
      if (selectedText) {
        this.selection = selectedText;
        console.log('[NADVO TE] Selection saved:', this.selection);
      }
    }
  }

  // Упрощённый метод получения выделения
  getSelectionString() {
    console.log('[NADVO TE] Get selection:', this.selection);
    return this.selection;
  }

  // Остальные методы без изменений...
  initEditorToolbar() {
    let toolbar = new Toolbar(this, this.options.toolbar);
    toolbar.init();
  }

  initEditorTextarea(element) {
    let textarea = new Textarea(this);
    textarea.init();
  }

  initEditorTextareaVisual(element) {
    let textareaVisual = new TextareaVisual(this);
    textareaVisual.init();
  }

  createElementTextarea() {
    return document.createElement('textarea');
  }

  createElementDiv() {
    return document.createElement('div');
  }

  createElementUl() {
    return document.createElement('ul');
  }

  createElementLi() {
    return document.createElement('li');
  }

  createElementIFrame() {
    return document.createElement('iframe');
  }

  createElementButton(content) {
    let element = document.createElement('button');
    element.innerHTML = content;
    return element;
  }

  async fetchJSON(url, data) {
    return fetch(url, data).then(response => response.ok ? response.json() : Promise.reject(response));
  }
}