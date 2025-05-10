/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Toolbar} from './nadvoTE/toolbar.class.js';
import {Textarea} from './nadvoTE/textarea.class.js';
import {TextareaVisual} from './nadvoTE/textareaVisual.class.js';

export class NadvoTE {
  constructor(element, options = {}) {
    this.element = element;
    this.options = options;
    console.log(`[NADVO TE] Object created.`);
  }

  init() {
    this.element.classList.add('nadvo-te');
    this.initEditorTextarea();
    this.initEditorToolbar();
    this.initEditorTextareaVisual();

    this.element.appendChild(this.toolbar.element);
    this.element.appendChild(this.textarea.element);
    this.element.appendChild(this.textareaVisual.element);
  }

  initEditorToolbar() {
    let toolbar = new Toolbar(this, this.options.toolbar);
    toolbar.init();
  }

  initEditorTextarea(element) {
    let textarea = new Textarea(this);
    textarea.init();
  }

  initEditorTextareaVisual(element) {
    let textareaVisual = new TextareaVisual(this);
    textareaVisual.init();
  }

  createElementTextarea() {
    return document.createElement('textarea');
  }

  createElementDiv() {
    return document.createElement('div');
  }

  createElementUl() {
    return document.createElement('ul');
  }

  createElementLi() {
    return document.createElement('li');
  }

  createElementIFrame() {
    return document.createElement('iframe');
  }

  createElementButton(content) {
    let element = document.createElement('button');
    element.innerHTML = content;

    return element;
  }
  
  getSelectionString() {
    return window.getSelection().toString();
  }

  async fetchJSON(url, data) {
    return fetch(url, data).then(response => response.ok ? response.json() : Promise.reject(response));
  }
}