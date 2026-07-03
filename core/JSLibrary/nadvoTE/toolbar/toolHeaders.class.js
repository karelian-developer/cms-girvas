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

    this.savedSelection = {
      text: '',
      start: 0,
      end: 0
    };
  }

  initClickEvent() {
    const selectElement = this.element.querySelector('select');
    const textarea = this.editor.textarea.element;
    
    // Сохраняем выделение при клике на select (срабатывает до потери фокуса)
    selectElement.addEventListener('mousedown', () => {
      this.savedSelection.text = textarea.value.substring(
        textarea.selectionStart, 
        textarea.selectionEnd
      );
      this.savedSelection.start = textarea.selectionStart;
      this.savedSelection.end = textarea.selectionEnd;
    });
    
    // При изменении select используем сохранённое выделение
    selectElement.addEventListener('change', (event) => {
      event.preventDefault();
      
      const headerLevel = selectElement.value;
      
      if (headerLevel && this.savedSelection.text) {
        // Возвращаем фокус и восстанавливаем выделение
        textarea.focus();
        textarea.setSelectionRange(
          this.savedSelection.start, 
          this.savedSelection.end
        );
        
        // Вставляем заголовок
        this.editor.textarea.replaceStringSelection(
          '#'.repeat(headerLevel) + ' ' + this.savedSelection.text
        );
        
        // Сбрасываем select
        selectElement.selectedIndex = 0;
        
        // Очищаем сохранённое
        this.savedSelection.text = '';
      }
    });
  }
}