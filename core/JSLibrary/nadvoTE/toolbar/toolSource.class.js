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

export class ToolSource extends Tool {
  constructor(editor, element) {
    super(editor, {
      name: 'source',
      type: 'button',
      iconPath: '/core/JSLibrary/nadvoTE/images/icons/toolbar/source.svg',
      element: element
    });

    this.initClickEvent();
  }

  initClickEvent() {
    super.addClickEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} clicked!`);
      
      if (this.editor.textareaVisual.element.classList.contains('nadvo-te__textarea-visual_is-showed')) {
        this.editor.textareaVisual.element.classList.remove('nadvo-te__textarea-visual_is-showed');
      }

      if (this.editor.textarea.element.classList.contains('nadvo-te__textarea_is-hidden')) {
        this.editor.textarea.element.classList.remove('nadvo-te__textarea_is-hidden');
      }
    });
  }
}