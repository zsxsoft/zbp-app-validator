const Nightmare = require('./nightmare')
const debug = require('debug')('zav:javascript:browser')
const config = require('../../shared/config')
const fs = require('fs')
const path = require('path')
const { simulateDevices, validateUrls } = config
let finishedDevices = 0

const networkLogged = new Map()
const nightmareQueue = []
const screenshotPath = path.join(config.tempPath, '/screenshot')

const currentElectronPath = typeof(require('electron')) === 'string' ? currentElectronPath : require('electron').app.getPath('exe')

if (!fs.existsSync(screenshotPath)) {
  fs.mkdirSync(screenshotPath)
}

async function runNightmareQueueWrapper () {
  try {
    await runNightmareQueue()
  } catch (e) {
    console.error(e)
    process.exit(1)
  }
}

async function runNightmareQueue () {
  if (nightmareQueue.length <= 0) return
  const item = nightmareQueue.pop()
  const device = item[0]
  const url = item[1]
  console.log(`loading ${device.name} with ${url}`)
  debug(`loading ${device.name} with ${url}`)
  const { viewport, userAgent } = device
  const nightmare = Nightmare({
    switches: {
      'force-device-scale-factor': viewport.deviceScaleFactor
    },
    useContentSize: true,
    frame: false,
    show: false,
    width: viewport.width,
    height: viewport.height,
    electronPath: config.electronPath === '' ? currentElectronPath : config.electronPath
  })

  if (userAgent) {
    nightmare.useragent(userAgent)
  }

  nightmare
    //.viewport(viewport.width, viewport.height)
    .logErrors()
    .getNetwork()
    .on('network-completed', (detail) => {
      if (networkLogged.has(detail.url)) return
      networkLogged.set(detail.url, true)
      process.send({type: 'network', data: detail})
    })
    .on('console', (type, message, stack) => {
      process.send({type: 'console', data: {type, message, url, device: device.name}})
      debug(`get console: ${type}: ${message}`)
    })
    .goto(url)
    .evaluate(function() {
      const s = document.styleSheets[0];
      s.insertRule('::-webkit-scrollbar { display:none; }');
    })

  if (device.fullScreenScreenShot) {
    const dimensions = await nightmare
      .wait('body')
      .evaluate(function () {
        const body = document.querySelector('body')
        return {
          height: body.scrollHeight,
          width: body.scrollWidth
        }
      })
    debug(`setting screenshot size to ${JSON.stringify(dimensions)}`)
    await nightmare
      .viewport(dimensions.width, dimensions.height)
      .wait(1000)
  }

  await nightmare.screenshot(path.join(screenshotPath, `${device.name}-${encodeURIComponent(url)}.png`))
  await nightmare.end()
  debug('closed nightmare')

  finishedDevices--
  if (finishedDevices === 0) {
    process.exit(0)
  }

  setImmediate(() => {
    runNightmareQueueWrapper()
  })
}

simulateDevices.forEach(device => {
  validateUrls.forEach(url => {
    nightmareQueue.push([device, config.host + url])
    finishedDevices++
  })
})
runNightmareQueueWrapper()
runNightmareQueueWrapper() // call twice to simulate two threads
