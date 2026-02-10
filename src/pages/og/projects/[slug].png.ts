import type { APIRoute, GetStaticPaths } from 'astro'
import { getCollection } from 'astro:content'
import { generateOgImage } from '../../../lib/og-image'

export const getStaticPaths: GetStaticPaths = async () => {
  const projects = await getCollection('projects')
  return projects.map((project) => ({
    params: { slug: project.id },
    props: { project },
  }))
}

export const GET: APIRoute = async ({ props }) => {
  const { project } = props as { project: any }
  const png = await generateOgImage({
    title: project.data.title,
    description: project.data.excerpt,
  })

  return new Response(png, {
    headers: { 'Content-Type': 'image/png' },
  })
}
