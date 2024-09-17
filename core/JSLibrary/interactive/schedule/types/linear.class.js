/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
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

  render(schedule) {
    if (typeof(schedule) == 'object') {
      if (typeof(schedule.context) == 'object' && this.data.length > 0) {
        let lineXStep = schedule.getFrameSize().width / schedule.getDaysCountInCurrentMonth();
        let lineYStep = schedule.getFrameSize().height / schedule.getMaxYData();

        schedule.context.strokeStyle = this.color;
        schedule.context.lineWidth = 2;

        for (let dataIndex = 1; dataIndex < this.data.length; dataIndex++) {
          let prevIndex = dataIndex - 1;
          
          if (typeof(this.data[dataIndex]) != 'undefined') {
            let prevData = this.data[prevIndex], currData = this.data[dataIndex];

            schedule.context.beginPath();
            schedule.context.moveTo(schedule.getFramePosition().x + (lineXStep * prevData.x), (schedule.getFramePosition().y + schedule.getFrameSize().height) - ((lineYStep * prevData.y)));
            schedule.context.lineTo(schedule.getFramePosition().x + (lineXStep * currData.x), (schedule.getFramePosition().y + schedule.getFrameSize().height) - ((lineYStep * currData.y)));
            schedule.context.stroke();
          }
        }

        schedule.context.lineWidth = 1;

        for (let data of this.data) {
          let dot = {
            x: schedule.getFramePosition().x + ((lineXStep * data.x) - 6),
            y: (schedule.getFramePosition().y + schedule.getFrameSize().height) - ((lineYStep * data.y) + 6)
          };

          if (schedule.mouse.x >= dot.x && schedule.mouse.x <= dot.x + 12 && schedule.mouse.y >= dot.y && schedule.mouse.y <= dot.y + 12) {
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
  }
}