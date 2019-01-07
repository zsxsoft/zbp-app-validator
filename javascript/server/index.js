const express = require('express')
const app = express()
const path = require('path')
const http = require('http').Server(app)
const io = require('socket.io')(http, {
  origins: ['http://127.0.0.1:21312']
})
const { spawn, exec } = require('child_process')
const { join } = path
const { readFileSync, writeFileSync } = require('fs')
const rootPath = join(__dirname, '../../')
const configPath = join(rootPath, '/config.json')
const defaultConfig = JSON.parse(readFileSync(join(rootPath, '/config.default.json'), 'utf-8'))
const configJson = JSON.parse(readFileSync(join(rootPath, '/config.json'), 'utf-8'))
const nodeModulePath = name => path.join(__dirname, '../../node_modules', name)
let config = require('../shared/config')

app.use('/node_modules/socket.io-client', express.static(nodeModulePath('socket.io-client')))
app.use('/node_modules/xterm', express.static(nodeModulePath('xterm')))
app.use('/node_modules/vue', express.static(nodeModulePath('vue')))
app.use('/node_modules/element-ui', express.static(nodeModulePath('element-ui')))
app.use(express.static(path.join(__dirname, '../gui')))
app.get('/api/config', (req, res) => {
  res.json({
    defaultConfig,
    config
  })
})

io.on('connection', (socket) => {
  const fn = {
    openBrowser: (data) => {
      // ShellExecute
    },
    startLauncher: (arg) => {
      exec(join(rootPath, 'launcher') + ` ${arg}`)
    },
    saveConfig: (data) => {
      writeFileSync(configPath, data, 'utf-8')
      config = JSON.parse(data)
    },
    startChecker: ({ appPath, phpPath: pPath }) => {
      const phpPath = pPath.trim() === '' ? config.phpPath : pPath

      // Save Config, rubbish code here @FIXME
      writeFileSync(configPath, JSON.stringify({
        ...configJson,
        phpPath
      }), 'utf-8')
      config.phpPath = phpPath

      const p = spawn(phpPath, ['checker', 'start', appPath], {
        env: {
          'term': 'xterm'
        },
        shell: true
      })
      p.stdout.on('data', buf => socket.emit('term', buf.toString().replace(/\n/g, '\r\n')))
      p.stderr.on('data', buf => socket.emit('term', buf.toString().replace(/\n/g, '\r\n')))
      p.on('close', () => {
        socket.emit('exit-term')
      })
    }
  }
  Object.keys(fn).forEach(p => socket.on(p, fn[p]))
})

http.listen(21312, '127.0.0.1', function(){
  console.log('listening on *:21312')
})
