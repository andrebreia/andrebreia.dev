import Alpine from 'alpinejs'

Alpine.data('floatingNav', () => ({
  hidden: false,
  bouncing: false,
  timeout: null,
  onScroll() {
    clearTimeout(this.timeout)
    this.hidden = true
    this.timeout = setTimeout(() => {
      this.hidden = false
      this.bouncing = true
    }, 1500)
  },
  reveal() {
    clearTimeout(this.timeout)
    if (this.hidden) {
      this.hidden = false
      this.bouncing = true
    }
  },
  destroy() { clearTimeout(this.timeout) },
}))

Alpine.data('servicesFan', () => ({
  open: false,
  timeout: null,
  show() { clearTimeout(this.timeout); this.open = true },
  hide() { this.timeout = setTimeout(() => { this.open = false }, 50) },
  destroy() { clearTimeout(this.timeout) },
}))

Alpine.data('localClock', () => ({
  time: '',
  interval: null,
  init() {
    const update = () => {
      this.time = new Intl.DateTimeFormat('en-GB', {
        hour: '2-digit', minute: '2-digit', timeZone: this.$el.dataset.tz,
      }).format(new Date())
    }
    update()
    this.interval = setInterval(update, 60_000)
  },
  destroy() { clearInterval(this.interval) },
}))

window.Alpine = Alpine
Alpine.start()
