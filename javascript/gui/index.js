(() => {
  const Module = require('module')
  const origRequire = Module._load
  Module._load = function (request, parent, isMain) {
    if (request === 'vue') { // element-ui will load Vue..
      request = 'vue/dist/vue.common.js'
    }
    return origRequire(request, parent, isMain)
  }
})();

(() => {
  const Vue = require('vue')
  const ElementUI = require('element-ui')

  const { dialog, getCurrentWindow, process, shell } = require('electron').remote
  const { spawn, exec } = require('child_process')
  // const fontManager = require('font-manager')
  const { join } = require('path')
  const { readFileSync, writeFileSync } = require('fs')
  const rootPath = join(__dirname, '../../')
  const configPath = join(rootPath, '/config.json')
  const config = require('../shared/config')
  const defaultConfig = JSON.parse(readFileSync(join(rootPath, '/config.default.json'), 'utf-8'))
  const currentWindow = getCurrentWindow()
  const argv = process.argv
  // const fontFamilies = fontManager.getAvailableFontsSync().map(p => p.family)
  // const monoFonts = ['Source Code Pro', 'Noto Mono', 'DejaVu Sans Mono', 'Monospace', 'Consolas'].filter(p => fontFamilies.includes(p))

  const term = new Terminal()
  const calculateTermSize = () => {
    // magic numbers!
    const bodyStyle = window.getComputedStyle(document.getElementsByClassName('app')[0])
    const fontSize = 9 //parseInt(bodyStyle.getPropertyValue('font-size'), 10)
    // const lineHeightStr = bodyStyle.getPropertyValue('line-height')
    const lineHeight = 20 //parseInt(lineHeightStr === 'normal' ? 1.14 * fontSize : lineHeightStr, 10)
    const loggerDiv = window.getComputedStyle(document.getElementsByClassName('logs')[0])
    const innerWidth = parseInt(loggerDiv.getPropertyValue('width'), 10)
    const innerHeight = parseInt(bodyStyle.getPropertyValue('height'), 10) - 150
    term.resize(Math.floor(innerWidth / fontSize), Math.floor(innerHeight / lineHeight))
  }
  const saveConfig = newConfig => {
    const savingConfig = JSON.parse(readFileSync(configPath, 'utf-8'))
    const keys = ['builtinServer', 'host', 'zbpPath']
    let saveFlag = false
    if (newConfig.builtinServer && !savingConfig.builtinServer) {
      saveFlag = true
      keys.map(key => {
        savingConfig[key] = defaultConfig[key]
      })
    } else if (!newConfig.builtinServer) {
      saveFlag = true
      keys.map(key => {
        savingConfig[key] = newConfig[key]
      })
    }
    console.log(savingConfig)
    if (saveFlag) {
      writeFileSync(configPath, JSON.stringify(savingConfig), 'utf-8')
    }
  }

  currentWindow.setIcon(join(rootPath, 'resources/Logo.png'))
  window.term = term
  window.addEventListener('resize', calculateTermSize)

  Vue.use(ElementUI)
  const app = new Vue({
    el: '#app',
    data: {
      config,
      input: {
        phpPath: '',
        appPath: ''
      },
      disableAuditButton: false
    },
    mounted () {
      if (argv.length >= 2) {
        this.input.appPath = argv[2]
      }
      term.open(document.getElementById('terminal'), false)
//      if (monoFonts.length > 0) {
//        term.setOption('fontFamily', monoFonts[0])
//      }
      term.writeln('Terminal...')
      calculateTermSize()
    },
    methods: {
      openBrowser (url) {
        shell.openExternal(url)
      },
      startLauncher (arg) {
        exec(join(rootPath, 'launcher') + ` ${arg}`)
      },
      browsePHPPath () {
        dialog.showOpenDialog(currentWindow, {
          filters: [
            {name: 'PHP Executable (php.exe, php)', extensions: ['php.exe', 'php']},
            {name: 'All Files', extensions: ['*']}
          ]
        }, paths => {
          if (paths && paths.length) {
            this.input.phpPath = paths[0]
          }
        })
      },
      browseAppId () {
        dialog.showOpenDialog(currentWindow, {
          filters: [
            {name: 'zba file (.zba, .gzba)', extensions: ['zba', 'gzba']}
          ]
        }, paths => {
          if (paths && paths.length) {
            this.input.appPath = paths[0]
          }
        })
      },
      browseZBPPath () {
        dialog.showOpenDialog(currentWindow, {
          properties: ['openDirectory']
        }, paths => {
          if (paths && paths.length) {
            this.config.zbpPath = paths[0]
          }
        })
      },
      doAudit () {
        saveConfig(this.config)
        const phpPath = this.input.phpPath.trim() === '' ? 'php' : this.input.phpPath
        this.disableAuditButton = true
        term.clear()
        const p = spawn(phpPath, ['checker', 'start', this.input.appPath], {
          env: {
            'term': 'xterm'
          },
          shell: true
        })
        p.stdout.on('data', buf => term.write(buf.toString().replace(/\n/g, '\r\n')))
        p.stderr.on('data', buf => term.write(buf.toString().replace(/\n/g, '\r\n')))
        p.on('exit', () => {
          this.disableAuditButton = false
        })
      }
    }
  })

})()
