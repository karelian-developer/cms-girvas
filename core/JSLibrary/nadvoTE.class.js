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
    this.element.classList.add('nadvo-te');
    this.initEditorTextarea();
    this.initEditorTextareaVisual();

    this.element.appendChild(this.textarea.element);
    this.element.appendChild(this.textareaVisual.element);

    this.options.locale.getData().then((localeData) => {
      this.localeData = localeData;

      this.initEditorToolbar();

      this.element.prepend(this.toolbar.element);

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

      this.textarea.element.addEventListener('mousedown', () => {
        this.saveTextareaSelection();
      });

      const copyright = this.createElementDiv();
      copyright.classList.add('nadvo-te__copyright');
      copyright.innerText = this.localeData.NTE_COPYRIGHT;
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

  clearSelection() {
    this.selection = '';
    console.log('[NADVO TE] Selection cleared');
  }

  getSelectionString() {
    const textarea = this.textarea?.element;

    if (textarea) {
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;

      if (start !== end) {
        this.selection = textarea.value.substring(start, end);
      } else {
        this.selection = '';
      }
    }

    return this.selection;
  }

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