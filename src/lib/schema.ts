import { siteConfig, socialLinks } from '../data/site'

export type JsonLd = Record<string, unknown>

export function personSchema(): JsonLd {
  return {
    '@type': 'Person',
    '@id': `${siteConfig.url}/#person`,
    name: siteConfig.name,
    url: siteConfig.url,
    jobTitle: 'Freelance Full-Stack Developer',
    knowsAbout: ['Laravel', 'React', 'Vue', 'PHP', 'JavaScript', 'TypeScript', 'Inertia.js', 'Livewire'],
    hasCredential: [
      {
        '@type': 'EducationalOccupationalCredential',
        name: 'Laravel Certified Developer',
        credentialCategory: 'Professional Certification',
      },
      {
        '@type': 'EducationalOccupationalCredential',
        name: 'Stripe Certified Architect',
        credentialCategory: 'Professional Certification',
      },
    ],
    sameAs: socialLinks.map((link) => link.href),
  }
}

export function websiteSchema(): JsonLd {
  return {
    '@type': 'WebSite',
    '@id': `${siteConfig.url}/#website`,
    name: siteConfig.name,
    url: siteConfig.url,
    publisher: {
      '@id': `${siteConfig.url}/#person`,
    },
  }
}

export function professionalServiceSchema(): JsonLd {
  return {
    '@type': 'ProfessionalService',
    '@id': `${siteConfig.url}/#professional-service`,
    name: `${siteConfig.name} Freelance Development`,
    url: siteConfig.url,
    description: siteConfig.description,
    areaServed: 'Worldwide',
    founder: {
      '@id': `${siteConfig.url}/#person`,
    },
    sameAs: socialLinks.map((link) => link.href),
  }
}

export function breadcrumbSchema(items: Array<{ name: string; path: string }>): JsonLd {
  return {
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      item: new URL(item.path, siteConfig.url).href,
    })),
  }
}

export function faqSchema(items: Array<{ question: string; answer: string }>): JsonLd {
  return {
    '@type': 'FAQPage',
    mainEntity: items.map((item) => ({
      '@type': 'Question',
      name: item.question,
      acceptedAnswer: {
        '@type': 'Answer',
        text: item.answer,
      },
    })),
  }
}
