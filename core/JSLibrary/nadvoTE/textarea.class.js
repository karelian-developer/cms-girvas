/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

export class Textarea {
  constructor(editor, options = []) {
    this.editor = editor;
    this.options = options;

    this.editor.textarea = this;
    console.log(`[NADVO TE] Object textarea created.`);
  }

  init() {
    this.element = this.editor.createElementTextarea();
    this.element.classList.add('nadvo-te__textarea');
  }

  replaceStringSelection(string) {
    if (this.element.selectionStart || this.element.selectionStart == '0') {
      let start = this.element.selectionStart, end = this.element.selectionEnd;
      this.element.value = this.element.value.substring(0, start) + string + this.element.value.substring(end, this.element.value.length);
    }
  }
}