"use strict";

export default function(
  person: {
    labels?: {
      de?: { language: string; value: string };
      en?: { language: string; value: string };
      fr?: { language: string; value: string };
      nl?: { language: string; value: string };
    };
  },
  lang: string
): string | null {
  if (
    typeof person.labels === "undefined" ||
    Object.keys(person.labels).length === 0
  ) {
    return null;
  }

  const label =
    (person.labels as any)[lang] ??
    person.labels.en ??
    person.labels.fr ??
    person.labels.nl ??
    person.labels.de ??
    null;

  return label !== null ? label.value : null;
}
