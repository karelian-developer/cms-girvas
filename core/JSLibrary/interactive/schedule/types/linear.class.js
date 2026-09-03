'use strict';

import {DataDot} from '../dataDot.class.js';

export class Linear {
  constructor(schedule) {
    this.schedule = schedule;
    this.data = [];
    this.color = '#000000';
    this.label = `Data #${schedule.types.length + 1}`;
    this._isHovered = false;
    this._hoveredData = null;
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
    // 1. ОПРЕДЕЛЯЕМ, НАВЕДЕНЫ ЛИ НА ЭТУ ЛИНИЮ
    // ==========================================
    const mouseX = schedule.mouse.x;
    const mouseY = schedule.mouse.y;
    const isMouseOnCanvas = mouseX > 0 && mouseY > 0 && 
                            mouseX < schedule.canvas.width && 
                            mouseY < schedule.canvas.height;
    
    let isHovered = false;
    let hoveredData = null;
    const hoverRadius = 12;

    if (isMouseOnCanvas) {
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
          isHovered = true;
          hoveredData = data;
          break;
        }
      }
    }

    // Сохраняем состояние для тултипа
    this._isHovered = isHovered;
    this._hoveredData = hoveredData;

    // ==========================================
    // 2. ОПРЕДЕЛЯЕМ, КАКАЯ ЛИНИЯ АКТИВНА
    // ==========================================
    const typeIndex = schedule.types.indexOf(this);
    const isActive = isHovered;
    
    let hasOtherActive = false;
    if (isHovered) {
      for (let t = 0; t < schedule.types.length; t++) {
        if (t !== typeIndex && schedule.types[t]._isHovered) {
          hasOtherActive = true;
          break;
        }
      }
    }

    // ==========================================
    // 3. РИСУЕМ ЛИНИЮ
    // ==========================================
    let lineAlpha = 1.0;
    if (isHovered) {
      lineAlpha = 1.0;
    } else if (hasOtherActive) {
      lineAlpha = 0.15;
    }
    
    schedule.context.globalAlpha = lineAlpha;
    
    schedule.context.strokeStyle = this.color;
    schedule.context.lineWidth = isActive ? 3.5 : 2.5;
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
    schedule.context.globalAlpha = 1.0;

    // ==========================================
    // 4. РИСУЕМ ТОЧКИ
    // ==========================================
    for (let i = 0; i < visibleData.length; i++) {
      const data = visibleData[i];
      if (!data) continue;

      const relativeX = data.x - viewStart * totalDays;
      const clampedX = Math.max(0, Math.min(visibleDays, relativeX));
      const xPos = clampedX * lineXStep;
      const yPos = data.y * lineYStep;

      const cx = frameX + xPos;
      const cy = frameY + frameHeight - yPos;
      
      const isHoveredPoint = (isHovered && hoveredData === data);
      const radius = isHoveredPoint ? 7 : 4;
      const borderWidth = isHoveredPoint ? 3 : 1.5;

      let pointAlpha = 1.0;
      if (hasOtherActive && !isActive) {
        pointAlpha = 0.15;
      }
      schedule.context.globalAlpha = pointAlpha;

      if (isHoveredPoint) {
        schedule.context.shadowColor = this.color;
        schedule.context.shadowBlur = 15;
      }

      schedule.context.fillStyle = this.color;
      schedule.context.beginPath();
      schedule.context.arc(cx, cy, radius, 0, Math.PI * 2);
      schedule.context.fill();

      schedule.context.shadowBlur = 0;
      schedule.context.strokeStyle = '#FFFFFF';
      schedule.context.lineWidth = borderWidth;
      schedule.context.beginPath();
      schedule.context.arc(cx, cy, radius, 0, Math.PI * 2);
      schedule.context.stroke();

      if (isHoveredPoint) {
        schedule.context.strokeStyle = this.color;
        schedule.context.lineWidth = 2;
        schedule.context.setLineDash([4, 4]);
        schedule.context.beginPath();
        schedule.context.arc(cx, cy, radius + 6, 0, Math.PI * 2);
        schedule.context.stroke();
        schedule.context.setLineDash([]);
      }
    }

    schedule.context.globalAlpha = 1.0;
    schedule.context.shadowBlur = 0;
  }

  getMonthName() {
    const months = [
      'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня',
      'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'
    ];
    const now = new Date();
    return months[now.getMonth()];
  }
}