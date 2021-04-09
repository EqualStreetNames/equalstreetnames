'use strict';

import getGender from './wikidata/gender';
import getName from './wikidata/labels';
import getBirth from './wikidata/birth';
import getDeath from './wikidata/death';
import getDescription from './wikidata/descriptions';
import getNickname from './wikidata/nicknames';
import getWikipedia from './wikidata/sitelinks';
import getImage from './wikidata/image';

import colors from './colors';

import { lang } from './index';

export default function (
  streetname: string,
  details:
    | {[key: string]: string | number | boolean}
    | {[key: string]: string | number | boolean}[]
    | null
): string {
  let html = '';

  if (details !== null) {
    if (Array.isArray(details) === true) {
      details.forEach((d: {[key: string]: string | number | boolean}) => {
        html += popupDetails(d);
      });
    } else {
      html += popupDetails(details);
    }
  }

  return html + `<div class="popup-streetname">${streetname}</div>`;
}

function popupDetails (
  details: {[key: string]: string | number | boolean}
): string {
  const wikidata = details.wikidata as string;
  const name = getName(details, lang);
  const birth = getBirth(details);
  const death = getDeath(details);
  const description = getDescription(details, lang);
  const nickname = getNickname(details, lang);
  const gender = getGender(details);
  const wikipedia = getWikipedia(details, lang);
  const image = getImage(details, 150, 150);

  let htmlDetails = '';

  htmlDetails += '<div class="popup-wikidata">';

  if (image !== null) {
    htmlDetails += '<div class="media">';
    htmlDetails += `<img class="mr-3" src="${image}" alt="${name}" />`;
    htmlDetails += '<div class="media-body">';
  }

  htmlDetails += '<div class="popup-name">';
  htmlDetails += `<span class="popup-name__name">${name}</span>`;
  if (nickname !== null) {
    htmlDetails += ` (<span class="popup-name__nickname">${nickname}</span>)`;
  }
  if (details.person === true && gender !== null) {
    const highlightColor = colors[gender.toLowerCase()] ?? '#fff';
    htmlDetails += `<span class="highlight-low" style="background:linear-gradient(180deg, rgba(255, 255, 255, 0) 65%, ${highlightColor} 65%);"></span>`;
  }
  htmlDetails += '</div>';

  if (details.person === true && (birth !== null || death !== null)) {
    htmlDetails +=
      '<div class="popup-life">' +
      (birth === null ? '???' : birth) +
      ' - ' +
      (death === null ? '???' : death) +
      '</div>';
  }

  if (description !== null) {
    htmlDetails += `<p class="popup-description">${description}</p>`;
  }

  htmlDetails += '<div class="popup-links">';
  if (wikipedia !== null) {
    htmlDetails +=
      `<a target="_blank" href="${wikipedia.url}">` +
      `Wikipedia (${wikipedia.lang.toUpperCase()})` +
      '</a> + ';
  }
  htmlDetails += `<a target="_blank" href="https://www.wikidata.org/wiki/${wikidata}">Wikidata</a>`;
  htmlDetails += '</div>';

  htmlDetails += '</div>';

  if (image !== null) {
    htmlDetails += '</div>';
  }

  htmlDetails += '<hr>';

  return htmlDetails;
}
