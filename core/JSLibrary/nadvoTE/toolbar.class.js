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

import {Interactive} from '../interactive.class.js';
import {ToolBold} from './toolbar/toolBold.class.js';
import {ToolItalic} from './toolbar/toolItalic.class.js';
import {ToolUnderline} from './toolbar/toolUnderline.class.js';
import {ToolHeader} from './toolbar/toolHeader.class.js';
import {ToolHeaders} from './toolbar/toolHeaders.class.js';
import {ToolQuote} from './toolbar/toolQuote.class.js';
import {ToolPreview} from './toolbar/toolPreview.class.js';
import {ToolSource} from './toolbar/toolSource.class.js';
import {ToolLink} from './toolbar/toolLink.class.js';
import {ToolImage} from './toolbar/toolImage.class.js';
import {ToolCode} from './toolbar/toolCode.class.js';

export class Toolbar {
  constructor(editor, options = []) {
    this.editor = editor;
    this.options = options;
    this.tools = {};

    this.editor.toolbar = this;
    console.log(`[NADVO TE] Object toolbar created.`);
  }

  init() {
    const toolbarElement = this.editor.createElementUl();
    toolbarElement.classList.add('nadvo-te__toolbar-list');

    if (typeof(this.options) != 'undefined') {
      for (let optionItem of this.options) {
        const optionItemElement = this.editor.createElementLi();
        optionItemElement.classList.add('nadvo-te__toolbar-item');
        optionItemElement.classList.add('nadvo-te__toolbar-item_' + optionItem.name);
        
        let optionItemInteractiveElement;
        if (optionItem.type == 'button') {
          const toolButton = new Interactive('button');
          toolButton.target.setLabel('Включить');
          toolButton.target.setCallback(() => {});
          toolButton.assembly();

          optionItemInteractiveElement = toolButton.target.element;
          optionItemInteractiveElement.firstChild.classList.add('nadvo-te__toolbar-button');
        }

        if (optionItem.type == 'choices') {
          const toolChoices = new Interactive('choices');

          if (optionItem.name === 'headers') {
            [1, 2, 3, 4, 5, 6].forEach((headerLevelID, headerLevelIndex) => {
              let labelElement = document.createElement('span');
              labelElement.innerText = this.editor.localeData.NTE_TOOL_HEADER_COMMON_LABEL + ' ' + headerLevelID;
              labelElement.style.fontSize = 18 - headerLevelIndex + 'px';
              toolChoices.target.addItem(labelElement.outerHTML, headerLevelID);
            });
          }

          toolChoices.assembly();

          toolChoices.target.element.classList.add('nadvo-te__toolbar-item');
          toolChoices.target.element.classList.add('nadvo-te__toolbar-item_' + optionItem.name);
          
          optionItemInteractiveElement = toolChoices.target.element;
          optionItemInteractiveElement.firstChild.classList.add('nadvo-te__toolbar-choices');
        }
        
        switch (optionItem.name) {
          case 'bold': this.tools.bold = new ToolBold(this.editor, optionItemInteractiveElement); break;
          case 'italic': this.tools.italic = new ToolItalic(this.editor, optionItemInteractiveElement); break;
          case 'underline': this.tools.underline = new ToolUnderline(this.editor, optionItemInteractiveElement); break;
          case 'headers': this.tools.headers = new ToolHeaders(this.editor, optionItemInteractiveElement); break;
          case 'header1': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 1); break;
          case 'header2': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 2); break;
          case 'header3': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 3); break;
          case 'header4': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 4); break;
          case 'header5': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 5); break;
          case 'header6': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 6); break;
          case 'quote': this.tools.header = new ToolQuote(this.editor, optionItemInteractiveElement); break;
          case 'code': this.tools.source = new ToolCode(this.editor, optionItemInteractiveElement); break;
          case 'preview': this.tools.preview = new ToolPreview(this.editor, optionItemInteractiveElement); break;
          case 'source': this.tools.source = new ToolSource(this.editor, optionItemInteractiveElement); break;
          case 'link': this.tools.link = new ToolLink(this.editor, optionItemInteractiveElement); break;
          case 'image': this.tools.image = new ToolImage(this.editor, optionItemInteractiveElement); break;
        }

        toolbarElement.appendChild(optionItemElement);
        optionItemElement.appendChild(optionItemInteractiveElement);
      }
    }

    this.element = toolbarElement;
  }
}