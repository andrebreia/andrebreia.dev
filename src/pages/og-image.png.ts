import type { APIRoute } from 'astro'
import { generateOgImage } from '../lib/og-image'

export const GET: APIRoute = async () => {
  const png = await generateOgImage({
    title: 'Freelance Laravel & JavaScript Developer',
    description:
      '10+ years of experience helping startups and agencies ship reliable Laravel, Vue, React, Inertia, Livewire, and TailwindCSS projects.',
  })

  return new Response(png, {
    headers: { 'Content-Type': 'image/png' },
  })
}
