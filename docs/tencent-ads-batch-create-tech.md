# 风起（批量广告投放系统）技术文档（腾讯广告存量资产 MVP 实施版）

## 1. 文档目的
本文档面向“风起（批量广告投放系统）”的开发实现，记录当前已经确认的腾讯广告一期 MVP 技术共识与实施顺序。

新开对话后，优先阅读：

- `docs/tencent-ads-batch-create-prd.md`
- `docs/tencent-ads-batch-create-tech.md`
- `docs/tencent-ads-batch-create-task-list.md`
- `docs/tencent-ads-batch-create-table-design.md`
- `docs/tencent-ads-api-baseline.md`
- `docs/tencent-ads-v3-official-reading-notes.md`
- `docs/功能设计/功能设计总览.md`

## 2. 当前 MVP 共识
### 2.1 总原则
- 先做最小闭环，不做大而全组织体系。
- 一期 MVP 先做腾讯广告存量资产拉取、编辑和复审；批量创建与统一任务中心放到二期。
- 先保证能跑通租户、项目、员工、账号授权、获取 `user_token` 执行身份、广告数据拉取与复审。
- 保留扩展位，但当前不引入复杂部门树、复杂角色树。
- 广告、创意、创意组件数据都先跟项目权限走，不做租户级默认共享。
- 腾讯广告接口对接以当前官方开发者官网 `developers.e.qq.com/v3.0` 作为主入口。
- 具体请求 `API_VERSION` 不在项目文档中写死，以每个接口页面的当前声明为准；首期实现先固定接口清单与对应版本号，再进入开发。
- 官方 SDK 用于快速枚举接口目录和追踪接口变更，但字段与约束仍以官方接口页面为准。

### 2.2 当前实现边界
- MVP 租户能力统一基于现有 `MineAdmin user/role/menu` 体系实现。
- 租户与项目成员关系通过业务表建模，不单独建设平行认证链路。

### 2.3 平台与租户分离
- 平台与租户统一使用 `/login` 登录入口。
- 平台与租户共用现有 `MineAdmin user/role/menu` 体系。
- 平台账号不套租户/项目作用域。
- 租户隔离主要通过统一工作域作用域、`user.tenant_id`、`tenant_project_user`、项目与账号授权关系完成。

### 2.4 腾讯广告账号体系简化说明
腾讯广告账号体系历史包袱较重，存在“腾讯广告服务台 / 商务管家 / BM / 客户工作台 / 服务商系统 / 自理投放管理员”等多套概念。

从开发实现角度，不建议把这些腾讯侧旧名词直接映射为系统核心模型，否则后续很容易被官方产品演进拖着改表、改流程。

MVP 阶段建议统一按以下方式理解：

- 真正用于投放与建广告的是 `广告主账户`
- 租户授权广告主账号走 OAuth 授权码模式，回调拿到的是 `authorization_code`
- 本地员工执行受限接口时另行获取 `user_token`
- 腾讯第三方应用由平台统一管理，并按租户分配使用

产品层直接围绕“广告主账户”提供操作，不单独暴露“管家账号 / 授权主体”菜单。
技术层保留一层 OAuth 授权来源记录，用于保存授权身份、token 刷新和账号同步来源。

产品页面命名与内部模型建议分开：

- 产品页面使用 `团队人员管理 / 团队项目管理 / 投放账号管理 / 广告主账号`
- 技术实现内部使用 `platform_app / tenant_app_assignment / tenant_auth_grant / tenant_ad_account`

其中：

- `platform_app` 是平台维护的应用配置，不属于租户侧可编辑菜单
- `tenant_app_assignment` 是平台分配给租户的应用关系
- `广告主账号` 是投放账号管理中的真实投放对象
- “账号授权”不是独立菜单，而是两个动作：
  - 在 `团队项目管理` 中配置项目成员
  - 在 `广告主账号` 页中分配员工可操作的账户

建议内部抽象如下：

- `platform_app`
  - 表示平台统一维护的腾讯第三方应用配置
  - 用于保存 `app_id`、密钥、回调、状态、校验结果
- `tenant_app_assignment`
  - 表示平台分配给租户的应用关系
  - 用于保存 `tenant_id`、`platform_app_id`、状态、分配时间
- `tenant_auth_grant`
  - 表示租户使用平台应用发起的一次 OAuth 授权来源记录
  - 用于保存 `authorization_code` 回调快照、`access_token / refresh_token`、过期时间、同步范围
- `tenant_ad_account`
  - 表示真正执行广告投放的广告主账户
- `tenant_project_ad_account`
  - 表示项目与广告主账户的归属关系
- `tenant_member_ad_account`
  - 表示员工对广告主账户的操作授权关系

MVP 绑定链路统一为：

```text
平台管理应用
-> 分配应用给租户
-> 租户在广告主账号中直接发起授权
-> OAuth 回调拿到 authorization_code
-> 系统内部创建授权来源记录并换取 access_token / refresh_token
-> 使用 `advertiser/get` 为主、必要时结合 `organization_account_relation/get` 拉取并选择广告主账户
-> 归属到项目
-> 配置项目成员
-> 授权给员工
-> 员工获取可用 `user_token` 后执行
```

这样做有几个好处：

- 平台统一保管应用密钥，租户不直接接触敏感配置
- 产品层仍然保持“先项目、再账号、再执行”的简单链路
- 后续如确实需要中间接入层，再单独补充模型也不迟

需要注意：

- 租户授权广告主账户时，必须先校验是否存在平台分配且可用的应用
- 租户侧不允许自行修改 `app_id`、密钥、回调等平台应用配置

## 3. 推荐 MVP 架构
### 3.1 平台域
复用现有 `MineAdmin` 作为后台基础框架：

- 平台用户
- 平台角色
- 平台菜单权限
- 平台日志

平台域只负责：

- 租户开通、停用
- 平台应用管理与分配
- 平台审计
- 全局告警

### 3.2 租户业务域
MVP 先建最少模型：

- `tenant`
- `platform_app`
- `tenant_app_assignment`
- `tenant_auth_grant`
- `tenant_project`
- `user`（复用，新增 `tenant_id`、`last_project_id`）
- `tenant_project_user`
- `tenant_ad_account`
- `tenant_project_ad_account`
- `tenant_member_ad_account`
- `user_token_session`
- `user_token`
- 广告本地快照
- 创意本地快照
- 创意组件本地快照
- 审核结果快照
- 编辑与复审审计记录

### 3.3 为什么先不做复杂角色表
MVP 阶段，租户角色直接复用 `MineAdmin` 的 `role` 表与角色菜单权限，不再额外新增 `tenant_role` 体系。

当前默认只启用两个租户角色：

- `TenantAdmin`
- `Optimizer`

团队人员管理页只负责给租户用户分配这些“可分配租户角色”，不再平行维护字符串角色枚举。

## 4. MVP 产品组织模型
```text
平台
├── 应用
└── 租户
    ├── 主项目
    ├── 项目
    └── 员工

项目
├── 广告主账号
├── 广告
├── 创意
├── 创意组件
└── 审核结果
```

租户是管理边界，项目是系统主工作上下文。

关键规则：

- 平台应用由平台统一管理并分配给租户，不属于租户/项目级可编辑对象。
- 平台与租户统一使用 `/login`，底层共用同一套 `user` 体系。
- 不再保留租户旧入口 `/tenant/login`。
- 租户登录凭证使用 `username + password`。
- 新租户自动创建一个主项目。
- 员工通过“加入项目”获得项目访问权。
- 租户授权广告主账号时必须使用平台已分配应用。
- 产品上直接在 `广告主账号` 中发起授权，不单独暴露授权来源层。
- 系统内部保留 `tenant_auth_grant` 记录 OAuth 授权来源和同步依据。
- 项目创建与广告主账号导入是两个动作；账号归项目在 `投放账号管理` 中完成。
- 广告主账号归属到项目，再授权给员工。
- 广告、创意、创意组件、审核结果都默认归项目管理。
- 租户侧业务页面默认在某个当前项目下运行，不按全租户平铺展示。
- 员工登录后，如只加入一个项目则默认进入该项目；如加入多个项目，则允许切换当前项目。
- 当前 MVP 只保留两类租户角色：
  - `TenantAdmin`
  - `Optimizer`

租户后台产品菜单建议为：

- 工作台
- 配置管理
  - 团队人员管理
  - 团队项目管理
  - 投放账号管理
    - 广告主账号
- 广告管理
- 审核与日志
- 报表与日志

## 5. 关键实现原则
### 5.1 表设计原则
- 所有业务核心表带 `tenant_id`
- 项目级业务表带 `project_id`
- 租户侧可见的登录日志、操作日志等审计表也带 `tenant_id`
- 平台应用表不带 `tenant_id`、`project_id`
- 租户应用分配关系表带 `tenant_id`，不带 `project_id`
- 可在员工侧预留 `last_project_id` 一类字段，用于记录最近使用项目
- 当前一期以项目内广告/创意/组件快照和审核快照为主，二期再补任务中心主表与明细表
- 执行链路相关表必须记录：
  - `operator_user_id`
  - `executor_user_id`
  - `user_token_session_id`
  - `user_token_id`

### 5.2 项目上下文原则
- 平台账号默认不进入租户/项目作用域。
- 租户账号默认先进入 `tenant_id` 作用域。
- 租户侧绝大多数业务接口必须在项目上下文中执行。
- 列表、详情、审核结果、投放账号授权等接口优先按 `project_id` 过滤数据。
- 权限校验顺序固定为：
  - 先校验 `tenant_id`
  - 再校验项目成员关系
  - 再校验广告主账户授权
- 租户管理员可以切换本租户下任意项目；优化师只能切换自己已加入项目。

### 5.3 凭证原则
- 执行凭证统一按官方参数名 `user_token` 记录，不再与 OAuth token 混用。
- 腾讯 OAuth 回调只负责返回 `authorization_code` 与 `state`，不负责返回 `user_token`。
- 受限接口页当前明确写明：`user_token` 是“实名认证完成获取的令牌”，且必须作为请求参数传递，不放在 header 中。
- `user_token` 的获取方式与有效期以腾讯当前实名对接文档为准，项目文档中不写死 15 天。
- `wechat_nickname` 只用于展示和审计，不作为唯一身份或权限依据。
- `user_token` 按本地员工维度存储，不按项目维度存储。
- `user_token_session` 只做跳转 `state` 绑定、回调防串单和短时状态记录。
- 凭证明文不入队列消息。

### 5.4 权限原则
- 平台与租户共用一套 `MineAdmin` 菜单/按钮权限模型。
- 租户用户只允许进入租户可用模块，平台模块即使误配菜单也要统一拦截。
- MVP 只做项目级和账号级数据权限。
- 不做复杂部门权限。
- 权限链固定为：
  - 先项目授权
  - 再广告主账户授权
  - 再确认可用 `user_token`

### 5.5 腾讯广告拉取与复审原则
- 当前 MVP 不做“批量创建广告主链路”，优先做存量广告资产的拉取、状态识别、编辑和复审。
- 首期接口重点围绕 `adgroups/get`、`dynamic_creatives/get`、`components/get`、`component_detail/get` 和对应审核/复审接口展开。
- 必须把审核状态、审核原因、拒审信息落到本地快照，支持项目维度筛选“未通过 / 待审核 / 已通过”。
- 只允许对“未通过且可编辑字段”开放编辑；字段可编辑性按官方接口约束做白名单控制。
- 编辑提交后必须回写“提交人、提交时间、提交参数摘要、目标对象ID”，并保留编辑前后快照。
- 复审结果通过轮询或定时同步回查，不靠人工刷新页面。
- 接口调用统一做幂等键和限流重试，避免重复提交和状态错乱。

### 5.6 三个对象关系与复杂度（按官方 v3 文档核对）
- `广告`：
  - 官方页面路径：`/v3.0/docs/api/adgroups/get`、`/v3.0/docs/api/adgroups/update`
  - 页面正文实际描述的是“广告”，不是“广告组”
  - 当前可见可编辑字段更偏广告层自身：名称、状态、预算、出价、定向、优化目标等
  - 审核相关字段至少包含：`configured_status`、`system_status`
- `创意`：
  - 官方页面路径：`/v3.0/docs/api/dynamic_creatives/get`、`/v3.0/docs/api/dynamic_creatives/update`
  - 与广告的关系：创意列表可按 `adgroup_id` 过滤
  - 与组件的关系：创意页和更新页大量出现 `component_id`
  - 还依赖：`creative_template_id` / 创意规格详情页 / `page_spec`
  - 编辑复杂度最高，官方更新页参数量远大于广告层
- `创意组件`：
  - 官方页面路径：`/v3.0/docs/api/components/get`、`/v3.0/docs/api/component_detail/get`
  - 组件库存层面返回：`component_id`、`component_value`
  - 组件详情按类型展开到标题、文本、图片、按钮等多种结构
  - 官方 `apilist` 当前可见 `components/add`、`components/delete`，但未见统一 `components/update` 页面，说明组件编辑链路不能简单理解成“一个统一 update 接口”

按当前官方文档可稳定确认的关系链：

```text
广告
-> 通过 adgroup_id 关联创意查询
-> 创意通过 component_id 引用创意组件
-> 创意组件详情再展开具体 component_value
```

补充说明：
- `dynamic_creatives/update` 页面正文明确提示：同一个广告下，创意的新建、更新、删除必须串行执行。
- 同页还明确提示：对于 `creative_components` 相关参数，`component_id` 和 `value` 只需传一个；若同时传入，以 `value` 为准。
- 审核与复审在官方文档里是独立能力，不只是对象 `update`：
  - `dynamic_creative_review_results/get`
  - `component_review_results/get`
  - `element_appeal_review/add|get`
  - `component_element_urge_review/add|get`

### 5.7 功能细节文档约定
为避免后续边做边改，当前开始对 MVP 关键功能采用“一功能一文档”的细节文档方式。

当前细节文档目录：

- `docs/功能设计/功能设计总览.md`
- `docs/功能设计/平台应用管理.md`
- `docs/功能设计/平台应用分配.md`
- `docs/功能设计/广告主账号授权与拉取.md`

后续开发时，仍固定优先阅读以下基础文档：

- `docs/tencent-ads-batch-create-prd.md`
- `docs/tencent-ads-batch-create-tech.md`
- `docs/tencent-ads-batch-create-task-list.md`

进入具体功能实现前，再按需阅读对应功能的细节文档，不要求每次把 `docs/功能设计/` 下所有文档全部读完。

## 6. 异步任务与调度原则
### 6.1 一期主链路
- 拉取与复审链路优先保证同步可用，定时同步再按需要接入 `hyperf/crontab`。
- 如引入异步处理，Job 中只放业务 ID，不放敏感凭证。

### 6.2 导出任务原则（二期）
- 所有导出能力统一创建异步任务，不再在页面请求中直接导出大文件
- 页面只负责提交导出请求、展示任务状态、提供结果下载入口
- 导出结果文件地址、过期时间、失败原因等信息写入 `export_task_detail`
- 任务中心按作用域展示导出记录：
  - 项目导出只在当前项目可见
  - 租户导出在租户范围可见
  - 平台导出仅平台可见

### 6.3 补偿链路
- 使用 `hyperf/crontab`
- 用于：
  - 超时巡检
  - 补偿重试
  - 通知补发

## 7. 近期开发顺序
### 第一步：表设计
输出一期表设计与迁移清单。

### 第二步：租户基础模型
实现：

- 租户
- 员工
- 项目
- 项目成员

### 第三步：账号授权
实现：

- 平台应用管理与租户分配
- OAuth 授权码回调、token 换取与刷新
- 通过 `advertiser/get` 为主、必要时结合 `organization_account_relation/get` 完成广告主账户同步
- 项目绑定广告主账户
- 员工广告主账户授权

### 第四步：凭证获取与执行
实现：

- `user_token_session`
- 执行凭证

### 第五步：广告数据拉取与审核状态同步
实现：

- 拉取广告列表（含审核状态）
- 拉取广告创意列表（含审核状态）
- 拉取创意组件列表（含审核状态）
- 未通过项筛选与状态聚合

### 第六步：编辑与复审闭环
实现：

- 未通过广告创意编辑
- 未通过创意组件编辑
- 再次提交审核
- 复审结果回查与日志审计

### 第七步（二期）：异步任务与批量创建
实现：

- 统一任务中心主表与执行项表
- 批量创建广告任务接入
- `hyperf/async-queue` 执行链路
- 失败重试与补偿链路

## 8. 当前明确不做
- 复杂部门树
- 租户内更多细分角色
- 套餐/计费系统
- 高级模板编排
- 高级 BI 报表
- 复杂风控调度

## 9. 新对话启动建议
新开对话时，先告诉模型：

1. 先读 `docs/tencent-ads-batch-create-prd.md`
2. 再读 `docs/tencent-ads-batch-create-tech.md`
3. 再读 `docs/tencent-ads-batch-create-task-list.md`
4. 如要做表设计、任务中心或导出，再读 `docs/tencent-ads-batch-create-table-design.md`
5. 当前目标是：
   - 做最快落地的 MVP
   - 组织架构采用 `平台 -> 租户 -> 项目 -> 广告主账户 -> 员工授权 -> 执行凭证`
   - 租户能力基于现有 `MineAdmin` 体系实现
   - 平台与租户身份分离
   - 一期先做拉取/编辑/复审，二期接入异步任务与批量创建

## 10. 下一步建议
下一步最值得先做的是：

1. 一期表设计文档
2. 锁定官方 v3 首期接口清单
3. 广告、创意、组件与审核结果本地快照设计
