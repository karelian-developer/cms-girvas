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

    this.history = [];
    this.historyIndex = -1;
    this.maxHistory = 100;
    this.isRestoring = false;

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

      this.initTextareaEvents();
      this.saveHistory(true);

      const copyright = this.createElementDiv();
      copyright.classList.add('nadvo-te__copyright');
      copyright.innerText = this.localeData.NTE_COPYRIGHT;
      this.element.appendChild(copyright);
    });
  }

  initTextareaEvents() {
    const textarea = this.textarea.element;

    textarea.addEventListener('mouseup', () => {
      this.saveTextareaSelection();
      this.saveHistory();
    });

    textarea.addEventListener('keyup', (e) => {
      if (e.shiftKey || e.key.startsWith('Arrow')) {
        this.saveTextareaSelection();
      }

      this.saveHistory();
    });

    textarea.addEventListener('click', () => {
      if (textarea.selectionStart === textarea.selectionEnd) {
        this.clearSelection();
      }
    });

    textarea.addEventListener('input', () => {
      this.saveHistory();
    });
  }

  saveHistory(force = false) {
    if (this.isRestoring) {
      return;
    }

    const value = this.textarea?.element?.value;

    if (value === undefined) {
      return;
    }

    const lastValue = this.history[this.historyIndex];

    if (!force && lastValue === value) {
      return;
    }

    this.history = this.history.slice(0, this.historyIndex + 1);
    this.history.push(value);

    if (this.history.length > this.maxHistory) {
      this.history.shift();
    } else {
      this.historyIndex++;
    }
  }

  undo() {
    if (this.historyIndex <= 0) {
      return false;
    }

    this.historyIndex--;
    this.restoreHistory();

    return true;
  }

  redo() {
    if (this.historyIndex >= this.history.length - 1) {
      return false;
    }

    this.historyIndex++;
    this.restoreHistory();

    return true;
  }

  restoreHistory() {
    const value = this.history[this.historyIndex];

    if (value === undefined) {
      return;
    }

    this.isRestoring = true;

    this.textarea.element.value = value;

    const cursorPos = value.length;
    this.textarea.element.setSelectionRange(cursorPos, cursorPos);

    this.isRestoring = false;
    this.clearSelection();
  }
}