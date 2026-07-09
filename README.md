# 三勾点餐系统-连锁店版

**面向开发、二开友好的连锁餐饮点餐系统**

三勾点餐系统基于 ThinkPHP 8 + Element Plus + uni-app 打造的面向开发的小程序商城，方便二次开发或直接使用，可发布到多端，包括微信小程序、微信公众号、QQ小程序、支付宝小程序、字节跳动小程序、百度小程序、Android端、iOS端。

---

## 项目简介

三勾点餐系统是一套完整的连锁餐饮点餐解决方案，提供从商品管理、订单处理、门店管理、会员营销到数据统计的全流程业务能力。系统分为后端服务（jjj_food_chain）、SAAS管理后台（jjj_food_chain_admin）、商城端管理后台（jjj_food_chain_shop）、移动端前端（jjj_food_chain_app）四个部分。

## 项目特色

- **SAAS支持**：无限多开、实现多租户应用开发
- **前后分离**：开发更清晰、分工更明确、提升开发效率
- **Element Plus**：基于饿了么团队 UI 库、用户体验超棒
- **ThinkPHP 8**：国内流行的 PHP 框架、结构代码清晰
- **极易二开**：代码结构清晰、快速开发应用
- **多平台支持**：微信小程序、H5、微信公众号、支付宝小程序、App 打包，开发不浪费
- **开发规范**：前后端高度一致的权限控制、实现项目规范
- **三端分离**：SAAS管理后台（admin）、商城端管理（shop）、移动端（app）独立开发，共用后端服务
- **模块化设计**：后端采用多应用模式，各业务模块职责清晰，易于扩展

## 技术栈

| 层级 | 技术 |
|------|------|
| 后端框架 | PHP 8.0+ + ThinkPHP 8.1 |
| ORM 框架 | ThinkORM 3.0 |
| 权限框架 | JWT + 自定义权限控制 |
| 缓存 | Redis |
| 数据库 | MySQL 5.7+ / 8.0+ |
| SAAS管理端前端 | Vue 3 + Vite + Element Plus + Pinia |
| 商城端前端 | Vue 3 + Vite + Element Plus + Pinia |
| 移动端前端 | uni-app + Vue 3（支持微信小程序、H5、多端发布） |
| 微信集成 | EasyWeChat |
| 云存储 | 七牛云 / 阿里云 OSS / 腾讯云 COS |
| 支付集成 | 微信支付、支付宝支付 |

## 项目源码

| 项目目录 | 说明 | 开发工具 | 核心技术 |
|---------|------|---------|---------|
| db | 数据库 | MySQL 5.7、MySQL 8.0 | - |
| jjj_food_chain | PHP 后端 | PhpStorm、Sublime Text | ThinkPHP 8.1 |
| jjj_food_chain_admin | SAAS管理后台 | HBuilder X、VS Code等JS开发工具 | Vue 3、Element Plus |
| jjj_food_chain_shop | 商城端管理后台 | HBuilder X、VS Code等JS开发工具 | Vue 3、Element Plus |
| jjj_food_chain_app | 移动端 | HBuilder X | Vue 3、uni-app |

## 后端架构

### 模块划分

后端采用 ThinkPHP 多应用模式，各模块职责清晰：

```
jjj_food_chain/
├── app/                        # 应用目录
│   ├── admin/                  #   SAAS管理端应用（平台运营）
│   │   ├── controller/         #     管理端控制器
│   │   ├── model/              #     管理端模型
│   │   └── service/            #     管理端服务
│   ├── shop/                   #   商城端应用（商家管理）
│   │   ├── controller/         #     商城端控制器
│   │   ├── model/              #     商城端模型
│   │   └── service/            #     商城端服务
│   ├── api/                    #   移动端应用（用户端）
│   │   ├── controller/         #     移动端控制器
│   │   ├── model/              #     移动端模型
│   │   └── service/            #     移动端服务
│   └── common/                 #   公共模块
│       ├── model/              #     公共模型
│       ├── service/            #     公共服务
│       └── library/            #     公共类库
├── config/                     # 配置文件
├── public/                     # 入口文件（网站根目录）
├── runtime/                    # 运行时目录
├── vendor/                     # Composer 依赖
└── extend/                     # 扩展类库
```

关键设计：
- 三端（admin/shop/api）共用同一套 Model、Service
- 通过不同的 Controller 层实现接口隔离和权限控制
- 公共业务逻辑统一在 common 模块维护

### 核心能力

| 能力 | 实现 |
|------|------|
| 统一返回格式 | JSON 格式封装 code/message/data |
| 认证鉴权 | JWT Token（签发/校验/刷新） |
| 参数校验 | ThinkPHP 验证器 + 自定义规则 |
| XSS 防护 | 输入过滤 + 输出转义 |
| 文件上传 | 工厂模式，支持本地/七牛云/阿里云/腾讯云 |
| 分页查询 | ThinkORM 分页功能 |
| 全局异常 | 统一异常处理 |
| 操作日志 | 中间件自动记录 |
| 定时任务 | ThinkPHP 定时任务 |
| 微信支付 | 微信小程序支付、微信公众号支付 |
| 物流查询 | 快递100 物流查询接口 |
| 打印功能 | 小票打印、订单打印 |

### 多端 API 隔离

后端通过应用目录区分不同端的 API：

| 端 | 应用目录 | 对应前端 | 认证方式 |
|----|---------|---------|---------|
| SAAS管理端 | `app/admin/` | `jjj_food_chain_admin` | Header Token |
| 商城端 | `app/shop/` | `jjj_food_chain_shop` | Header Token |
| 移动端 | `app/api/` | `jjj_food_chain_app` | app_id + Token |

## 项目结构

```
根目录/
├── db/                               # 数据库文件
├── jjj_food_chain/                   # PHP 后端代码（ThinkPHP 8.1）
│   ├── app/                          #   应用目录
│   │   ├── admin/                    #     SAAS管理端应用
│   │   ├── shop/                     #     商城端应用
│   │   ├── api/                      #     移动端应用
│   │   └── common/                   #     公共模块
│   ├── config/                       #   配置文件
│   ├── public/                       #   公共资源（网站根目录）
│   ├── runtime/                      #   运行时目录
│   ├── vendor/                       #   Composer 依赖
│   └── extend/                       #   扩展类库
├── jjj_food_chain_admin/             # SAAS管理后台（Vue 3 + Element Plus）
│   ├── src/                          #   源代码
│   │   ├── views/                    #     页面视图
│   │   ├── components/               #     公共组件
│   │   ├── api/                      #     接口封装
│   │   ├── router/                   #     路由配置
│   │   ├── store/                    #     状态管理
│   │   └── utils/                    #     工具函数
│   ├── public/                       #   静态资源
│   └── package.json                  #   依赖配置
├── jjj_food_chain_shop/              # 商城端管理后台（Vue 3 + Element Plus）
│   ├── src/                          #   源代码
│   │   ├── views/                    #     页面视图
│   │   ├── components/               #     公共组件
│   │   ├── api/                      #     接口封装
│   │   ├── router/                   #     路由配置
│   │   ├── store/                    #     状态管理
│   │   └── utils/                    #     工具函数
│   ├── public/                       #   静态资源
│   └── package.json                  #   依赖配置
├── jjj_food_chain_app/               # 移动端（uni-app）
│   ├── pages/                        #   页面文件
│   ├── components/                   #   组件
│   ├── static/                       #   静态资源
│   ├── api/                          #   接口封装
│   ├── utils/                        #   工具函数
│   ├── store/                        #   状态管理
│   └── manifest.json                 #   uni-app 配置
```

## 功能模块

### SAAS管理端（jjj_food_chain_admin）

面向平台超级管理员使用，管理整个系统的基础配置。

| 模块 | 功能 |
|------|------|
| 店铺管理 | 店铺列表、店铺审核、店铺配置 |
| 权限管理 | 管理员账号、角色权限、菜单管理 |
| 区域管理 | 省市区数据维护 |
| 系统设置 | 系统参数配置、全局设置 |
| 应用管理 | 插件管理 |

### 商城端（jjj_food_chain_shop）

面向店铺运营人员使用，管理店铺日常运营。

| 模块 | 功能 |
|------|------|
| 首页看板 | 数据概览、待处理事项、快捷入口 |
| 商品管理 | 商品列表、商品分类、商品规格、商品评价 |
| 订单管理 | 订单列表、订单详情、订单发货、退款/售后处理 |
| 会员管理 | 会员列表、会员等级、会员标签、余额明细 |
| 营销中心 | 优惠券管理、文章管理、文章分类、专题管理、推荐位、收藏管理 |
| 门店管理 | 门店列表、门店店员、门店订单（自提/核销） |
| 页面装修 | 首页装修、分类页装修、个人中心装修、底部导航、主题风格 |
| 数据统计 | 销售统计、用户统计 |
| 应用设置 | 小程序配置、微信配置 |
| 系统设置 | 店铺信息、交易设置、配送方式、快递公司、退货地址、上传设置、打印机、打印模板、短信设置、客服设置 |
| 权限管理 | 店铺账号、角色权限、登录日志、操作日志 |
| 文件管理 | 文件库、文件分组 |

### 移动端（jjj_food_chain_app）

面向终端用户使用（微信小程序/H5）。

| 模块 | 功能 |
|------|------|
| 首页 | 轮播图、商品推荐、分类导航、营销活动入口 |
| 商品浏览 | 商品列表、商品详情、商品搜索、商品分类 |
| 购物车 | 加入购物车、购物车管理、批量结算 |
| 订单流程 | 确认订单、收银台支付、订单列表、订单详情、物流跟踪、订单评价 |
| 售后服务 | 申请退款、退款详情 |
| 会员中心 | 个人信息、收货地址管理、我的优惠券、我的收藏、我的钱包、积分明细 |
| 营销活动 | 优惠券领取 |
| 内容浏览 | 文章列表、文章详情 |
| 门店服务 | 门店列表、门店详情、门店订单（自提） |
| 自定义页面 | 支持 DIY 页面装修 |
| 微信功能 | 微信登录、微信支付、手机号绑定 |

## 系统功能

![系统功能](https://www.jjjshop.net/gitee/food/gongneng.png)

## 环境要求

| 环境类型 | 开发工具 | 版本要求 |
|---------|---------|---------|
| PHP后端 | PhpStorm、Sublime Text | PHP 8.0+、ThinkPHP 8.1 |
| 后端Vue管理页面 | HBuilder X、VS Code等JS开发工具 | Node 16+（推荐16） |
| 前端页面 | HBuilder X | uni-app |
| 数据库 | MySQL | MySQL 5.7+ / 8.0+ |
| 缓存 | Redis | 3.0+ |
| Web服务器 | Nginx / Apache | 推荐 Nginx |

⚠️ **注意**：Node.js 16 版本以下存在兼容性问题，请勿使用

## 快速开始

### 1. 数据库安装

```bash
# MySQL 5.7 或 8.0 新建数据库 jjj_food_chain
CREATE DATABASE jjj_food_chain CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 导入脚本 database/init.sql
```

### 2. Redis 安装

```bash
# 安装 Redis，设置密码（根据实际情况配置）
```

### 3. 后端启动

```bash
# 使用 phpenv 或者 phpstudy 集成环境，建立站点
# 根目录指向 jjj_food_chain/public

# 用 PhpStorm 打开 jjj_food_chain 目录

# 安装 Composer 依赖
composer install

# 修改 jjj_food_chain/.env 里面的数据库配置和 Redis 配置
# 配置 MySQL 和 Redis 连接信息

# 配置伪静态（Nginx）
# 将 public 目录设置为网站根目录
```

### 4. 商城端页面启动

```bash
# cmd 进入 jjj_food_chain_shop 目录
cd jjj_food_chain_shop

# 安装依赖
npm install

# 修改 jjj_food_chain_shop/.env.development 里面的后端地址
# 默认根据实际情况修改（如 http://127.0.0.1）

# 启动开发服务器
npm run dev

# 默认账号：admin
# 默认密码：123456
```

### 5. SAAS管理端页面启动

```bash
# cmd 进入 jjj_food_chain_admin 目录
cd jjj_food_chain_admin

# 安装依赖
npm install

# 修改 .env.development 里面的后端地址
# 启动开发服务器
npm run dev
```

### 6. uni-app 移动端启动

```bash
# 用 HBuilder X 打开 jjj_food_chain_app 目录
# 修改 manifest.json 文件，可视化修改 Web配置 -> 路由模式为 hash

# 修改 env/development.js 里面的域名
# 配置后端接口地址

# 点击菜单 运行 -> 运行到浏览器 -> Chrome

# 或编译到微信小程序
# 点击菜单 运行 -> 运行到小程序模拟器 -> 微信开发者工具
```

## 部署说明

### 生产环境打包

```bash
# 后端部署
# 将 jjj_food_chain 目录上传到服务器
# 配置 Nginx 虚拟主机，根目录指向 public
# 修改 .env 配置文件为生产环境配置

# 商城端打包
cd jjj_food_chain_shop
npm run build

# SAAS管理端打包
cd jjj_food_chain_admin
npm run build

# 移动端打包
# 使用 HBuilder X 打开 jjj_food_chain_app
# 点击菜单 发行 -> 小程序-微信（仅适用于uni-app）
# 或 发行 -> H5
```

### Nginx 配置示例

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/jjj_food_chain/public;
    index index.php index.html;

    # 开启 gzip 压缩
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    location / {
        if (!-e $request_filename) {
            rewrite ^(.*)$ /index.php?s=$1 last;
            break;
        }
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 禁止访问敏感文件
    location ~ /\.(env|git|svn) {
        deny all;
    }
}
```

### 部署建议

- **后端**：使用 Nginx 反向代理 + PHP-FPM
- **前端**：静态文件部署到 Nginx，配置 gzip 压缩
- **数据库**：定期备份，开启慢查询日志
- **Redis**：配置持久化，设置合理的过期策略
- **文件存储**：生产环境建议使用云存储（七牛云/阿里云/腾讯云）
- **HTTPS**：配置 SSL 证书，启用 HTTPS
- **性能优化**：开启 OPcache，配置 Redis 缓存

## 项目截图

### 移动端截图

| ![移动端1](https://www.jjjshop.net/gitee/food/ky01.jpg) | ![移动端2](https://www.jjjshop.net/gitee/food/ky02.jpg) | ![移动端3](https://www.jjjshop.net/gitee/food/ky03.jpg) |
|---|---|---|
| ![移动端4](https://www.jjjshop.net/gitee/food/ky04.jpg) | ![移动端5](https://www.jjjshop.net/gitee/food/ky05.jpg) | ![移动端6](https://www.jjjshop.net/gitee/food/ky06.jpg) |

### 后台截图

| ![后台截图1](https://www.jjjshop.net/gitee/food/k01.png) | ![后台截图2](https://www.jjjshop.net/gitee/food/k02.png) |
|---|---|
| ![后台截图3](https://www.jjjshop.net/gitee/food/k03.png) | ![后台截图4](https://www.jjjshop.net/gitee/food/k04.png) |

## 开发指南

| 名称 | 地址 |
|------|------|
| 官方文档 | https://doc.jjjshop.net/chain |
| 视频教程 | https://doc.jjjshop.net/chain?category_id=10029&document_id=1243 |
| 本地安装 | https://doc.jjjshop.net/chain?category_id=10029&document_id=1253 |
| 线上部署 | https://doc.jjjshop.net/chain?category_id=10029&document_id=353 |
| 二开说明 | https://doc.jjjshop.net/chain?category_id=10029&document_id=1257 |
| 功能说明 | https://doc.jjjshop.net/chain?category_id=10029&document_id=358 |
| 常见问题 | https://doc.jjjshop.net/chain?category_id=10029&document_id=394 |

## 特别感谢

- Gitee 官方
- [Element Plus](https://element-plus.gitee.io/zh-CN/) - 基于 Vue 3 的组件库
- [Vue.js](https://cn.vuejs.org/) - 渐进式 JavaScript 框架
- [ThinkPHP](https://www.thinkphp.cn/) - 优秀的 PHP 框架
- [uni-app](https://uniapp.dcloud.io/) - 跨平台应用开发框架
- [EasyWeChat](https://www.easywechat.com/) - 微信开发 SDK

## 技术支持

- **官网地址**：[https://www.jjjshop.net](https://www.jjjshop.net)
- **交流QQ群**：618300870
- **问题反馈**：提交 Issue

## 开源协议

本项目遵循 Apache-2.0 开源协议

## 免责声明

本项目仅供学习交流使用，请勿用于非法用途。使用本项目所产生的一切后果由使用者自行承担。

---

⭐ 如果这个项目对您有帮助，请给我们一个 Star 支持一下！