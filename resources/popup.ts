"use strict";

import colors from "./colors";

export default function(
  streetname: string,
  wikidata?: string | null,
  name?: string | null,
  birth?: number | null,
  death?: number | null,
  description?: string | null,
  gender?: string | null,
  wikipedia?: { lang: string; url: string } | null
): string {
  let html = "";
  if (
    typeof wikidata !== "undefined" &&
    wikidata !== null &&
    // to-do: Find out why wikidata is a "null" string (instead of null)
    wikidata !== "null"
  ) {
    const highlightColor = gender ? colors[gender.toLowerCase()] : "#fff";
    html += '<div class="popup-wikidata">';
    html += `<h2 class="popup-name highlight-low" style="background:linear-gradient(180deg, rgba(255, 255, 255, 0) 65%, ${highlightColor} 65%);">${name}</h2>`;
    if (birth !== null || death !== null) {
      html +=
        '<div class="popup-life">' +
        (birth === null ? "???" : birth) +
        " - " +
        (death === null ? "???" : death) +
        "</div>";
    }
    if (description !== null) {
      html += `<p class="popup-description">${description}</p>`;
    }
    html += '<div class="popup-links">';
    html +=
      typeof wikipedia !== "undefined" && wikipedia !== null
        ? `<a target="_blank" href="${wikipedia.url}">` +
          `Wikipedia (${wikipedia.lang.toUpperCase()})` +
          `</a> + `
        : "";
    html += `<a target="_blank" href="https://www.wikidata.org/wiki/${wikidata}">Wikidata</a>`;
    html += "</div>";
    html += "</div>";
    html += "<hr>";
  }

  html += `<div class="popup-streetname">${streetname}</div>`;

  return html;
}
