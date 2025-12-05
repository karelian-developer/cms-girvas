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