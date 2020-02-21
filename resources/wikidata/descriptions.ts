"use strict";

export default function(
  person: {
    descriptions?: {
      de?: { language: string; value: string };
      en?: { language: string; value: string };
      fr?: { language: string; value: string };
      nl?: { language: string; value: string };
    };
  },
  lang: string
): string | null {
  if (
    typeof person.descriptions === "undefined" ||
    Object.keys(person.descriptions).length === 0
  ) {
    return null;
  }

  const description =
    (person.descriptions as any)[lang] ??
    person.descriptions.en ??
    person.descriptions.fr ??
    person.descriptions.nl ??
    person.descriptions.de ??
    null;

  return description !== null ? description.value : null;
}
