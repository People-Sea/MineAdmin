# 风起（批量广告投放系统）腾讯接口基线清单（MVP）

## 1. 文档目的
本清单用于锁定“广告数据拉取与复审 MVP 首期接口范围”，避免开发过程中反复改接口、改字段和改版本。

当前结论：

- 文档主入口使用腾讯广告官方开发者门户：`developers.e.qq.com/v3.0`
- 每个接口的 `API_VERSION` 以对应接口页当前声明为准
- 首期开发前必须冻结一版“接口 + 版本 + 字段”基线，再开始后端实现
- 官方 SDK 可作为“接口目录发现与变更追踪”辅助来源：
  - `TencentAd/marketing-api-php-sdk`
  - `tencentad/marketing-api-go-sdk`

## 2. 版本口径

- 门户版本：`v3.0`（开发者站点入口）
- 接口版本：按接口页声明，不在本项目文档中一次性写死
- 执行要求：本清单中的“版本快照”列必须在开发启动当天复核并落签

## 2.1 来源优先级

- 第一优先级：官方接口页面（版本、字段、约束、错误码）
- 第二优先级：官方 SDK（用于快速盘点接口目录和跟踪新增/下线）
- 第三优先级：历史镜像或社区整理文档（仅用于交叉排查，不直接作为开发依据）

## 3. 首期接口清单（拉取 + 编辑 + 复审主链路）

| 模块 | 接口动作 | 接口标识 | 版本快照 | 锁定状态 | 备注 |
| --- | --- | --- | --- | --- | --- |
| OAuth | 获取 Authorization Code | `oauth/authorize` | 已核对 | 待锁定 | 回调返回 `authorization_code` 与 `state`，`authorization_code` 当前页面注明有效期 5 分钟 |
| OAuth | 换取应用 token | `oauth/token` | 已核对 | 待锁定 | 支持 `authorization_code` 与 `refresh_token` 两种 `grant_type` |
| OAuth | 刷新应用 token | `oauth/refresh_token` | 已核对 | 待锁定 | 独立刷新接口仍在 `apilist` 中保留 |
| 账号与授权 | 拉取广告主账号列表 | `advertiser/get` | 已核对 | 待锁定 | 当前主导入接口；对广告主与代理商的 `account_id` 口径不同 |
| 账号与授权 | 查询组织下广告账户信息 | `organization_account_relation/get` | 已核对 | 待锁定 | 组织 token 场景补充使用，不替代 `advertiser/get` |
| 广告层 | 查询广告列表 | `adgroups/get` | 已核对 | 待锁定 | 官方页面正文为“获取广告” |
| 广告层 | 更新广告 | `adgroups/update` | 已核对 | 待锁定 | 编辑范围偏广告自身配置 |
| 创意层 | 查询创意列表 | `dynamic_creatives/get` | 已核对 | 待锁定 | 可按 `adgroup_id`、`component_id` 过滤 |
| 创意层 | 更新创意 | `dynamic_creatives/update` | 已核对 | 待锁定 | 首期最复杂接口 |
| 组件层 | 查询创意组件列表 | `components/get` | 已核对 | 待锁定 | 返回组件库存与 `component_value` |
| 组件层 | 查询创意组件详情 | `component_detail/get` | 已核对 | 待锁定 | 查看组件具体结构 |
| 组件层 | 新增创意组件 | `components/add` | 已核对 | 待评估 | `apilist` 已存在，是否进入首期取决于实际编辑链路 |
| 组件层 | 删除创意组件 | `components/delete` | 已核对 | 待评估 | `apilist` 已存在，是否进入首期取决于实际编辑链路 |
| 审核层 | 查询创意审核结果 | `dynamic_creative_review_results/get` | 已核对 | 待锁定 | 按 `dynamic_creative_id_list` 查询 |
| 审核层 | 查询组件审核结果 | `component_review_results/get` | 已核对 | 待锁定 | 按 `component_id_list` 查询 |
| 复审层 | 发起元素申诉复审 | `element_appeal_review/add` | 已核对 | 待锁定 | 粒度到 `dynamic_creative_id + component_id + element_id` |
| 复审层 | 查询元素申诉复审结果 | `element_appeal_review/get` | 已核对 | 待锁定 | 回查申诉处理状态 |
| 催审层 | 发起组件/元素催审 | `component_element_urge_review/add` | 已核对 | 待锁定 | 支持 `component` 或 `element` 维度 |
| 催审层 | 查询组件/元素催审状态 | `component_element_urge_review/get` | 已核对 | 待锁定 | 回查催审状态 |

说明：

- 广告、创意、组件三层在官方页面中的命名与旧 SDK 命名不完全一致，开发时以官方页面路径为准
- 广告主账号导入接口当前不应再使用 `adaccounts/get` 这类历史命名占位，首期文档以 `advertiser/get` 为主
- 账号授权与执行身份是两条链路：OAuth 负责 `access_token / refresh_token`，`user_token` 是受限接口的实名认证参数
- `dynamic_creatives/update` 当前是首期最重接口，应单独做字段白名单
- 一旦锁定进入开发，变更必须走“文档更新 + 任务拆分更新 + 表结构影响评估”

## 4. 请求口径基线

- `oauth/token` 页面当前明确说明：OAuth 相关接口无需提供 `access_token`、`timestamp`、`nonce` 等通用请求参数
- 重试必须复用同一业务单元幂等键，避免重复提交和状态错乱
- 涉及执行身份的受限接口按官方当前参数口径传递 `user_token`，不自行改为请求头
- 请求日志与任务日志需可关联：`task_no`、`item_no`、`request_idempotency_key`

## 5. 首期冻结标准

满足以下条件后，才允许进入“拉取 + 编辑 + 复审后端开发”：

1. 本文档“首期接口清单”全部从 `待锁定` 变为 `已锁定`
2. 每个已锁定接口补齐：版本、核心字段、必填约束、幂等策略、错误码处理规则
3. `docs/tencent-ads-batch-create-task-list.md` 中对应任务同步更新
4. 表设计文档确认字段可承载已锁定接口的请求/响应快照与编辑审计快照

## 6. 变更记录

- 2026-04-02：初版建立，作为“首期接口清单与版本锁定”承载文档
- 2026-04-02：按腾讯广告 v3.0 官方文档复核，修正广告主账号导入接口、OAuth 授权链与 `user_token` 执行参数口径
