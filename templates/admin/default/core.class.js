/**
 * Garbalo (https://www.garbalo.com/)
 * 
 * @copyright   Copyright (c) 2020 - 2024, Garbalo (https://www.garbalo.com/)
 */

'use strict';

import {Interactive} from "../../../core/JSLibrary/interactive.class.js";

export class Core {
  constructor(CMSCore) {
    this.CMSCore = CMSCore;
  }

  init() {
    if (this.CMSCore.searchParams.getPathPart(2) == 'concerts') {
      let interactiveCreatePageButton = new Interactive('button');
      interactiveCreatePageButton.target.setLabel('Новый концерт');
      interactiveCreatePageButton.target.setCallback(() => {
        let concertsContainerElement = document.querySelector('.page_concerts .concerts-container');
        if (concertsContainerElement != null) {
          let concertBlockElement = ConcertBlock.createElement();
          concertsContainerElement.prepend(concertBlockElement);
        }
      });

      interactiveCreatePageButton.assembly();

      let interactiveContainerElement = document.querySelector('#E8548530785');
      interactiveContainerElement.append(interactiveCreatePageButton.target.element);

      let pageContentElement = document.querySelector('[role="page-content"]');
      if (pageContentElement != null) {
        fetch('/handler/concerts', {method: 'GET'}).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          if (data.outputData.hasOwnProperty('concerts')) {
            let concertsContainerElement = document.createElement('div');
            concertsContainerElement.classList.add('concerts-container');

            let concerts = data.outputData.concerts;
            concerts.forEach((concert) => {
              let concertBlockElement = ConcertBlock.createElement({
                'id': concert.id,
                'categoryID': concert.categoryID,
                'title': concert.title,
                'titlePlatform': concert.titlePlatform,
                'link': concert.link,
                'date': concert.date,
                'city': concert.city
              });

              concertsContainerElement.appendChild(concertBlockElement);
            });

            pageContentElement.appendChild(concertsContainerElement);
          }
        });
      }
    } else if (this.CMSCore.searchParams.getPathPart(2) == 'mediabox') {
      let pageContentElement = document.querySelector('[role="page-content"]');
      if (pageContentElement != null) {
        fetch('/kozyrev-media.json', {method: 'GET'}).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          let formElement = document.createElement('form');
          
          let groupPhotosLabelElement = document.createElement('h3');
          let groupVideosLabelElement = document.createElement('h3');

          groupPhotosLabelElement.innerHTML = 'Фотографии';
          groupVideosLabelElement.innerHTML = 'Видео';

          formElement.classList.add('form');
          formElement.classList.add('media-container');
          groupPhotosLabelElement.classList.add('form__title');
          groupVideosLabelElement.classList.add('form__title');

          formElement.append(groupPhotosLabelElement);

          if (data.hasOwnProperty('photos')) {
            let photos = data.photos;
            photos.forEach((element, elementIndex) => {
              let itemContainerElement = document.createElement('div');
              let itemPhotoContainerElement = document.createElement('div');
              let itemTextareaContainerElement = document.createElement('div');

              let itemPhotoImageElement = document.createElement('img');
              itemPhotoImageElement.setAttribute('src', element.url);

              let itemTextareaElement = document.createElement('textarea');
              itemTextareaElement.innerText = element.url;

              itemTextareaElement.setAttribute('name', 'media_photo_url[]');

              itemContainerElement.classList.add('media-container__item');
              itemContainerElement.classList.add('item');
              itemPhotoContainerElement.classList.add('item__photo-container');
              itemTextareaContainerElement.classList.add('item__textarea-container');
              itemPhotoImageElement.classList.add('item__image');
              itemTextareaElement.classList.add('item__textarea');
              itemTextareaElement.classList.add('form__textarea');
              itemTextareaElement.classList.add('textarea');

              itemTextareaElement.addEventListener('change', (event) => {
                event.preventDefault();

                fetch(itemTextareaElement.value, {method: 'HEAD'}).then((response) => {
                  let image = new Image();
                  image.src = itemTextareaElement.value;

                  image.addEventListener('load', () => {
                    itemPhotoImageElement.setAttribute('src', itemTextareaElement.value);
                  });

                  image.addEventListener('error', () => {
                    itemPhotoImageElement.setAttribute('src', '/templates/kozyrev/images/cross_128.png');
                  });
                });
              });

              itemPhotoContainerElement.append(itemPhotoImageElement);
              itemTextareaContainerElement.append(itemTextareaElement);

              itemContainerElement.append(itemPhotoContainerElement);
              itemContainerElement.append(itemTextareaContainerElement);
              formElement.append(itemContainerElement);
            });
          }

          formElement.append(groupVideosLabelElement);

          if (data.hasOwnProperty('videos')) {
            let videos = data.videos;
            videos.forEach((element, elementIndex) => {
              let itemContainerElement = document.createElement('div');
              let itemPreviewContainerElement = document.createElement('div');
              let itemTextareaContainerElement = document.createElement('div');

              let itemPreviewImageElement = document.createElement('img');
              itemPreviewImageElement.setAttribute('src', element.previewURL);

              let itemPreviewTextareaElement = document.createElement('textarea');
              let itemVideoURLTextareaElement = document.createElement('textarea');
              itemPreviewTextareaElement.innerText = element.previewURL;
              itemVideoURLTextareaElement.innerText = element.videoURL;

              itemPreviewTextareaElement.setAttribute('name', 'media_video_preview_url[]');
              itemVideoURLTextareaElement.setAttribute('name', 'media_video_url[]');

              itemContainerElement.classList.add('media-container__item');
              itemContainerElement.classList.add('item');
              itemPreviewContainerElement.classList.add('item__photo-container');
              itemTextareaContainerElement.classList.add('item__textarea-container');
              itemPreviewImageElement.classList.add('item__image');
              itemPreviewTextareaElement.classList.add('item__textarea');
              itemPreviewTextareaElement.classList.add('form__textarea');
              itemPreviewTextareaElement.classList.add('textarea');
              itemVideoURLTextareaElement.classList.add('item__textarea');
              itemVideoURLTextareaElement.classList.add('form__textarea');
              itemVideoURLTextareaElement.classList.add('textarea');

              itemPreviewTextareaElement.addEventListener('change', (event) => {
                event.preventDefault();

                fetch(itemPreviewTextareaElement.value, {method: 'HEAD'}).then((response) => {
                  let image = new Image();
                  image.src = itemPreviewTextareaElement.value;

                  image.addEventListener('load', () => {
                    itemPreviewImageElement.setAttribute('src', itemPreviewTextareaElement.value);
                  });

                  image.addEventListener('error', () => {
                    itemPreviewImageElement.setAttribute('src', '/templates/kozyrev/images/cross_128.png');
                  });
                });
              });

              itemPreviewContainerElement.append(itemPreviewImageElement);
              itemTextareaContainerElement.append(itemPreviewTextareaElement);
              itemTextareaContainerElement.append(itemVideoURLTextareaElement);

              itemContainerElement.append(itemPreviewContainerElement);
              itemContainerElement.append(itemTextareaContainerElement);
              formElement.append(itemContainerElement);
            });
          }

          let interactiveSaveButton = new Interactive('button');
          interactiveSaveButton.target.setLabel('Сохранить');
          interactiveSaveButton.target.setCallback(() => {
            let formData = new FormData(formElement);
            let request = new Interactive('request', {
              method: 'POST',
              url: '/handler/mediabox'
            });

            request.target.data = formData;

            request.target.send().then((data) => {
              if (data.statusCode == 1) {
                
              }
            });
          });

          interactiveSaveButton.assembly();

          pageContentElement.append(formElement);
          pageContentElement.append(interactiveSaveButton.target.element);
        });
      }
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.CMSCore.addEventListener('ready', () => {
    window.CMSCore.templateCore = new Core(window.CMSCore);
    window.CMSCore.templateCore.init();
  });
});