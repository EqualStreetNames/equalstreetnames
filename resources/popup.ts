"use strict";

export default function(
  streetname: string,
  wikidata?: string | null,
  name?: string | null,
  description?: string | null,
  wikipedia?: { lang: string; url: string } | null
): string {
  let html = "";

  if (
    typeof wikidata !== "undefined" &&
    wikidata !== null &&
    // to-do: Find out why wikidata is a "null" string (instead of null)
    wikidata !== "null"
  ) {
    html += '<div class="popup-wikidata">';
    html += `<div class="popup-name">${name}</div>`;
    html += `<div class="popup-description">${description}</div>`;
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
