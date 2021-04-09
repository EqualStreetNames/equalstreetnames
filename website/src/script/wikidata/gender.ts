'use strict';

export default function (person: { gender?: string }): string | null {
  if (typeof person.gender === 'undefined') {
    return null;
  }

  return person.gender;
}
