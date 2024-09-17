/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from "../interactive.class.js";
import {InstallationMaster} from "./installationMaster.class.js";

document.addEventListener('DOMContentLoaded', () => {
  let installationPagesElements = document.querySelectorAll('[data-page-index]');
  let interactiveChoicesDMSSelector = document.querySelector('[role="cms-dms-selector"]');

  let installationMaster = new InstallationMaster(installationPagesElements.length);
  
  installationMaster.buildPanel();
});