"use strict";

import getGender from "./wikidata/gender";
import getName from "./wikidata/labels";
import getBirth from "./wikidata/birth";
import getDeath from "./wikidata/death";
import getDescription from "./wikidata/descriptions";
import getWikipedia from "./wikidata/sitelinks";

import colors from "./colors";

import { lang } from "./index";

export default function(
  streetname: string,
  etymology: string | null,
  person: Record<string, string | number> | null
): string {
  let html = `<div class="popup-streetname">${streetname}</div>`;

  // Bug in MapboxGL (see https://github.com/mapbox/mapbox-gl-js/issues/8497)
  if (etymology !== null && etymology !== "null" && person !== null) {
    const name = getName(person, lang);
    const birth = getBirth(person);
    const death = getDeath(person);
    const description = getDescription(person, lang);
    const gender = getGender(person);
    const wikipedia = getWikipedia(person, lang);

    let htmlPerson = "";

    const highlightColor = gender ? colors[gender.toLowerCase()] : "#fff";
    htmlPerson += '<div class="popup-wikidata">';
    htmlPerson += `
    <div class="popup-name">
      <span class="popup-name__name">${name}</span>
      <span class="highlight-low" style="background:linear-gradient(180deg, rgba(255, 255, 255, 0) 65%, ${highlightColor} 65%);"></span>
    </div>`;
    if (birth !== null || death !== null) {
      htmlPerson +=
        '<div class="popup-life">' +
        (birth === null ? "???" : birth) +
        " - " +
        (death === null ? "???" : death) +
        "</div>";
    }
    if (description !== null) {
      htmlPerson += `<p class="popup-description">${description}</p>`;
    }
    htmlPerson += '<div class="popup-links">';
    if (wikipedia !== null) {
      htmlPerson +=
        `<a target="_blank" href="${wikipedia.url}">` +
        `Wikipedia (${wikipedia.lang.toUpperCase()})` +
        `</a> + `;
    }
    htmlPerson += `<a target="_blank" href="https://www.wikidata.org/wiki/${etymology}">Wikidata</a>`;
    htmlPerson += "</div>";
    htmlPerson += "</div>";
    htmlPerson += "<hr>";

    html = htmlPerson + html;
  }

  return html;
}
