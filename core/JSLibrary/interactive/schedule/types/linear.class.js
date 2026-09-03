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

import {DataDot} from '../dataDot.class.js';

export class Linear {
  constructor(schedule) {
    this.schedule = schedule;
    this.data = [];
    this.color = '#000000';
    this.label = `Data #${schedule.types.length + 1}`;
  }

  setLabel(value) {
    this.label = value;
  }

  getLabel() {
    return this.label;
  }

  setColor(value) {
    this.color = value;
  }

  addData(data) {
    this.data.push(data);
  }

  buildData(dataTotalCount) {
    this.dataBuckup = this.data;
    this.data = [];

    for (let i = 0; i < dataTotalCount; i++) {
      for (let data of this.dataBuckup) {
        if (data.x == i) {
          this.data[i] = new DataDot(i, data.y);
        }
      }
    }

    for (let i = 0; i < dataTotalCount; i++) {
      if (typeof(this.data[i]) == 'undefined') {
        this.data[i] = new DataDot(i, 0);
      }
    }

    this.dataBuckup = [];
  }

  getMaxYData() {
    let maxData = {x: 0, y: 0};
    for (let data of this.data) {
      if (data.y > maxData.y) {
        maxData = data;
      }
    }
    return maxData.y;
  }

  getMaxXData() {
    let maxData = {x: 0, y: 0};
    for (let data of this.data) {
      if (data.x > maxData.x) {
        maxData = data;
      }
    }
    return maxData.x;
  }

  /**
   * Получить данные с учётом зума (видимая область)
   * @returns {Array} Массив точек для отображения
   */
  getVisibleData() {
    const schedule = this.schedule;
    if (!schedule || !schedule.zoom) return this.data;

    const totalDays = schedule.getDaysCountInCurrentMonth();
    const viewStart = schedule.zoom.viewStart || 0;
    const viewEnd = schedule.zoom.viewEnd || 1;

    const startIdx = Math.floor(viewStart * totalDays);
    const endIdx = Math.ceil(viewEnd * totalDays);

    const result = [];
    for (let i = startIdx; i < endIdx && i < this.data.length; i++) {
      if (this.data[i]) {
        result.push(this.data[i]);
      }
    }
    return result;
  }

  render(schedule) {
    if (typeof(schedule) != 'object') return;
    if (typeof(schedule.context) != 'object' || this.data.length === 0) return;

    const totalDays = schedule.getDaysCountInCurrentMonth();
    const viewStart = schedule.zoom.viewStart || 0;
    const viewEnd = schedule.zoom.viewEnd || 1;
    const visibleDays = Math.max(1, (viewEnd - viewStart) * totalDays);
    
    const maxY = schedule.getMaxYData() || 1;
    const lineXStep = schedule.getFrameSize().width / visibleDays;
    const lineYStep = schedule.getFrameSize().height / maxY;

    const frameX = schedule.getFramePosition().x;
    const frameY = schedule.getFramePosition().y;
    const frameHeight = schedule.getFrameSize().height;

    // 1. Собираем точки для отображения
    const visibleData = [];
    const startDay = Math.floor(viewStart * totalDays);
    const endDay = Math.ceil(viewEnd * totalDays);
    
    for (let day = startDay; day < endDay && day < this.data.length; day++) {
      if (this.data[day]) {
        visibleData.push(this.data[day]);
      } else {
        visibleData.push(new DataDot(day, 0));
      }
    }

    if (visibleData.length < 2) {
      schedule.context.fillStyle = '#999';
      schedule.context.font = '14px sans-serif';
      schedule.context.textAlign = 'center';
      schedule.context.textBaseline = 'middle';
      schedule.context.fillText(
        'Нет данных для отображения',
        frameX + schedule.getFrameSize().width / 2,
        frameY + schedule.getFrameSize().height / 2
      );
      return;
    }

    // 2. Рисуем линию
    schedule.context.strokeStyle = this.color;
    schedule.context.lineWidth = 2;
    schedule.context.beginPath();

    let isFirst = true;
    for (let i = 0; i < visibleData.length; i++) {
      const data = visibleData[i];
      if (!data) continue;

      const xPos = (data.x - viewStart * totalDays) * lineXStep;
      const yPos = data.y * lineYStep;

      const x = frameX + xPos;
      const y = frameY + frameHeight - yPos;

      if (isFirst) {
        schedule.context.moveTo(x, y);
        isFirst = false;
      } else {
        schedule.context.lineTo(x, y);
      }
    }

    // ==========================================
    // 3. ДОСТРАИВАЕМ ЛИНИЮ ДО КОНЦА ПОСЛЕДНЕГО ДНЯ
    // ==========================================
    const lastData = visibleData[visibleData.length - 1];
    const lastDayOfMonth = totalDays - 1;

    if (lastData && lastData.x < lastDayOfMonth) {
      const lastXPos = (lastData.x - viewStart * totalDays) * lineXStep;
      const lastYPos = lastData.y * lineYStep;
      const lastX = frameX + lastXPos;
      const lastY = frameY + frameHeight - lastYPos;
      
      // ==========================================
      // ИСПРАВЛЕНИЕ: достраиваем до КОНЦА ячейки последнего дня
      // ==========================================
      const endDayPos = (lastDayOfMonth + 1 - viewStart * totalDays) * lineXStep;
      const endX = frameX + endDayPos;
      const endY = lastY;
      
      schedule.context.lineTo(endX, endY);
    }

    schedule.context.stroke();
    schedule.context.lineWidth = 1;

    // 4. Рисуем точки (только для существующих данных)
    for (let data of visibleData) {
      if (!data) continue;
      
      const xPos = (data.x - viewStart * totalDays) * lineXStep;
      const yPos = data.y * lineYStep;

      const dot = {
        x: frameX + xPos - 6,
        y: frameY + frameHeight - yPos - 6
      };

      // Проверка коллизии с мышью
      if (schedule.mouse.x >= dot.x && schedule.mouse.x <= dot.x + 12 && 
          schedule.mouse.y >= dot.y && schedule.mouse.y <= dot.y + 12) {
        data.collision = true;
      } else {
        data.collision = false;
      }

      if (data.collision) {
        schedule.context.strokeStyle = '#232323';
        schedule.context.fillStyle = '#FFFFFF';
        schedule.context.beginPath();
        schedule.context.rect(dot.x + 3, dot.y + 3, 6, 6);
        schedule.context.fill();
        schedule.context.stroke();
      }
    }
  }
}