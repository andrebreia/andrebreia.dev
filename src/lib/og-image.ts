import satori from 'satori'
import { Resvg } from '@resvg/resvg-js'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

// Load logo as base64 data URI at build time
const logoPath = resolve('public/images/logo@2x.png')
const logoBase64 = `data:image/png;base64,${readFileSync(logoPath).toString('base64')}`

export async function generateOgImage({
  title,
  description,
  siteUrl = 'andrebreia.dev',
}: {
  title: string
  description?: string
  siteUrl?: string
}) {
  const interRegular = await fetch(
    'https://cdn.jsdelivr.net/fontsource/fonts/inter@latest/latin-400-normal.woff'
  ).then((res) => res.arrayBuffer())

  const interMedium = await fetch(
    'https://cdn.jsdelivr.net/fontsource/fonts/inter@latest/latin-500-normal.woff'
  ).then((res) => res.arrayBuffer())

  const svg = await satori(
    {
      type: 'div',
      props: {
        style: {
          height: '100%',
          width: '100%',
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'space-between',
          backgroundColor: '#f1f5f9',
          padding: '60px 80px',
          fontFamily: 'Inter',
        },
        children: [
          // Top: logo + site URL
          {
            type: 'div',
            props: {
              style: {
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
              },
              children: [
                {
                  type: 'img',
                  props: {
                    src: logoBase64,
                    width: 44,
                    height: 44,
                    style: {
                      borderRadius: '10px',
                    },
                  },
                },
                {
                  type: 'span',
                  props: {
                    style: {
                      fontSize: '20px',
                      color: '#475569',
                      fontWeight: 400,
                    },
                    children: siteUrl,
                  },
                },
              ],
            },
          },
          // Center: title + description
          {
            type: 'div',
            props: {
              style: {
                display: 'flex',
                flexDirection: 'column',
                gap: '16px',
              },
              children: [
                {
                  type: 'div',
                  props: {
                    style: {
                      fontSize: title.length > 50 ? '42px' : '52px',
                      fontWeight: 500,
                      color: '#0f172a',
                      lineHeight: 1.15,
                      letterSpacing: '-0.025em',
                    },
                    children: title,
                  },
                },
                ...(description
                  ? [
                      {
                        type: 'div',
                        props: {
                          style: {
                            fontSize: '22px',
                            color: '#475569',
                            lineHeight: 1.5,
                          },
                          children:
                            description.length > 120
                              ? description.slice(0, 117) + '...'
                              : description,
                        },
                      },
                    ]
                  : []),
              ],
            },
          },
          // Bottom: name + role
          {
            type: 'div',
            props: {
              style: {
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                fontSize: '18px',
                color: '#64748b',
              },
              children: [
                {
                  type: 'span',
                  props: {
                    style: { fontWeight: 500, color: '#334155' },
                    children: 'André Breia',
                  },
                },
                {
                  type: 'span',
                  props: {
                    children: '·  Freelance Developer',
                  },
                },
              ],
            },
          },
        ],
      },
    },
    {
      width: 1200,
      height: 630,
      fonts: [
        {
          name: 'Inter',
          data: interRegular,
          weight: 400,
          style: 'normal',
        },
        {
          name: 'Inter',
          data: interMedium,
          weight: 500,
          style: 'normal',
        },
      ],
    }
  )

  const resvg = new Resvg(svg, {
    fitTo: { mode: 'width', value: 1200 },
  })

  return resvg.render().asPng()
}
