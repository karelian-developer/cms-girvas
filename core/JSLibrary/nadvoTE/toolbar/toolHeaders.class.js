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
    this.selectionStart = 0;
    this.selectionEnd = 0;
  }

  initClickEvent() {
    const selectElement = this.element.querySelector('select');
    if (selectElement) {
      // Захватываем выделение ДО того, как select получит фокус
      selectElement.addEventListener('mousedown', (event) => {
        const textarea = this.editor.textarea.element;
        this.selectedText = textarea.value.substring(
          textarea.selectionStart,
          textarea.selectionEnd
        );
        this.selectionStart = textarea.selectionStart;
        this.selectionEnd = textarea.selectionEnd;
        
        console.log('[NADVO TE] Captured selection:', {
          text: this.selectedText,
          start: this.selectionStart,
          end: this.selectionEnd
        });
      });

      // Обрабатываем изменение выбора
      selectElement.addEventListener('change', (event) => {
        event.preventDefault();
        
        const headerLevel = parseInt(selectElement.value);
        
        // Проверяем, что уровень выбран и есть сохранённый текст
        if (headerLevel > 0 && this.selectedText) {
          console.log(`[NADVO TE] Tool ${this.name} selected! Level: ${headerLevel}`);
          
          // Возвращаем фокус в текстовое поле
          const textarea = this.editor.textarea.element;
          textarea.focus();
          
          // Восстанавливаем выделение
          textarea.setSelectionRange(this.selectionStart, this.selectionEnd);
          
          // Вставляем заголовок
          const newText = '#'.repeat(headerLevel) + ' ' + this.selectedText;
          this.editor.textarea.replaceStringSelection(newText);
          
          // Сбрасываем значение select
          selectElement.selectedIndex = 0;
          
          // Очищаем сохранённые данные
          this.selectedText = '';
          this.selectionStart = 0;
          this.selectionEnd = 0;
        }
      });
    }
  }
}