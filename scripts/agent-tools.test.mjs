import assert from 'node:assert/strict'
import test from 'node:test'
import { registerPageTools } from '../resources/js/agent-tools.js'

function page(modelContext) {
  const registered = new Map()
  const listeners = new Map()
  const main = { innerText: 'Développement Laravel' }
  const links = [
    { href: '/fr/services/', innerText: ' Services ' },
    { href: '/fr/services/', innerText: 'Services' },
    { href: 'mailto:contact@example.test', innerText: '', getAttribute: () => 'Email' },
    { href: 'javascript:alert(1)', innerText: 'Unsafe link' },
  ]
  const context = modelContext ?? {
    async registerTool(tool, options) { registered.set(tool.name, { tool, options }) },
  }
  const document = {
    modelContext: context,
    title: 'André Breia',
    location: { href: 'https://example.test/fr/' },
    querySelector: () => main,
    querySelectorAll: () => links,
    defaultView: { addEventListener: (name, listener) => listeners.set(name, listener) },
  }

  return { document, registered, listeners, main, context }
}

test('reads the current rendered content and deduplicates safe page links', async () => {
  const { document, registered, main } = page()
  await registerPageTools(document, {})
  main.innerText = 'Updated visible content'

  assert.deepEqual(JSON.parse(await registered.get('read_page').tool.execute()), {
    title: 'André Breia',
    url: 'https://example.test/fr/',
    content: 'Updated visible content',
  })
  assert.deepEqual(JSON.parse(await registered.get('list_page_links').tool.execute()), [
    { title: 'Services', url: 'https://example.test/fr/services/' },
    { title: 'Email', url: 'mailto:contact@example.test' },
  ])
})

test('does nothing when WebMCP or public page content is unavailable', async () => {
  const { document, registered } = page()
  await registerPageTools({ ...document, modelContext: undefined }, {})
  await registerPageTools({ ...document, querySelector: () => null }, {})

  assert.equal(registered.size, 0)
})

test('supports the older navigator API and prefers the document API', async () => {
  const { document, context, registered } = page()
  await registerPageTools({ ...document, modelContext: undefined }, { modelContext: context })
  assert.equal(registered.size, 2)

  let fallbackCalls = 0
  await registerPageTools(document, {
    modelContext: { registerTool() { fallbackCalls++ } },
  })
  assert.equal(fallbackCalls, 0)
})

test('keeps tools for back-forward cache and unregisters them when leaving the document', async () => {
  const { document, registered, listeners } = page()
  await registerPageTools(document, {})
  const signal = registered.get('read_page').options.signal

  listeners.get('pagehide')({ persisted: true })
  assert.equal(signal.aborted, false)
  listeners.get('pagehide')({ persisted: false })
  assert.equal(signal.aborted, true)
})

test('contains registration failures and cancels partially registered tools', async () => {
  let signal
  const { document } = page({
    async registerTool(tool, options) {
      signal = options.signal
      if (tool.name === 'list_page_links') throw new Error('Registration unavailable')
    },
  })

  await registerPageTools(document, {})

  assert.equal(signal.aborted, true)
})
