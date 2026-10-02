import { readFile, writeFile, mkdir, readdir, rm } from 'node:fs/promises'
import { createRequire } from 'node:module'
import { createHash } from 'node:crypto'
import yaml from 'js-yaml'
import sharp from 'sharp'
import { generateOgImage } from './og-image.ts'

const require = createRequire(import.meta.url)
const site = yaml.load(await readFile('content/globals/default/site.yaml', 'utf8'))

// Keep all installed icons available when their names are edited in the CP.
// PHP loads only the icons a page uses. Hashes keep CP input out of file paths.
const icons = {}
for (const pack of ['solar', 'lucide', 'simple-icons', 'ri']) {
  const data = require(`@iconify-json/${pack}/icons.json`)
  for (const [name, icon] of Object.entries(data.icons)) {
    icons[`${pack}:${name}`] = { width: data.width ?? 24, height: data.height ?? 24, ...icon }
  }
  for (const [name, alias] of Object.entries(data.aliases ?? {})) {
    icons[`${pack}:${name}`] = { ...icons[`${pack}:${alias.parent}`], ...alias }
  }
}
await mkdir('public/build/icons', { recursive: true })
for (const [name, icon] of Object.entries(icons)) {
  const hash = createHash('sha256').update(name).digest('hex')
  await writeFile(`public/build/icons/${hash}.json`, JSON.stringify(icon))
}

const logoBuffer = await sharp(`public/images/${site.ogLogo}`).png().toBuffer()
const common = { name: site.name, role: site.ogRole, siteUrl: new URL(site.url).hostname, logoBase64: `data:image/png;base64,${logoBuffer.toString('base64')}` }
await rm('public/og', { recursive: true, force: true })
let count = 1
await writeFile('public/og-image.png', await generateOgImage({ ...common, title: site.ogTitle, description: site.ogDescription }))
for (const collection of ['articles', 'projects', 'services']) {
  await mkdir(`public/og/${collection}`, { recursive: true })
  for (const file of (await readdir(`content/collections/${collection}`)).filter(file => file.endsWith('.md'))) {
    const source = await readFile(`content/collections/${collection}/${file}`, 'utf8')
    const entry = yaml.load(source.match(/^---\r?\n([\s\S]*?)\r?\n---(?:\r?\n|$)/)[1])
    if (entry.published === false) continue
    const slug = file.replace(/^\d{4}-\d{2}-\d{2}\./, '').replace(/\.md$/, '')
    await writeFile(`public/og/${collection}/${slug}.png`, await generateOgImage({ ...common, title: entry.headline ?? entry.title, description: entry.excerpt }))
    count++
  }
}
console.log(`Built ${Object.keys(icons).length} server-side icons and ${count} social images.`)
