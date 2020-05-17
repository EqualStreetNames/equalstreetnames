"use strict";

export default function(
  person: {
    labels?: Record<string, { language: string; value: string }>;
  },
  lang: string
): string | null {
  if (
    typeof person.labels === "undefined" ||
    Object.keys(person.labels).length === 0
  ) {
    return null;
  }

  const keys = Object.keys(person.labels);
  const label = person.labels[lang] ?? person.labels[keys[0]] ?? null;

  return label !== null ? label.value : null;
}
