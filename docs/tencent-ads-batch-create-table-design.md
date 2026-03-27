# 风起（批量广告投放系统）一期表设计（MVP 草案）

## 1. 文档目的
本文档用于补充 MVP 的一期表设计口径，当前优先把“统一任务中心 + 所有导出统一入任务中心”这部分定清楚，避免后面做迁移时反复改表。

当前约束：

- 产品入口只有一个任务中心
- 所有导出都必须进入任务中心
- 底层不用一张万能大表，采用“统一任务主表 + 可选执行项表 + 类型明细表”
- 广告投放类任务必须属于项目
- 平台级、租户级、项目级任务允许共存

## 2. MVP 核心表清单
### 2.1 平台与租户基础
- `tenant`
- `platform_app`
- `tenant_app_assignment`
- `tenant_project`
- `user`（复用现有表，补 `tenant_id`、`last_project_id`）
- `tenant_project_user`

### 2.2 账号与授权
- `tenant_ad_account`
- `tenant_project_ad_account`
- `tenant_member_ad_account`

### 2.3 素材与模板
- `project_material`
- `project_template`

### 2.4 认证与执行身份
- `auth_session`
- `user_token`

### 2.5 任务中心
- `async_task`
- `async_task_item`
- `async_task_log`
- `ad_batch_task_detail`
- `export_task_detail`

## 3. 任务中心总设计
### 3.1 当前任务范围
当前任务中心统一承接两类异步任务：

- 广告批量创建、重试这类执行任务
- 报表导出、任务日志导出、账号数据导出这类文件任务

任务中心统一后，系统需要满足：

- 页面入口统一
- 状态口径统一
- 权限过滤统一
- 下载记录、失败原因、审计日志统一可查

当前方案是：

- 产品入口统一为一个任务中心
- 底层统一使用一张任务主表承接公共状态与检索字段
- 业务差异通过执行项表和类型明细表承接

### 3.2 当前结构原则
- `async_task` 只保存公共查询、状态、归属、执行链路字段
- 需要拆分执行单元的任务使用 `async_task_item`
- 广告批量任务使用 `ad_batch_task_detail`
- 导出任务使用 `export_task_detail`
- 通用状态变化和执行摘要统一写入 `async_task_log`

### 3.3 推荐模型
```text
async_task
├── async_task_item         可选，广告任务等需要拆执行项时使用
├── async_task_log          通用任务日志
├── ad_batch_task_detail    广告批量任务明细
└── export_task_detail      导出任务明细
```

## 4. 作用域设计
任务归属统一使用 `scope_type + scope_id` 表达：

| scope_type | 说明 | scope_id |
| --- | --- | --- |
| `platform` | 平台级任务 | `null` |
| `tenant` | 租户级任务 | `tenant_id` |
| `project` | 项目级任务 | `project_id` |

规则如下：

- 广告投放类任务必须为 `project`
- 项目导出任务使用 `project`
- 租户汇总导出任务使用 `tenant`
- 平台后台导出任务使用 `platform`

补充约束：

- `tenant_id` 对平台级任务可为空，对租户级和项目级任务必填
- `project_id` 只在项目级任务中必填
- 广告任务除了 `scope_type=project`，仍保留显式 `project_id` 字段，便于项目上下文查询

## 5. 主表：`async_task`
主表只放公共查询、状态、归属、执行链路字段。

建议字段：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `id` | bigint | 主键 |
| `task_no` | varchar(64) | 任务编号，给用户和日志定位使用 |
| `tenant_id` | bigint nullable | 租户 ID |
| `project_id` | bigint nullable | 项目 ID |
| `scope_type` | varchar(16) | `platform / tenant / project` |
| `scope_id` | bigint nullable | 作用域对象 ID |
| `task_type` | varchar(64) | 任务类型 |
| `task_name` | varchar(255) | 展示名称 |
| `source_module` | varchar(64) | 来源模块，如 `task_center`、`report`、`ad_account` |
| `status` | varchar(32) | 任务状态 |
| `operator_user_id` | bigint | 发起人 |
| `auth_user_id` | bigint nullable | 实际扫码执行人，导出任务可为空 |
| `auth_session_id` | bigint nullable | 认证会话 ID |
| `user_token_id` | bigint nullable | 执行凭证 ID |
| `total_count` | int default 0 | 总执行数 |
| `success_count` | int default 0 | 成功数 |
| `failed_count` | int default 0 | 失败数 |
| `retry_count` | int default 0 | 已重试次数 |
| `status_reason` | varchar(500) nullable | 当前失败或阻塞原因摘要 |
| `started_at` | datetime nullable | 开始时间 |
| `finished_at` | datetime nullable | 完成时间 |
| `created_at` | datetime | 创建时间 |
| `updated_at` | datetime | 更新时间 |

建议索引：

- `uk_task_no (task_no)`
- `idx_scope_created (scope_type, scope_id, created_at)`
- `idx_tenant_project_created (tenant_id, project_id, created_at)`
- `idx_task_type_status_created (task_type, status, created_at)`
- `idx_operator_created (operator_user_id, created_at)`

建议任务类型：

- `ad_batch_create`
- `ad_batch_retry`
- `export_report`
- `export_task_log`
- `export_ad_account`
- `export_material`

建议主任务状态：

- `pending`
- `waiting_auth`
- `queued`
- `running`
- `partial_success`
- `success`
- `failed`
- `canceled`

## 6. 执行项表：`async_task_item`
不是所有任务都要拆执行项。

建议只在以下场景启用：

- 一次广告任务要拆成多个广告账户或多个子单元执行
- 后续确实需要把大导出切成分片任务

建议字段：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `id` | bigint | 主键 |
| `task_id` | bigint | 归属主任务 |
| `tenant_id` | bigint nullable | 冗余租户 ID，便于筛选 |
| `project_id` | bigint nullable | 冗余项目 ID，便于筛选 |
| `item_no` | varchar(64) | 执行项编号 |
| `item_type` | varchar(64) | 执行项类型 |
| `ref_id` | bigint nullable | 关联业务对象 ID，如广告主账号 ID |
| `ref_name` | varchar(255) nullable | 关联业务对象名称 |
| `status` | varchar(32) | 执行项状态 |
| `attempt_count` | int default 0 | 尝试次数 |
| `status_reason` | varchar(500) nullable | 失败原因摘要 |
| `payload_snapshot` | json nullable | 执行项快照 |
| `result_snapshot` | json nullable | 结果快照 |
| `started_at` | datetime nullable | 开始时间 |
| `finished_at` | datetime nullable | 完成时间 |
| `created_at` | datetime | 创建时间 |
| `updated_at` | datetime | 更新时间 |

建议执行项状态：

- `pending`
- `queued`
- `running`
- `success`
- `failed`
- `skipped`

## 7. 任务日志表：`async_task_log`
任务日志统一承接任务状态变化、执行摘要、补偿记录，不和业务操作日志混在一起。

建议字段：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `id` | bigint | 主键 |
| `task_id` | bigint | 主任务 ID |
| `item_id` | bigint nullable | 执行项 ID |
| `tenant_id` | bigint nullable | 租户 ID |
| `project_id` | bigint nullable | 项目 ID |
| `level` | varchar(16) | `info / warning / error` |
| `action` | varchar(64) | 如 `created`、`queued`、`retry`、`completed` |
| `message` | varchar(1000) | 日志内容 |
| `context` | json nullable | 结构化上下文 |
| `operator_user_id` | bigint nullable | 操作人 |
| `created_at` | datetime | 创建时间 |

## 8. 广告任务明细：`ad_batch_task_detail`
这张表只服务广告批量任务，不让广告业务字段污染主表。

建议字段：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `id` | bigint | 主键 |
| `task_id` | bigint unique | 对应 `async_task.id` |
| `tenant_id` | bigint | 租户 ID |
| `project_id` | bigint | 项目 ID |
| `template_id` | bigint nullable | 使用的模板 ID |
| `material_snapshot` | json nullable | 素材快照 |
| `account_snapshot` | json nullable | 广告主账号快照 |
| `task_payload` | json | 广告任务提交参数快照 |
| `created_at` | datetime | 创建时间 |
| `updated_at` | datetime | 更新时间 |

说明：

- MVP 阶段优先保留快照，不急着把所有广告参数拆成很多列
- 真正高频筛选字段仍应回到主表或业务实体表做

## 9. 导出任务明细：`export_task_detail`
所有导出都走这张明细表，不直接沿用页面同步导出流程。

建议字段：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `id` | bigint | 主键 |
| `task_id` | bigint unique | 对应 `async_task.id` |
| `tenant_id` | bigint nullable | 租户 ID |
| `project_id` | bigint nullable | 项目 ID |
| `export_type` | varchar(64) | 导出类型 |
| `filter_snapshot` | json nullable | 导出筛选条件快照 |
| `file_disk` | varchar(32) nullable | 文件存储磁盘 |
| `file_path` | varchar(500) nullable | 文件路径 |
| `file_name` | varchar(255) nullable | 下载文件名 |
| `file_size` | bigint default 0 | 文件大小 |
| `row_count` | int default 0 | 导出行数 |
| `download_count` | int default 0 | 下载次数 |
| `expired_at` | datetime nullable | 文件过期时间 |
| `last_downloaded_at` | datetime nullable | 最近下载时间 |
| `created_at` | datetime | 创建时间 |
| `updated_at` | datetime | 更新时间 |

建议导出类型：

- `report_export`
- `task_log_export`
- `ad_account_export`
- `material_export`
- `member_export`

## 10. 状态流转建议
### 10.1 广告批量任务
```text
pending
-> waiting_auth
-> queued
-> running
-> success / partial_success / failed / canceled
```

### 10.2 导出任务
```text
pending
-> queued
-> running
-> success / failed / canceled
```

补充规则：

- `success` 才允许提供下载地址
- `failed` 必须回填 `status_reason`
- `partial_success` 仅广告类任务使用

## 11. 与 MineAdmin 导出能力的关系
MVP 阶段，导出能力统一由任务中心承接。

当前接入方式：

- 可以复用 MineAdmin 或现有导出组件做文件生成
- 但导出申请、权限校验、异步执行、状态展示、下载入口统一走任务中心
- 页面点击“导出”时，只负责创建一条 `async_task + export_task_detail`

这样做的好处是：

- 前台交互统一
- 大文件导出不会阻塞请求
- 后续加导出审计、导出过期、导出通知都更自然

## 12. 实施顺序建议
如果要尽快先落地导出任务中心，建议顺序如下：

1. 先建 `async_task`
2. 再建 `async_task_log`
3. 再建 `export_task_detail`
4. 页面导出统一改为“创建任务 -> 去任务中心查看结果”
5. 广告批量任务接入时再补 `async_task_item` 和 `ad_batch_task_detail`

如果准备一次性把广告任务和导出任务都接进来，也可以五张表一起建。
