'use strict';

export default function (
  person: {
    sitelinks?: {
      dewiki?: { badges: string[]; site: string; title: string; url: string };
      enwiki?: { badges: string[]; site: string; title: string; url: string };
      frwiki?: { badges: string[]; site: string; title: string; url: string };
      nlwiki?: { badges: string[]; site: string; title: string; url: string };
    };
  },
  lang: string
): { lang: string; url: string } | null {
  if (
    typeof person.sitelinks === 'undefined' ||
    Object.keys(person.sitelinks).length === 0
  ) {
    return null;
  }

  const link =
    (person.sitelinks as any)[`${lang}wiki`] ??
    person.sitelinks.enwiki ??
    person.sitelinks.frwiki ??
    person.sitelinks.nlwiki ??
    person.sitelinks.dewiki ??
    null;

  return link !== null ? { lang: link.site.substr(0, 2), url: link.url } : null;
}
