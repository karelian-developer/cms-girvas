/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {DataDot} from './schedule/dataDot.class.js';
import {Legend} from './schedule/legend.class.js';
import {Linear as ScheduleLinear} from './schedule/types/linear.class.js';

export class Schedule {
  constructor(interactiveObject, canvas, type = 'linear') {
    this.interactiveObject = interactiveObject;

    this.dataCollision = {x: 0, y: 0};
    this.mouseCollision = false;
    this.mouse = {x: 0, y: 0};
    this.canvas = canvas;
    this.frame = {
      position: {x: 25, y: 25},
      size: {width: 600, height: 200}
    };
    this.context = typeof(canvas) == 'object' ? canvas.getContext('2d') : null;
    this.dataDots = [];
    this.types = [];
    this.legend = new Legend();

    if (this.context != null) {
      this.context.width = this.getCanvasWidth();
      this.context.height = this.getCanvasHeight();

      this.context.lineWidth = 1;
    }

    this.type = type;
  }

  setFramePosition(x, y) {
    this.frame.position.x = x;
    this.frame.position.y = y;
  }

  setFrameSize(width, height) {
    this.frame.size.width = width;
    this.frame.size.height = height;
  }

  getFramePosition() {
    return {x: this.frame.position.x, y: this.frame.position.y};
  }

  getFrameSize() {
    return {width: this.frame.size.width, height: this.frame.size.height};
  }

  addGroup(label) {
    let type;

    switch (this.type) {
      default: type = new ScheduleLinear(this);
    }

    type.setLabel(label);

    this.types.push(type)
  }

  buildData() {
    if (this.types.length > 0) {
      this.types.forEach((element) => {
        element.buildData(this.getDaysCountInCurrentMonth());
      });
    }
  }

  addData(groupIndex, x, y) {
    this.types[groupIndex].addData(new DataDot(x, y));
  }

  getMaxYData() {
    let maxData = {x: 0, y: 0};

    for (let groupIndex = 0; groupIndex < this.types.length; groupIndex++) {
      for (let data of this.types[groupIndex].data) {
        if (data.y > maxData.y) {
          maxData = data;
        }
      }
    }

    return maxData.y;
  }

  getMaxXData() {
    let maxData = {x: 0, y: 0};

    for (let groupIndex = 0; groupIndex < this.types.length; groupIndex++) {
      for (let data of this.types[groupIndex].data) {
        if (data.x > maxData.x) {
          maxData = data;
        }
      }
    }
    return maxData.x;
  }

  init() {
    this.dotsRenderInterval = null;
    this.canvas.addEventListener('mousemove', (event) => {
      this.mouse.x = event.pageX - this.canvas.getBoundingClientRect().left;
      this.mouse.y = event.pageY - this.canvas.getBoundingClientRect().top;

      if (this.dotsRenderInterval == null) {
        this.dotsRenderInterval = setInterval(() => {
          this.context.reset();
          this.render();
        }, 10);
      }
    });

    for (let typeIndex = 0; typeIndex < this.types.length; typeIndex++) {
      if (this.types[typeIndex].label == '') {
        this.types[typeIndex].label = `Data #${typeIndex}`;
      }

      this.legend.addType(this.types[typeIndex]);
    }

    this.legend.setRenderSize(this.getFrameSize().width, this.canvas.height - this.getFrameSize().height - 10);

    this.canvas.addEventListener('mouseleave', (event) => {
      this.mouse = {x: 0, y: 0}

      if (this.dotsRenderInterval != null) {
        clearInterval(this.dotsRenderInterval);
        this.dotsRenderInterval = null;

        this.context.reset();
        this.render();
      }
    });

    window.addEventListener('resize', () => {
      this.render();
    });

    this.render();
  }

  assembly() {
    this.element = this.canvas;
  }

  getCanvasWidth() {
    if (typeof(this.canvas) == 'object') {
      return Math.ceil(this.canvas.getBoundingClientRect().width);
    }

    return 0;
  }

  getCanvasHeight() {
    if (typeof(this.canvas) == 'object') {
      return Math.ceil(this.canvas.getBoundingClientRect().height);
    }

    return 0;
  }

  getDaysCountInCurrentMonth() {
    let date = new Date();
    return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
  }

  drawFrame() {
    if (this.context != null) {
      this.context.strokeStyle = '#CECECE';

      this.context.beginPath();
      this.context.rect(
        this.getFramePosition().x,
        this.getFramePosition().y,
        this.getFrameSize().width,
        this.getFrameSize().height
      );
      this.context.stroke();

      this.context.beginPath();
      this.context.rect(0, 0, this.canvas.width, this.canvas.height);
      this.context.stroke();
    }
  }

  drawGrid() {
    if (this.context != null) {
      let lineXStep = this.getFrameSize().width / (this.getDaysCountInCurrentMonth() * 2);
      let lineYStep = lineXStep;

      console.log(lineXStep);

      this.context.strokeStyle  = '#EAEAEA';
      this.context.font = '12px serif';
      this.context.textBaseline = 'top';
      this.context.textAlign = 'center';
      
      for (let lineX = this.getFramePosition().x, step = 0, day = 0; lineX <= this.getFramePosition().x + this.getFrameSize().width; lineX += lineXStep, step += 1) {
        this.context.beginPath();
        this.context.moveTo(lineX, this.getFramePosition().y);
        this.context.lineTo(lineX, this.getFramePosition().y + this.getFrameSize().height);
        this.context.stroke();

        if (step % 2 == 0) {
          day++;

          this.context.fillText(`${day} Day`, lineX, this.getFramePosition().y + this.getFrameSize().height + 10);
        }
      }

      for (let lineY = this.getFramePosition().y; lineY <= this.getFrameSize().height; lineY += lineYStep) {
        this.context.beginPath();
        this.context.moveTo(this.getFramePosition().x, (this.getFramePosition().y + this.getFrameSize().height) - lineY);
        this.context.lineTo(this.getFramePosition().x + this.getFrameSize().width, (this.getFramePosition().y + this.getFrameSize().height) - lineY);
        this.context.stroke();
      }
    }
  }

  render() {
    if (typeof(this.canvas) == 'object') {
      this.drawGrid();
      this.drawFrame();

      if (this.types.length > 0) {
        this.types.forEach((element) => {
          element.render(this);
        });
      }

      this.legend.render(this.context, this.getFramePosition().x, (this.getFramePosition().y + this.getFrameSize().height) + 10);

      let collisionDetected = false;
      for (let type of this.types) {
        if (collisionDetected) {
          break;
        }

        for (let data of type.data) {
          if (data.collision) {
            this.mouseCollision = true;
            this.dataCollision = data;
            collisionDetected = true;
            break;
          }
        }
      }

      if (!collisionDetected) {
        this.dataCollision = {x: 0, y: 0};
        this.mouseCollision = false;
      }

      if (this.mouseCollision) {
        this.context.strokeStyle = '#232323';
        this.context.fillStyle = '#FFFFFF';

        this.context.beginPath();
        this.context.rect(this.mouse.x + 10, this.mouse.y + 10, 60, 20);
        this.context.fill();
        this.context.stroke();

        this.context.textAlign = 'left';
        this.context.textBaseline = 'top';
        this.context.font = '12px serif';
        this.context.fillStyle = '#232323';

        this.context.fillText(`x: ${this.dataCollision.x}, y: ${this.dataCollision.y}`, this.mouse.x + 10 + 4, this.mouse.y + 10 + 4);
      }
    }
  }
}