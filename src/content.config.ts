import { defineCollection, z } from 'astro:content'
import { glob } from 'astro/loaders'

const projects = defineCollection({
  loader: glob({ pattern: '**/*.mdx', base: './src/content/projects' }),
  schema: z.object({
    title: z.string(),
    tags: z.string(),
    year: z.string(),
    icon: z.string(),
    logo: z.string().optional(),
    excerpt: z.string(),
    url: z.string().url().optional(),
  }),
})

const articles = defineCollection({
  loader: glob({ pattern: '**/*.mdx', base: './src/content/articles' }),
  schema: z.object({
    title: z.string(),
    excerpt: z.string(),
    date: z.coerce.date().optional(),
    draft: z.boolean().optional(),
  }),
})

export const collections = { projects, articles }
