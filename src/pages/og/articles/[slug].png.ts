import type { APIRoute, GetStaticPaths } from 'astro'
import { getCollection } from 'astro:content'
import { generateOgImage } from '../../../lib/og-image'

export const getStaticPaths: GetStaticPaths = async () => {
  const articles = (await getCollection('articles')).filter((a) => !a.data.draft)
  return articles.map((article) => ({
    params: { slug: article.id },
    props: { article },
  }))
}

export const GET: APIRoute = async ({ props }) => {
  const { article } = props as { article: any }
  const png = await generateOgImage({
    title: article.data.title,
    description: article.data.excerpt,
  })

  return new Response(png, {
    headers: { 'Content-Type': 'image/png' },
  })
}
