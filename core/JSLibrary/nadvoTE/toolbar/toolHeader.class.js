/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Tool} from './tool.class.js';

export class ToolHeader extends Tool {
  constructor(editor, element, level) {
    super(editor, {
      name: 'header' + level,
      type: 'button',
      iconPath: `/core/JSLibrary/nadvoTE/images/icons/toolbar/header${level}.svg`,
      element: element
    });

    this.level = level;
    this.initClickEvent();
  }

  initClickEvent() {
    super.addClickEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} clicked!`);
      this.editor.textarea.replaceStringSelection(
        '#'.repeat(this.level) + ' ' + this.editor.getSelectionString()
      );
    });
  }
}