/**
 * Garbalo (https://www.garbalo.com/)
 * 
 * @copyright   Copyright (c) 2020 - 2024, Garbalo (https://www.garbalo.com/)
 */

'use strict';

import {Interactive} from "../../../core/JSLibrary/interactive.class.js";
import {InstallationMaster} from "../../../core/JSLibrary/install/installationMaster.class.js";

export class Core {
  constructor() {
    // ...
  }

  init() {
    // ...
  }
}

document.addEventListener('DOMContentLoaded', () => {
  let installationPagesElements = document.querySelectorAll('[data-page-index]');
  let installationMaster = new InstallationMaster(installationPagesElements.length);
    
  installationMaster.buildPanel();
});