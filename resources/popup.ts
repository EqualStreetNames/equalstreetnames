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
  details: Record<string, string | number | boolean> | null
): string {
  let html = `<div class="popup-streetname">${streetname}</div>`;

  // Bug in MapboxGL (see https://github.com/mapbox/mapbox-gl-js/issues/8497)
  if (etymology !== null && etymology !== "null" && details !== null) {
    const name = getName(details, lang);
    const birth = getBirth(details);
    const death = getDeath(details);
    const description = getDescription(details, lang);
    const gender = getGender(details);
    const wikipedia = getWikipedia(details, lang);

    let htmlDetails = "";

    htmlDetails += '<div class="popup-wikidata">';

    htmlDetails += '<div class="popup-name">';
    htmlDetails += `<span class="popup-name__name">${name}</span>`;
    if (details.person === true && gender !== null) {
      const highlightColor = colors[gender.toLowerCase()] ?? "#fff";
      htmlDetails += `<span class="highlight-low" style="background:linear-gradient(180deg, rgba(255, 255, 255, 0) 65%, ${highlightColor} 65%);"></span>`;
    }
    htmlDetails += "</div>";

    if (details.person === true && (birth !== null || death !== null)) {
      htmlDetails +=
        '<div class="popup-life">' +
        (birth === null ? "???" : birth) +
        " - " +
        (death === null ? "???" : death) +
        "</div>";
    }

    if (description !== null) {
      htmlDetails += `<p class="popup-description">${description}</p>`;
    }

    htmlDetails += '<div class="popup-links">';
    if (wikipedia !== null) {
      htmlDetails +=
        `<a target="_blank" href="${wikipedia.url}">` +
        `Wikipedia (${wikipedia.lang.toUpperCase()})` +
        `</a> + `;
    }
    htmlDetails += `<a target="_blank" href="https://www.wikidata.org/wiki/${etymology}">Wikidata</a>`;
    htmlDetails += "</div>";

    htmlDetails += "</div>";

    htmlDetails += "<hr>";

    html = htmlDetails + html;
  }

  return html;
}
