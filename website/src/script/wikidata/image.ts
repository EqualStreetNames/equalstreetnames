'use strict';

export default function (person: { image?: string }, height: number, width: number): string | null {
  if (typeof person.image === 'undefined' || person.image === null) {
    return null;
  }

  return `https://commons.wikimedia.org/wiki/Special:FilePath/${encodeURIComponent(person.image)}?width=${width}&height=${height}`;
}
