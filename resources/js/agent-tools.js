export async function registerPageTools(document, navigator) {
  const modelContext = document.modelContext ?? navigator.modelContext

  if (typeof modelContext?.registerTool !== 'function' || !document.querySelector('main')) return

  const controller = new AbortController()
  const tools = [
    {
      name: 'read_page',
      description: 'Read the title, URL, and visible main content of the current portfolio page or article.',
      inputSchema: { type: 'object', properties: {}, additionalProperties: false },
      annotations: { readOnlyHint: true },
      execute: async () => JSON.stringify({
        title: document.title,
        url: document.location.href,
        content: document.querySelector('main').innerText,
      }),
    },
    {
      name: 'list_page_links',
      description: 'List links in the current page content and navigation, including articles, services, and contact options.',
      inputSchema: { type: 'object', properties: {}, additionalProperties: false },
      annotations: { readOnlyHint: true },
      execute: async () => {
        const links = new Map()

        for (const link of document.querySelectorAll('main a[href], nav a[href]')) {
          const url = new URL(link.href, document.location.href)
          if (!['https:', 'http:', 'mailto:'].includes(url.protocol)) continue

          const title = (link.innerText || link.getAttribute('aria-label') || '').trim()
          if (title) links.set(url.href, { title, url: url.href })
        }

        return JSON.stringify([...links.values()])
      },
    },
  ]

  document.defaultView.addEventListener('pagehide', (event) => {
    if (!event.persisted) controller.abort()
  })

  const results = await Promise.allSettled(tools.map(async (tool) => {
    await modelContext.registerTool(tool, { signal: controller.signal })
  }))

  if (results.some((result) => result.status === 'rejected')) {
    controller.abort()
  }
}
