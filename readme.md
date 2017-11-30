zbp-app-validator
===================================

Z-BlogPHP App 机器审核工具

服务器版本将基于：https://github.com/zsxsoft/mmp-server

## 已实现功能

### 环境配置
1. 自动下载最新版Z-BlogPHP
1. 创建沙盒Z-BlogPHP环境，每次审核自动清理
1. 自动下载依赖应用，解压应用时自动安装
1. 自动启动内置PHP WebServer，免服务器配置

### 通用审核
1. 静态危险PHP功能与函数扫描
1. 全局变量合规性检测
1. 静态调用数据库随机函数扫描

### 主题审核
1. 离线W3C规范扫描
1. 失效资源与网络资源检测
1. 多终端分辨率截图


## 系统支持

1. 启动器（负责双击打开程序与文件关联）仅支持Windows。
1. 程序主体和界面跨平台。

## 使用

### 前置条件
1. Windows 7+ / macOS 10.10+。
1. 安装了Java Runtime和PHP。

### GUI
Windows用户直接双击``launcher.exe``即可直接使用GUI。其它系统的GUI正在编写启动器。

### 命令行
```bash
php checker help
```
## 功能Roadmap

### 尚未实现
1. 静态裸写SQL扫描。
1. SQL查询语句检测。

### 不在本项目内实现

以下功能受限于技术原因，只能在服务器上实现，故本项目内不再考虑。

1. 动态危险函数扫描。
1. 未过滤输入输出检测。
1. 动态脱加密壳（包括phpjiami等）
1. 网络流量拦截

### 不实现

## 项目构建

```bash
composer update
npm install
```
如果不使用GUI，已经可以启动项目了。

### 生成GUI主题

见：http://element-cn.eleme.io/#/zh-CN/component/custom-theme

```bash
et -c javascript/gui/element-variables.scss -o javascript/gui/element/
```

### 生成Windows启动器

Windows用户打开Visual Studio 2017，直接打开项目文件``launcher\windows-launcher\windows-launcher.sln``并编译。

### 打包

目前仅有Windows打包脚本，直接PowerShell启动``build\\build.ps1``即可。