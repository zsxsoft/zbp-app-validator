(async () => {
  const initialData = await fetch('/api/config').then(p => p.json())
  const { config, defaultConfig } = initialData
  const socket = io()

  const parseHash = () => location.hash.substr(1).split('&').map(v => v.split('=')).reduce( (pre, [key, value]) => ({ ...pre, [key]: decodeURIComponent(value) }), {} )
  let options = parseHash()

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
    const savingConfig = Object.assign({}, config)
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
      socket.emit('saveConfig', JSON.stringify(saveConfig))
    }
  }

  window.term = term
  window.addEventListener('resize', calculateTermSize)
  window.addEventListener('hashchange', () => {
    options = parseHash()
  })

  Vue.use(ELEMENT)
  const app = new Vue({
    el: '#app',
    data: {
      config,
      input: {
        phpPath: '',
        appPath: ''
      },
      fileList: [],
      disableAuditButton: false
    },
    mounted() {
      if (options.path) {
        this.input.appPath = options.path
      }
      this.input.phpPath = this.config.phpPath
      term.open(document.getElementById('terminal'), false)
      term.writeln('Terminal...')
      calculateTermSize()

      socket.on('term', (data) => {
        term.write(data)
      })
      socket.on('exit-term', () => {
        this.disableAuditButton = false
      })
    },
    methods: {
      openBrowser(url) {
        socket.emit('openBrowser', url)
      },
      startLauncher(arg) {
        socket.emit('startLauncher', arg)
      },
      doAudit() {
        saveConfig(this.config)
        const phpPath = this.input.phpPath.trim() === '' ? 'php' : this.input.phpPath
        this.disableAuditButton = true
        term.clear()
        socket.emit('startChecker', {
          phpPath: this.input.phpPath,
          appPath: this.input.appPath
        })
      }
    }
  })

})()

