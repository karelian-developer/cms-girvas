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
    this._hoveredIndex = -1; // Индекс точки под мышью
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

    // Собираем видимые данные
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

    // ==========================================
    // 1. РИСУЕМ ЛИНИЮ
    // ==========================================
    schedule.context.strokeStyle = this.color;
    schedule.context.lineWidth = 2.5;
    schedule.context.beginPath();

    let isFirst = true;
    for (let i = 0; i < visibleData.length; i++) {
      const data = visibleData[i];
      if (!data) continue;

      const relativeX = data.x - viewStart * totalDays;
      const clampedX = Math.max(0, Math.min(visibleDays, relativeX));
      const xPos = clampedX * lineXStep;
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

    // Достраиваем до конца
    const lastData = visibleData[visibleData.length - 1];
    const lastDayOfMonth = totalDays - 1;

    if (lastData && lastData.x < lastDayOfMonth) {
      const relativeX = lastData.x - viewStart * totalDays;
      const clampedX = Math.max(0, Math.min(visibleDays, relativeX));
      const lastXPos = clampedX * lineXStep;
      const lastYPos = lastData.y * lineYStep;
      const lastX = frameX + lastXPos;
      const lastY = frameY + frameHeight - lastYPos;
      
      const endX = frameX + schedule.getFrameSize().width;
      schedule.context.lineTo(endX, lastY);
    }

    schedule.context.stroke();

    // ==========================================
    // 2. РИСУЕМ ТОЧКИ (кружки)
    // ==========================================
    const mouseX = schedule.mouse.x;
    const mouseY = schedule.mouse.y;
    let foundIndex = -1;
    let foundData = null;
    const hoverRadius = 8; // Радиус для детекции

    // Сначала ищем точку под мышью
    for (let i = 0; i < visibleData.length; i++) {
      const data = visibleData[i];
      if (!data) continue;

      const relativeX = data.x - viewStart * totalDays;
      const clampedX = Math.max(0, Math.min(visibleDays, relativeX));
      const xPos = clampedX * lineXStep;
      const yPos = data.y * lineYStep;

      const cx = frameX + xPos;
      const cy = frameY + frameHeight - yPos;

      const dx = mouseX - cx;
      const dy = mouseY - cy;
      
      if (dx * dx + dy * dy < hoverRadius * hoverRadius) {
        foundIndex = i;
        foundData = data;
        break;
      }
    }

    // Сохраняем найденную точку для тултипа
    this._hoveredIndex = foundIndex;

    // Рисуем все точки
    for (let i = 0; i < visibleData.length; i++) {
      const data = visibleData[i];
      if (!data) continue;

      const relativeX = data.x - viewStart * totalDays;
      const clampedX = Math.max(0, Math.min(visibleDays, relativeX));
      const xPos = clampedX * lineXStep;
      const yPos = data.y * lineYStep;

      const cx = frameX + xPos;
      const cy = frameY + frameHeight - yPos;
      
      // Размер точки: увеличиваем если под мышью
      const isHovered = (i === foundIndex);
      const radius = isHovered ? 7 : 4;
      const borderWidth = isHovered ? 3 : 1.5;

      // Тень для выделенной точки
      if (isHovered) {
        schedule.context.shadowColor = this.color;
        schedule.context.shadowBlur = 15;
      }

      // Заливка
      schedule.context.fillStyle = this.color;
      schedule.context.beginPath();
      schedule.context.arc(cx, cy, radius, 0, Math.PI * 2);
      schedule.context.fill();

      // Обводка (белая для контраста)
      schedule.context.shadowBlur = 0;
      schedule.context.strokeStyle = '#FFFFFF';
      schedule.context.lineWidth = borderWidth;
      schedule.context.beginPath();
      schedule.context.arc(cx, cy, radius, 0, Math.PI * 2);
      schedule.context.stroke();

      // Если точка под мышью — дополнительный внешний кружок
      if (isHovered) {
        schedule.context.strokeStyle = this.color;
        schedule.context.lineWidth = 2;
        schedule.context.setLineDash([4, 4]);
        schedule.context.beginPath();
        schedule.context.arc(cx, cy, radius + 6, 0, Math.PI * 2);
        schedule.context.stroke();
        schedule.context.setLineDash([]);
      }
    }

    // ==========================================
    // 3. РИСУЕМ ТУЛТИП
    // ==========================================
    if (foundData !== null) {
      const data = visibleData[foundIndex];
      const relativeX = data.x - viewStart * totalDays;
      const clampedX = Math.max(0, Math.min(visibleDays, relativeX));
      const xPos = clampedX * lineXStep;
      const yPos = data.y * lineYStep;

      const cx = frameX + xPos;
      const cy = frameY + frameHeight - yPos;

      // Формируем дату
      const dayNumber = data.x + 1;
      const monthName = this.getMonthName();

      // Определяем тип метрики
      const typeIndex = this.schedule.types.indexOf(this);
      const typeNames = ['Просмотры', 'Визиты', 'Посещения'];
      const metricName = typeNames[typeIndex] || this.label;

      // Текст тултипа
      const lines = [
        `${dayNumber} ${monthName}`,
        `${metricName}: ${data.y}`
      ];

      // Вычисляем размеры тултипа
      const fontSize = 13;
      const lineHeight = 20;
      const padding = 10;
      const borderRadius = 6;

      schedule.context.font = `${fontSize}px sans-serif`;
      schedule.context.textAlign = 'left';
      schedule.context.textBaseline = 'top';

      let maxWidth = 0;
      for (const line of lines) {
        const metrics = schedule.context.measureText(line);
        if (metrics.width > maxWidth) maxWidth = metrics.width;
      }

      const tooltipWidth = maxWidth + padding * 2;
      const tooltipHeight = lines.length * lineHeight + padding * 2;

      // Позиционируем тултип (смещаем вверх и вправо от точки)
      let tooltipX = cx + 15;
      let tooltipY = cy - tooltipHeight / 2;

      // Корректируем, чтобы не выходил за границы
      const canvasWidth = schedule.canvas.width;
      const canvasHeight = schedule.canvas.height;

      if (tooltipX + tooltipWidth > canvasWidth - 10) {
        tooltipX = cx - tooltipWidth - 15;
      }
      if (tooltipY < 10) {
        tooltipY = 10;
      }
      if (tooltipY + tooltipHeight > canvasHeight - 10) {
        tooltipY = canvasHeight - tooltipHeight - 10;
      }

      // Рисуем фон тултипа
      schedule.context.shadowBlur = 10;
      schedule.context.shadowColor = 'rgba(0, 0, 0, 0.15)';
      schedule.context.fillStyle = '#FFFFFF';
      schedule.context.strokeStyle = this.color;
      schedule.context.lineWidth = 2;
      
      // Скруглённый прямоугольник
      const r = borderRadius;
      const x = tooltipX;
      const y = tooltipY;
      const w = tooltipWidth;
      const h = tooltipHeight;

      schedule.context.beginPath();
      schedule.context.moveTo(x + r, y);
      schedule.context.lineTo(x + w - r, y);
      schedule.context.quadraticCurveTo(x + w, y, x + w, y + r);
      schedule.context.lineTo(x + w, y + h - r);
      schedule.context.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
      schedule.context.lineTo(x + r, y + h);
      schedule.context.quadraticCurveTo(x, y + h, x, y + h - r);
      schedule.context.lineTo(x, y + r);
      schedule.context.quadraticCurveTo(x, y, x + r, y);
      schedule.context.closePath();
      schedule.context.fill();
      schedule.context.stroke();

      schedule.context.shadowBlur = 0;

      // Рисуем текст
      schedule.context.fillStyle = this.color;
      schedule.context.font = `bold ${fontSize}px sans-serif`;
      schedule.context.textBaseline = 'top';
      schedule.context.textAlign = 'left';

      for (let i = 0; i < lines.length; i++) {
        const textY = tooltipY + padding + i * lineHeight;
        const textX = tooltipX + padding;
        
        // Первая строка — день, остальные — данные
        if (i === 0) {
          schedule.context.fillStyle = '#333333';
          schedule.context.font = `bold ${fontSize}px sans-serif`;
        } else {
          schedule.context.fillStyle = this.color;
          schedule.context.font = `${fontSize}px sans-serif`;
        }
        
        schedule.context.fillText(lines[i], textX, textY);
      }

      // Маленькая стрелка от тултипа к точке
      const arrowX = cx > tooltipX + tooltipWidth / 2 ? tooltipX : tooltipX + tooltipWidth;
      const arrowY = tooltipY + tooltipHeight / 2;

      schedule.context.fillStyle = '#FFFFFF';
      schedule.context.strokeStyle = this.color;
      schedule.context.lineWidth = 2;
      schedule.context.beginPath();
      
      if (cx > tooltipX + tooltipWidth / 2) {
        // Стрелка справа
        schedule.context.moveTo(tooltipX + tooltipWidth, arrowY);
        schedule.context.lineTo(tooltipX + tooltipWidth + 10, arrowY);
        schedule.context.lineTo(tooltipX + tooltipWidth, arrowY - 6);
      } else {
        // Стрелка слева
        schedule.context.moveTo(tooltipX, arrowY);
        schedule.context.lineTo(tooltipX - 10, arrowY);
        schedule.context.lineTo(tooltipX, arrowY - 6);
      }
      schedule.context.closePath();
      schedule.context.fill();
      schedule.context.stroke();
    }
  }

  /**
   * Получить название месяца
   */
  getMonthName() {
    const months = [
      'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня',
      'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'
    ];
    const now = new Date();
    return months[now.getMonth()];
  }
}