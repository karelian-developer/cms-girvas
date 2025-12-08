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

export class Legend {
  constructor() {
    this.renderSize = {width: 0, height: 0};
    this.types = [];
  }

  addType(type) {
    this.types.push(type);
  }

  setRenderSize(width, height) {
    this.renderSize.width = width;
    this.renderSize.height = height;
  }

  getRenderSize() {
    return this.renderSize;
  }

  render(context, x, y) {
    if (typeof(context) == 'object') {

      let itemSize = {width: 60, height: 20};
      let itemY = (y + this.getRenderSize().height) - ((this.getRenderSize().height / 2) + itemSize.height);
      let itemLabelY = (y + (this.getRenderSize().height / 2)) - (itemSize.height / 2);
      
      context.strokeStyle = '#232323';

      for (let typeIndex = 0; typeIndex < this.types.length; typeIndex++) {
        context.fillStyle = this.types[typeIndex].color;

        context.beginPath();
        context.rect(x + ((itemSize.width + 80 + 10) * typeIndex), itemY, itemSize.width, itemSize.height);
        context.fill();
        context.stroke();

        context.strokeStyle  = '#232323';
        context.font = 'bold 12px serif';
        context.textBaseline = 'middle';
        context.textAlign = 'left';

        context.fillStyle = '#232323';

        context.fillText(
          this.types[typeIndex].getLabel(),
          (x + itemSize.width + 10) + ((itemSize.width + 10 + 80) * typeIndex),
          itemLabelY);
      }
    }
  }
}