/**
 * Garbalo (https://www.garbalo.com/)
 * 
 * @copyright   Copyright (c) 2020 - 2024, Garbalo (https://www.garbalo.com/)
 */

'use strict';

import {Interactive} from "../../../core/JSLibrary/interactive.class.js";

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