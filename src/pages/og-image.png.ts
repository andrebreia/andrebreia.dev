import type { APIRoute } from 'astro'
import { generateOgImage } from '../lib/og-image'

export const GET: APIRoute = async () => {
  const png = await generateOgImage({
    title: 'I build web applications that work.',
    description:
      'Freelance developer based in France with 10+ years of experience helping startups and agencies ship products faster.',
  })

  return new Response(png, {
    headers: { 'Content-Type': 'image/png' },
  })
}
