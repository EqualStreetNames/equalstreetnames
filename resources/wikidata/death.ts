"use strict";

export default function(person: { death?: string }): string | null {
  if (typeof person.death === "undefined") {
    return null;
  }

  return person.death;
}
