'use strict';

export default function (person: { birth?: number }): number | null {
  if (typeof person.birth === 'undefined') {
    return null;
  }

  return person.birth;
}
