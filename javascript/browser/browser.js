const path = require('path')
const cp = require('child_process')
const config = require('../shared/config')
const currentElectronPath = typeof(require('electron')) === 'string' ? currentElectronPath : require('electron').app.getPath('exe')

const nightmare = cp.spawn(currentElectronPath, [path.join(__dirname, '/child-process')], {
  stdio: [0, 2, 2, 'ipc'],
}) // fork has bug in electron

const ret = {
  console: [],
  network: []
}
nightmare.on('message', data => {
  switch (data.type) {
    case 'network':
      ret.network.push(data.data)
      break
    case 'console':
      ret.console.push(data.data)
      break
  }
})

nightmare.on('exit', code => {
  process.stdout.write(JSON.stringify(ret))
  process.exit(0)
})
