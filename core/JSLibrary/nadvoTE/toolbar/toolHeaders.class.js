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

import {Tool} from './tool.class.js';

export class ToolHeaders extends Tool {
  constructor(editor, element) {
    super(editor, {
      name: 'headers',
      type: 'choices',
      iconPath: '',
      element: element
    });

    this.selectedText = '';
    this.initFocusHandlers();
  }

  initFocusHandlers() {
    this.editor.textarea.element.addEventListener('blur', () => {
      this.selectedText = this.editor.getSelectionString();
    });

    this.editor.textarea.element.addEventListener('mouseup', () => {
      this.selectedText = this.editor.getSelectionString();
    });

    this.editor.textarea.element.addEventListener('keyup', (e) => {
      if (e.key === 'Shift' || e.key.startsWith('Arrow')) {
        this.selectedText = this.editor.getSelectionString();
      }
    });
  }

  initClickEvent() {
    const selectElement = this.element.querySelector('select');
    if (selectElement) {
      selectElement.addEventListener('change', (event) => {
        event.preventDefault();
        
        this.editor.textarea.element.focus();
        
        const textToWrap = this.selectedText || this.editor.getSelectionString();
        
        if (textToWrap) {
          console.log(`[NADVO TE] Tool ${this.name} selected!`);
          this.editor.textarea.replaceStringSelection(
            '#'.repeat(selectElement.value) + ' ' + textToWrap
          );
        }
        
        selectElement.selectedIndex = 0;
        
        this.selectedText = '';
      });

      selectElement.addEventListener('mousedown', (event) => {
        this.selectedText = this.editor.getSelectionString();
      });
    }
  }
}