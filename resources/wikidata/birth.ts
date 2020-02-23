"use strict";

export default function(person: { birth?: string }): string | null {
  if (typeof person.birth === "undefined") {
    return null;
  }

  return person.birth;
}
