const Nightmare = require('nightmare')
require('nightmare-custom-event')(Nightmare)

/**
 * Log erros to console
 */
Nightmare.action('logErrors',
  function (name, options, parent, win, renderer, done) {
    parent.on('logErrors', function (done) {
      const domReady = () => {
        window.addEventListener('error', (e) => {
          let message = e.message
          if (!message) {
            message = e.target.src + ' load error'
          }
          console.error(message)
        }, true)
      }
      win.webContents.once('dom-ready', () => {
        win.webContents.executeJavaScript(`(${domReady.toString()})()`)
      })
      parent.emit('logErrors')
    })
    done()
    return this
  },
  function (done) {
    this.child.once('logErrors', done)
    this.child.emit('logErrors')
  }
)

/**
 * Log network into console
 */
Nightmare.action('getNetwork', function (name, options, parent, win, renderer, done) {
  parent.on('getNetwork', function (done) {
    win.webContents.session.webRequest.onCompleted(details => {
      parent.emit('network-completed', details)
    })
    parent.emit('getNetwork')
  })
  done()
  return this
}, function (done) {
  this.child.once('getNetwork', done)
  this.child.emit('getNetwork')
})

module.exports = Nightmare
