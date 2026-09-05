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
    this.bracketMap = {
      '(': ')',
      '[': ']',
      '{': '}',
      '"': '"',
      "'": "'",
      '`': '`',
      '«': '»',
      '„': '“'
    };

    this.editor.textarea = this;
    console.log(`[NADVO TE] Object textarea created.`);
  }

  init() {
    this.element = this.editor.createElementTextarea();
    this.element.classList.add('nadvo-te__textarea');
    
    this.element.addEventListener('keydown', (event) => {
      const key = event.key;

      if (this.bracketMap[key] && !event.ctrlKey && !event.altKey && !event.metaKey) {
        const start = this.element.selectionStart;
        const end = this.element.selectionEnd;

        if (start !== end) {
          event.preventDefault();
          
          const value = this.element.value;
          const selectedText = value.substring(start, end);
          const closingBracket = this.bracketMap[key];
            
          this.element.value = value.substring(0, start) + key + selectedText + closingBracket + value.substring(end);
            
          const newCursorPos = end + (key === closingBracket ? 1 : 2);
          this.element.setSelectionRange(newCursorPos, newCursorPos);
          
          this.element.focus();
        }
      };
    });
  }

  insertStringAtCursor(string) {
    const start = this.element.selectionStart;
    const end = this.element.selectionEnd;

    this.element.value = this.element.value.substring(0, start)
      + string
      + this.element.value.substring(end);

    const cursorPos = start + string.length;

    this.element.focus();
    this.element.setSelectionRange(cursorPos, cursorPos);

    return true;
  }

  replaceStringSelection(string) {
    const start = this.element.selectionStart;
    const end = this.element.selectionEnd;

    if (start === end) {
      return false;
    }

    this.element.value = this.element.value.substring(0, start)
      + string
      + this.element.value.substring(end, this.element.value.length);

    this.element.setSelectionRange(
      start,
      start + string.length
    );

    this.element.focus();

    return true;
  }
}