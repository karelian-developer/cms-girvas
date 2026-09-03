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

    this.initClickEvent();
  }

  initClickEvent() {
    super.addChangeEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} selected!`);

      const selectElement = this.element.querySelector('select');
      const selection = this.editor.getSelectionString();

      if (!selection) {
        return;
      }

      const textarea = this.editor.textarea.element;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;

      const before = textarea.value.substring(0, start);
      const after = textarea.value.substring(end);

      const beforeEndsWithNewLine = before.endsWith('\n') || before.endsWith('\r');
      const afterStartsWithNewLine = after.startsWith('\n') || after.startsWith('\r');

      let prefix = '';
      let suffix = '';

      if (!beforeEndsWithNewLine) {
        prefix = '\n\r';
      }

      if (!afterStartsWithNewLine) {
        suffix = '\n\r';
      }

      const header = '#'.repeat(selectElement.value) + ' ' + selection;

      this.editor.textarea.replaceStringSelection(
        prefix + header + suffix
      );

      this.editor.clearSelection();
    });
  }
}