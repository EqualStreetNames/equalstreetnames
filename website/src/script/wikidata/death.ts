'use strict';

export default function (person: { death?: number }): number | null {
  if (typeof person.death === 'undefined') {
    return null;
  }

  return person.death;
}
