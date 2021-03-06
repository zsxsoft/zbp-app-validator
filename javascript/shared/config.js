const deepAssign = require('deep-assign')
const fs = require('fs')
const path = require('path')
const defaultConfig = require('../../config.default')
defaultConfig.rootPath = path.join(__dirname, '../../')
defaultConfig.tempPath = path.join(defaultConfig.rootPath, '/tmp')

if (fs.existsSync(path.join(defaultConfig.rootPath, '/config.json'))) {
  const userConfig = require(path.join(defaultConfig.rootPath, '/config.json'))
  module.exports = deepAssign({}, defaultConfig, userConfig)
} else {
  module.exports = defaultConfig
}
