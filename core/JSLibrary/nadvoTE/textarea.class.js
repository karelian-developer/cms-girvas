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