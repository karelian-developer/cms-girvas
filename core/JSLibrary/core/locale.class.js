/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

export class Locale {
  constructor(name, dir = 'base') {
    this.name = name;
    this.corePath = `/locales/${name}`;
    this.dataPath = `/locales/${name}/${dir}`;
  }

  getMetadataURL() {
    return `${this.corePath}/metadata.json`;
  }

  getDataURL() {
    return `${this.dataPath}/data.json`;
  }

  async getMetadata() {
    return fetch(this.getMetadataURL(), {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    });
  }

  async getData() {
    return fetch(this.getDataURL(), {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    });
  }
}