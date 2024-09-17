/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Locale} from './core/locale.class.js';
import {Page} from './core/page.class.js';
import {Client} from './core/client.class.js';
import {Metrics} from './core/metrics.class.js';
import {URLParser} from './urlParser.class.js';
import {Form as StaticForm} from './form.class.js';

export class Core {
  // ¯\_(ツ)_/¯
  static noneSmile = '¯\_(ツ)_/¯';

  /**
   * constructor
   */
  constructor() {
    this.searchParams = new URLParser();
    this.pages = {default: {}, admin: {}, install: {}};
    this.locales = {base: {}, admin: {}, install: {}};
    this.client = new Client(this);
    this.metrics = new Metrics();
    this.debugLevel = 1;
    this.isReady = false;

    this.callbacks = {
      ready: []
    };
  }

  addEventListener(event, callback) {
    if (this.callbacks.hasOwnProperty(event)) {
      this.callbacks[event].push(callback);
    }
  }

  /**
   * Инициализация локализаций
   * 
   * @returns
   */
  async initLocales() {
    return fetch('/handler/locales', {
      method: 'GET'
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      this.locales.list = data.outputData.locales;
      return fetch('/handler/locale/base', {method: 'GET'});
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      let cookieMatches = document.cookie.match(new RegExp('(^| )locale=([^;]+)'));
      
      if (cookieMatches != null) {
        this.locales.list.forEach(element => {
          if (element.name == cookieMatches[2]) {
            this.locales.base = new Locale(element.name, 'base');
          }
        });
      } else {
        this.locales.base = new Locale(data.outputData.locale.name, 'base');
      }

      return fetch('/handler/locale/admin', {method: 'GET'});
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      this.locales.admin = new Locale(data.outputData.locale.name, 'admin');
    });
  }
  
  /**
   * Инициализация страниц
   * 
   * @returns {Boolean}
   */
  async initPages() {
    let locale;

    return this.client.isLogged().then((clientIsLogged) => {
      if (this.searchParams.getPathPart(1) == 'entry') this.pages.default.entry = new Page(this, 'default', 'entry');
      if (this.searchParams.getPathPart(1) == 'profile') this.pages.default.profile = new Page(this, 'default', 'profile');
    
      if (this.searchParams.getPathPart(1) == 'admin') {
        if (clientIsLogged) {
          if (this.searchParams.getPathPart(2) == 'analytics') this.pages.admin.entry = new Page(this, 'admin', 'analytics');
          if (this.searchParams.getPathPart(2) == 'entry') this.pages.admin.entry = new Page(this, 'admin', 'entry');
          if (this.searchParams.getPathPart(2) == 'entries') this.pages.admin.entries = new Page(this, 'admin', 'entries');
          if (this.searchParams.getPathPart(2) == 'entriesCategory') this.pages.admin.entriesCategory = new Page(this, 'admin', 'entriesCategory');
          if (this.searchParams.getPathPart(2) == 'entriesCategories') this.pages.admin.entriesCategories = new Page(this, 'admin', 'entriesCategories');
          if (this.searchParams.getPathPart(2) == 'entriesComments') this.pages.admin.entriesComments = new Page(this, 'admin', 'entriesComments');
          if (this.searchParams.getPathPart(2) == 'pages') this.pages.admin.pages = new Page(this, 'admin', 'pages');
          if (this.searchParams.getPathPart(2) == 'page') this.pages.admin.pageStatic = new Page(this, 'admin', 'pageStatic');
          if (this.searchParams.getPathPart(2) == 'media') this.pages.admin.media = new Page(this, 'admin', 'media');
          if (this.searchParams.getPathPart(2) == 'module') this.pages.admin.module = new Page(this, 'admin', 'module');
          if (this.searchParams.getPathPart(2) == 'modules') this.pages.admin.modules = new Page(this, 'admin', 'modules');
          if (this.searchParams.getPathPart(2) == 'settings') this.pages.admin.settings = new Page(this, 'admin', 'settings');
          if (this.searchParams.getPathPart(2) == 'template') this.pages.admin.template = new Page(this, 'admin', 'template');
          if (this.searchParams.getPathPart(2) == 'templates') this.pages.admin.templates = new Page(this, 'admin', 'templates');
          if (this.searchParams.getPathPart(2) == 'user') this.pages.admin.user = new Page(this, 'admin', 'user');
          if (this.searchParams.getPathPart(2) == 'users') this.pages.admin.users = new Page(this, 'admin', 'users');
          if (this.searchParams.getPathPart(2) == 'userGroup') this.pages.admin.usersGroup = new Page(this, 'admin', 'usersGroup');
          if (this.searchParams.getPathPart(2) == 'usersGroups') this.pages.admin.usersGroups = new Page(this, 'admin', 'usersGroups');
          if (this.searchParams.getPathPart(2) == 'webChannel') this.pages.admin.webChannel = new Page(this, 'admin', 'webChannel');
          if (this.searchParams.getPathPart(2) == 'webChannels') this.pages.admin.webChannels = new Page(this, 'admin', 'webChannels');
        }

        this.pages.admin.global = new Page(this, 'admin', 'global');
      } else {
        this.pages.default.global = new Page(this, 'default', 'global');
      }

      if (this.pages.admin.hasOwnProperty('global')) {
        locale = this.locales.admin;
      }
  
      if (this.pages.default.hasOwnProperty('global')) {
        locale = this.locales.base;
      }
  
      if (this.pages.install.hasOwnProperty('global')) {
        locale = this.locales.install;
      }

      return locale.getData();
    }).then((localeData) => {
      this.localeData = localeData;

      return true;
    });
  }

  /**
   * Инициализация статических форм (встроенные через TPL)
   * 
   * @returns 
   */
  async initStaticForms() {
    let staticFormsArray = [], formsElements = document.querySelectorAll('form');
    let locale;

    if (this.pages.admin.hasOwnProperty('global')) {
      locale = this.locales.admin;
    }

    if (this.pages.default.hasOwnProperty('global')) {
      locale = this.locales.base;
    }

    if (this.pages.install.hasOwnProperty('global')) {
      locale = this.locales.install;
    }

    for (let element of formsElements) {
      if (!element.hasAttribute('coreignore')) {
        let staticForm = new StaticForm(element, locale);
        staticForm.initFormElement();

        staticFormsArray.push(staticForm);
      }
    }

    return staticFormsArray;
  }

  /**
   * Получение текущей версии CMS
   * 
   * @returns {String}
   */
  async getCMSVersion() {
    return fetch('/handler/utils/cms-version', {
      method: 'GET'
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      if (data.outputData.hasOwnProperty('result')) {
        return data.outputData.result;
      }

      return Core.noneSmile;
    });
  }

  /**
   * Получение наименования CMS
   * @returns {String}
   */
  async getCMSTitle() {
    return fetch('/handler/utils/cms-title', {
      method: 'GET'
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      if (data.outputData.hasOwnProperty('result')) {
        return data.outputData.result;
      }

      return Core.noneSmile;
    });
  }

  /**
   * Вывести сообщение в консоль
   * 
   * @param {Number} debugLevel
   * @param {String} label 
   * @param {String} string 
   * @param {Boolean} timestamp
   */
  debugLog(debugLevel, label, string, timestamp = false) {
    if (this.debugLevel >= debugLevel) {
      let stringResult = '';
      
      if (typeof(timestamp) == 'boolean') {
        if (timestamp) {
          let currentDate = new Date();
          stringResult += `[${currentDate.toGMTString()}] `;
        }
      }

      if (label != '' && label != null) stringResult += `[${label}] `;

      stringResult += string;

      console.log(stringResult);
    }
  }

  /**
   * Вывести ошибку в консоль
   * 
   * @param {Number} debugLevel 
   * @param {String} label 
   * @param {String} string 
   * @param {Boolean} timestamp
   */
  debugError(debugLevel, label, string, timestamp) {
    if (this.debugLevel >= debugLevel) {
      let stringResult = '';

      if (typeof(timestamp) == 'boolean') {
        if (timestamp) {
          let currentDate = new Date();
          stringResult += `[${currentDate.toGMTString()}] `;
        }
      }

      if (label != '' && label != null) stringResult += `[${label}] `;

      stringResult += string;

      console.error(stringResult);
    }
  }

  /**
   * Вывести примечание в консоль
   * 
   * @param {Number} debugLevel
   * @param {String} label
   * @param {String} string
   * @param {Boolean} timestamp
   */
  debugInfo(debugLevel, label, string, timestamp) {
    if (this.debugLevel >= debugLevel) {
      let stringResult = '';
      
      if (typeof(timestamp) == 'boolean') {
        if (timestamp) {
          let currentDate = new Date();
          stringResult += `[${currentDate.toGMTString()}] `;
        }
      }

      if (label != '' && label != null) stringResult += `[${label}] `;

      stringResult += string;

      console.info(stringResult);
    }
  }

  ready() {
    for (let method of this.callbacks.ready) {
      method();
    }

    window.CMSCore.debugLog(1, 'CMSCore', `Core CMS is ready!`, true);
  }
}

window.CMSCore = new Core();

// Инициализация клиентского ядра CMS
document.addEventListener('DOMContentLoaded', async () => {
  await window.CMSCore.initLocales().then(() => {
    window.CMSCore.debugLog(1, 'CMSCore', 'Locales initied!', true);
    return window.CMSCore.initPages();
  }).then(() => {
    window.CMSCore.debugLog(1, 'CMSCore', 'Pages initied!', true);
    return window.CMSCore.initStaticForms();
  }).then(() => {
    window.CMSCore.debugLog(1, 'CMSCore', 'Static forms initied!', true);
    return window.CMSCore.getCMSVersion();
  }).then((version) => {
    window.CMSCore.debugLog(1, 'CMSCore', `Core CMS initied! Current version: ${version}`, true);
  }).then(() => {
    window.CMSCore.ready();
  });
});