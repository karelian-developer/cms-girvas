/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

export class TextareaVisual {
  constructor(editor, options = []) {
    this.editor = editor;
    this.options = options;

    this.editor.textareaVisual = this;
    console.log(`[NADVO TE] Object textarea visual created.`);
  }

  init() {
    this.element = this.editor.createElementDiv();
    this.element.classList.add('nadvo-te__textarea-visual');

    let iFrameElement = this.editor.createElementIFrame();

    this.editor.fetchJSON('/handler/template?categoryName=base', {method: 'GET'}).then((data) => {
      console.log(data);
    });

    this.element.append(iFrameElement);
  }
}