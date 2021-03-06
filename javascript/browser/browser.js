const path = require('path')
const cp = require('child_process')
const config = require('../shared/config')
const crypto = require('crypto')

const nightmare = cp.fork(path.join(__dirname, '/child-process'))

const map = {
  console: new Map(),
  network: new Map()
}

const handlers = {
  console: (data) => {
    const hash = `${data.type}-${data.text}`
    if (map.console.has(hash)) {
      map.console.get(hash).count++
    } else {
      map.console.set(hash, {
        ...data,
        count: 0
      })
    }
  },
  network: (data) => {
    const hash = `${data.url}-${data.status}`
    if (map.network.has(hash)) {
      map.network.get(hash).count++
    } else {
      map.network.set(hash, {
        ...data,
        count: 0
      })
    }
  }
}

nightmare.on('message', data => {
  handlers[data.type](data.data)
})

nightmare.on('exit', code => {
  const ret = {}
  Object.keys(map).forEach(k => {
    ret[k] = []
    for (const entry of map[k].entries()) {
      ret[k].push(entry[1])
    }
  })
  process.stdout.write(JSON.stringify(ret))
  process.exit(0)
})
