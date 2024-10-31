/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from "../../core/JSLibrary/interactive.class.js";

export class Core {
  constructor(CMSCore) {
    this.CMSCore = CMSCore;
  }

  init() {
    // ...
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.CMSCore.addEventListener('ready', () => {
    window.CMSCore.templateCore = new Core(window.CMSCore);
    window.CMSCore.templateCore.init();
  });
});