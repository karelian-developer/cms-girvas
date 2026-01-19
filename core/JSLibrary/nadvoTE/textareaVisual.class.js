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
    this.element.classList.add('textarea-visual');

    let iFrameElement = this.editor.createElementIFrame();
    iFrameElement.classList.add('textarea-visual__iframe');
    iFrameElement.classList.add('iframe');

    this.editor.fetchJSON('/handler/template?categoryName=base', {method: 'GET'}).then((data) => {
      let templateName = data.outputData.template.name;
      let templateCategoryName = data.outputData.template.categoryName;
      let templateURL = (templateCategoryName == 'base') ? `/templates/${templateName}` : `/templates/${templateCategoryName}/${templateName}`;
      let iFrameTargetStylesURLs = [
        `/core/CSSCore/normalize.css`,
        `/core/CSSCore/default-colors-scheme.css`,
        `/core/CSSCore/default-base.css`,
        `/core/CSSCore/default-fonts.css`,
        `/core/CSSCore/default-tables.css`,
        `${templateURL}/styles/fonts.css`,
        `${templateURL}/styles/colors.css`,
        `${templateURL}/styles/nadvoTE/preview.css?_t=` + new Date().getTime()
      ];

      let iFrameElementDocument = iFrameElement.contentDocument || iFrameElement.contentWindow.document;

      iFrameTargetStylesURLs.forEach(url => {
        let linkElement = document.createElement('link');
        linkElement.rel = 'stylesheet';
        linkElement.href = url;

        iFrameElementDocument.head.appendChild(linkElement);
      });
    });

    this.element.append(iFrameElement);
  }
}