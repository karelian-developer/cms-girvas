/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from '../interactive.class.js';
import {ToolBold} from './toolbar/toolBold.class.js';
import {ToolItalic} from './toolbar/toolItalic.class.js';
import {ToolUnderline} from './toolbar/toolUnderline.class.js';
import {ToolHeader} from './toolbar/toolHeader.class.js';
import {ToolQuote} from './toolbar/toolQuote.class.js';
import {ToolPreview} from './toolbar/toolPreview.class.js';
import {ToolSource} from './toolbar/toolSource.class.js';
import {ToolLink} from './toolbar/toolLink.class.js';
import {ToolImage} from './toolbar/toolImage.class.js';

export class Toolbar {
  constructor(editor, options = []) {
    this.editor = editor;
    this.options = options;
    this.tools = {};

    this.editor.toolbar = this;
    console.log(`[NADVO TE] Object toolbar created.`);
  }

  init() {
    let toolbarElement = this.editor.createElementUl();
    toolbarElement.classList.add('nadvo-te__toolbar-list');

    if (typeof(this.options) != 'undefined') {
      for (let optionItem of this.options) {
        let optionItemElement = this.editor.createElementLi();
        optionItemElement.classList.add('nadvo-te__toolbar-item');
        optionItemElement.classList.add('nadvo-te__toolbar-item_' + optionItem.name);
        
        let optionItemInteractiveElement;
        if (optionItem.type == 'button') {
          let buttonTest = new Interactive('button');
          buttonTest.target.setLabel('Включить');
          buttonTest.target.setCallback(() => {});
          buttonTest.assembly();

          optionItemInteractiveElement = buttonTest.target.element;
          optionItemInteractiveElement.firstChild.classList.add('nadvo-te__toolbar-button');
        }
        
        switch (optionItem.name) {
          case 'bold': this.tools.bold = new ToolBold(this.editor, optionItemInteractiveElement); break;
          case 'italic': this.tools.italic = new ToolItalic(this.editor, optionItemInteractiveElement); break;
          case 'underline': this.tools.underline = new ToolUnderline(this.editor, optionItemInteractiveElement); break;
          case 'header1': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 1); break;
          case 'header2': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 2); break;
          case 'header3': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 3); break;
          case 'header4': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 4); break;
          case 'header5': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 5); break;
          case 'header6': this.tools.header = new ToolHeader(this.editor, optionItemInteractiveElement, 6); break;
          case 'quote': this.tools.header = new ToolQuote(this.editor, optionItemInteractiveElement); break;
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