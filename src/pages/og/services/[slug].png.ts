import type { APIRoute, GetStaticPaths } from 'astro'
import { getCollection } from 'astro:content'
import { generateOgImage } from '../../../lib/og-image'

export const getStaticPaths: GetStaticPaths = async () => {
  const services = await getCollection('services')
  return services.map((service) => ({
    params: { slug: service.id },
    props: { service },
  }))
}

export const GET: APIRoute = async ({ props }) => {
  const { service } = props as { service: any }
  const png = await generateOgImage({
    title: service.data.title,
    description: service.data.excerpt,
  })

  return new Response(png, {
    headers: { 'Content-Type': 'image/png' },
  })
}
