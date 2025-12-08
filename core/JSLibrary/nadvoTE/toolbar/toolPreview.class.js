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

export class ToolPreview extends Tool {
  constructor(editor, element) {
    super(editor, {
      name: 'preview',
      type: 'button',
      iconPath: '/core/JSLibrary/nadvoTE/images/icons/toolbar/preview.svg',
      element: element
    });

    this.initClickEvent();
  }

  resizePreviewIFrame(element) {
    let elementDocument = element.contentDocument || element.contentWindow.document;
    element.style.height = elementDocument.documentElement.scrollHeight + 'px';
  }

  initClickEvent() {
    super.addClickEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} clicked!`);
      
      if (typeof(this.editor.options.handler) != 'undefined') {
        let formData = new FormData();
        formData.append('markdown_text', this.editor.textarea.element.value);

        fetch(this.editor.options.handler, {
          method: 'POST',
          dataType: 'json',
          body: formData
        }).then((response) => {
          return response.json();
        }).then((data) => {
          let iFrameWrapperElement = this.editor.textareaVisual.element;
          let iFrameElement = iFrameWrapperElement.querySelector('iframe');
          iFrameElement.setAttribute('scrolling', 'no');

          let iFrameElementDocument = iFrameElement.contentDocument || iFrameElement.contentWindow.document;

          iFrameElementDocument.body.innerHTML = data.outputData.nadvoparse;
          this.resizePreviewIFrame(iFrameElement);
        }).catch((error) => {
          console.error(error);
        });
      }

      if (!this.editor.textareaVisual.element.classList.contains('nadvo-te__textarea-visual_is-showed')) {
        this.editor.textareaVisual.element.classList.add('nadvo-te__textarea-visual_is-showed');
      }

      if (!this.editor.textarea.element.classList.contains('nadvo-te__textarea_is-hidden')) {
        this.editor.textarea.element.classList.add('nadvo-te__textarea_is-hidden');
      }
    });
  }
}