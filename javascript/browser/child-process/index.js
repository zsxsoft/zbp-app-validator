const puppeteer = require('puppeteer')
const Promise = require('bluebird')
const debug = require('debug')('zav:javascript:browser')
const config = require('../../shared/config')
const fs = require('fs')
const path = require('path')
const { simulateDevices, validateUrls } = config
const wait = (timeout) => new Promise((resolve, reject) => setTimeout(resolve, timeout))
let finishedDevices = 0

const queue = []
const screenshotPath = path.join(config.tempPath, '/screenshot')

const currentElectronPath = typeof(require('electron')) === 'string' ? require('electron') : require('electron').app.getPath('exe')

if (!fs.existsSync(screenshotPath)) {
  fs.mkdirSync(screenshotPath)
}

async function runQueue () {
  try {
    await runBrowser()
  } catch (e) {
    console.error(e)
    process.exit(1)
  }
}

async function runBrowser () {
  if (queue.length <= 0) return
  const item = queue.pop()
  const device = item[0]
  const url = item[1]
  const onProcessExit = () => {
    finishedDevices--
    if (finishedDevices === 0) {
      process.exit(0)
    }
    setImmediate(() => {
      runQueue()
    })
  }
  console.log(`Loading ${device.name} with ${url}`)
  debug(`loading ${device.name} with ${url}`)
  const { viewport, userAgent } = device

  const browser = await puppeteer.launch({
    defaultViewport: viewport,
    // headless: false
  })
  const page = await browser.newPage()
  if (userAgent) {
    await page.setUserAgent(userAgent)
  }
  page.on('console', (message) => {
    const type = message.type()
    const text = message.text()
    if (process.send) {
      process.send({type: 'console', data: {type, text, url, device: device.name}})
    }
    debug(`get console: ${type}: ${text}`)
  })
  page.on('response', async (response) => {
    const url = response.url()

    const object = {
      url,
      status: response.status(),
      statusText: response.statusText(),
      length: await response.buffer().then(p => p.length),
      remoteAddress: response.remoteAddress(),
      headers: response.headers(),
      request: {
        method: response.request().method(),
        headers: response.request().headers()
      },
      device: device.name
    }
    if (process.send) {
      process.send({type: 'network', data: object})
    }
  })
  page.on('pageerror', (e) => {
    console.log(e)
  })


  await page.goto(url)
  await wait(3000)
  await page.screenshot({
    path: path.join(screenshotPath, `${device.name}-${encodeURIComponent(url)}.png`),
    fullPage: device.fullScreenScreenShot
  })
  await browser.close()

  onProcessExit()

}

simulateDevices.forEach(device => {
  validateUrls.forEach(url => {
    queue.push([device, config.host + url])
    finishedDevices++
  })
})

runQueue()
runQueue() // call twice to simulate two threads
