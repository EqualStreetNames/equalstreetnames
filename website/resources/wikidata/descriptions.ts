"use strict";

export default function(
  person: {
    descriptions?: Record<string, { language: string; value: string }>;;
  },
  lang: string
): string | null {
  if (
    typeof person.descriptions === "undefined" ||
    Object.keys(person.descriptions).length === 0
  ) {
    return null;
  }

  const keys = Object.keys(person.descriptions);
  const description = person.descriptions[lang] ?? person.descriptions[keys[0]] ?? null;

  return description !== null ? description.value : null;
}
