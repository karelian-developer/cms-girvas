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

export class ToolItalic extends Tool {
  constructor(editor, element) {
    super(editor, {
      name: 'italic',
      type: 'button',
      iconPath: '/core/JSLibrary/nadvoTE/images/icons/toolbar/italic.svg',
      element: element
    });

    this.initClickEvent();
    this.bindHotkey('Ctrl+I');
  }

  initClickEvent() {
    super.addClickEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} clicked!`);

      const selection = this.editor.getSelectionString();

      if (selection) {
        const inserted = this.editor.textarea.replaceStringSelection(
          '*' + selection + '*'
        );

        if (inserted) {
          this.editor.clearSelection();
        }
      }
    });
  }
}