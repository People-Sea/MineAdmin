# 腾讯广告 v3.0 官方文档阅读笔记

## 1. 文档目的
本文档只记录本轮从腾讯广告官方 `developers.e.qq.com/v3.0` 文档页面直接读到的结论，供后续二次复查。

约束：

- 只采信官方 v3.0 文档页面正文
- 不以 SDK 命名作为最终结论来源
- 对未在官方页面明确出现的关系和接口，不做写死结论

## 2. 本轮实际阅读的官方页面

- `https://developers.e.qq.com/v3.0/docs/apilist`
- `https://developers.e.qq.com/v3.0/docs/api/oauth/authorize`
- `https://developers.e.qq.com/v3.0/docs/api/oauth/token`
- `https://developers.e.qq.com/v3.0/docs/api/oauth/refresh_token`
- `https://developers.e.qq.com/v3.0/docs/api/advertiser/get`
- `https://developers.e.qq.com/v3.0/docs/api/organization_account_relation/get`
- `https://developers.e.qq.com/v3.0/docs/api/adgroups/get`
- `https://developers.e.qq.com/v3.0/docs/api/adgroups/update`
- `https://developers.e.qq.com/v3.0/docs/api/dynamic_creatives/get`
- `https://developers.e.qq.com/v3.0/docs/api/dynamic_creatives/update`
- `https://developers.e.qq.com/v3.0/docs/api/components/get`
- `https://developers.e.qq.com/v3.0/docs/api/component_detail/get`
- `https://developers.e.qq.com/v3.0/docs/api/dynamic_creative_review_results/get`
- `https://developers.e.qq.com/v3.0/docs/api/component_review_results/get`
- `https://developers.e.qq.com/v3.0/docs/api/element_appeal_review/add`
- `https://developers.e.qq.com/v3.0/docs/api/component_element_urge_review/add`

## 3. 已确认结论

### 3.1 OAuth 与广告主账号导入链路
- 官方 `apilist` 当前明确列出了：
  - `oauth/authorize`
  - `oauth/token`
  - `oauth/refresh_token`
  - `advertiser/get`
  - `organization_account_relation/get`
- `oauth/authorize` 页写明：
  - 回调会带 `authorization_code` 和 `state`
  - `authorization_code` 有效期 5 分钟
- `oauth/token` 页写明：
  - `grant_type` 支持 `authorization_code` 和 `refresh_token`
  - 返回 `access_token`
  - 返回 `refresh_token`
  - 返回 `access_token_expires_in`
  - 返回 `refresh_token_expires_in`
  - OAuth 相关接口无需提供 `access_token`、`timestamp`、`nonce` 等通用请求参数
- `advertiser/get` 页写明：
  - 当前官方广告主账号列表接口名是 `advertiser/get`
  - 对广告主而言 `account_id` 必填
  - 对代理商而言 `account_id` 选填，未传时默认拉取该代理商下属所有子客信息
- `organization_account_relation/get` 页写明：
  - 页面标题为“查询组织下广告账户信息”
  - 仅支持集团、主体、业务单元 id 的 token 进行操作

结论：

- 广告主账号授权链路不应再写成“扫码回调直接拿到 `userToken`”
- 当前更准确的理解是：

```text
oauth/authorize
-> 回调拿到 authorization_code
-> oauth/token 或 oauth/refresh_token
-> advertiser/get 为主、organization_account_relation/get 为补充
-> 导入广告主账号
```

### 3.2 官方 v3.0 页面中的对象命名
- 广告相关页面当前使用：
  - `adgroups/get`
  - `adgroups/update`
- 创意相关页面当前使用：
  - `dynamic_creatives/get`
  - `dynamic_creatives/update`
- 创意组件相关页面当前使用：
  - `components/get`
  - `component_detail/get`

说明：

- 官方 v3.0 `apilist` 中，广告/创意/组件的命名与旧 SDK 中常见的 `ads / adcreatives / creative_components` 不一致。
- 后续开发时，接口页面路径以官方页面为准，不以历史命名习惯硬映射。

### 3.3 `user_token` 的官方口径
- `dynamic_creatives/update` 页存在特定请求参数 `user_token`
- 页面正文说明：
  - 该参数为实名认证要求参数
  - 调用受限接口时必传
  - 注意不是放在 header 中
  - 获取方式参考“API 身份验证升级公告”中的开发者对接文档

结论：

- `user_token` 是受限接口执行身份参数，不是 OAuth 回调字段
- 现有项目文档不应再把 `user_token` 与 `authorization_code / access_token / refresh_token` 混为一条链路
- 在未拿到实名对接文档明确说明前，不应在项目文档中写死 `user_token` 的有效期

### 3.4 广告页的异常点
- `https://developers.e.qq.com/v3.0/docs/api/adgroups/get` 页面正文标题为“获取广告”
- `https://developers.e.qq.com/v3.0/docs/api/adgroups/update` 页面正文标题为“更新广告”

结论：

- 官方站存在“路径名像广告组，但正文描述是广告”的错位现象。
- 后续分析时，必须同时看页面标题、请求地址、字段表，不能只看 URL 名称。

### 3.5 三个对象的关系
按当前官方页面能稳定确认的关系：

```text
广告
-> 通过 adgroup_id 关联创意查询
-> 创意通过 component_id 引用创意组件
-> 创意组件详情再展开具体 component_value
```

具体依据：

- `dynamic_creatives/get`
  - 过滤字段中明确出现 `adgroup_id`
  - 过滤字段和应答字段中明确大量出现 `component_id`
  - 页面中明确出现 `creative_template_id`
- `components/get`
  - 返回 `component_id`
  - 返回 `component_value`
- `component_detail/get`
  - 按 `component_id`、`component_type` 等维度查询组件详情
  - 组件详情展开到标题、文本、图片、按钮等不同结构

### 3.6 广告层结论
从 `adgroups/get`、`adgroups/update` 正文可确认：

- 广告层重点是广告自身配置：
  - 广告名称
  - 客户设置状态 `configured_status`
  - 系统状态 `system_status`
  - 出价
  - 日预算
  - 定向
  - 优化目标
- 本轮阅读中，没有在广告页正文里直接看到一个稳定清晰的 `adcreative_id` 字段说明

因此：

- 不能仅凭广告页正文，就把“广告直接返回创意 ID”写成确定规则
- 广告与创意的稳定关系，应先按“创意页可按 `adgroup_id` 查询”理解

### 3.7 创意层结论
从 `dynamic_creatives/get`、`dynamic_creatives/update` 正文可确认：

- 创意列表可按 `adgroup_id` 过滤
- 创意列表与更新页均大量出现 `component_id`
- 创意强依赖：
  - `creative_template_id`
  - 创意规格页面
  - `page_spec`
- 创意更新页字段量非常大，远高于广告层

官方页面中还明确写了两个高风险约束：

- 同一个广告下，创意的新建、更新、删除必须串行执行
- 对 `creative_components` 相关参数，`component_id` 和 `value` 只需传一个；若同时传入，以 `value` 为准

结论：

- 创意层是当前一期 MVP 中最复杂的编辑层
- “编辑创意”不能理解成只改文案标题，它实际上会影响组件引用、页面结构和规格约束

### 3.8 创意组件层结论
从 `components/get`、`component_detail/get` 正文可确认：

- 组件库存页支持按以下维度过滤：
  - `component_id`
  - `component_type`
  - `component_sub_type`
  - `generation_type`
  - `potential_status`
  - `first_publication_status`
  - `similarity_status`
  - `scene`
- 返回结构核心是：
  - `component_id`
  - `component_value`
- `component_value` 再根据组件类型展开：
  - 标题
  - 文本
  - 图片
  - 按钮
  - 其他组件结构

结论：

- 创意组件不是一个扁平对象，而是一个“组件类型 + 组件值结构”的体系
- 组件详情页是理解可编辑范围的重要页面，不能只看列表页
- `apilist` 当前还明确存在 `components/add` 与 `components/delete`
- 但本轮未看到统一 `components/update` 页面，因此不能把组件编辑直接写成“标准 update 模式”

## 4. 审核、复审、催审链路

### 4.1 创意审核结果
从 `dynamic_creative_review_results/get` 正文可确认：

- 按 `dynamic_creative_id_list` 查询
- 返回字段至少包含：
  - `review_status`
  - `reason`
  - `reject_message`
  - `element_reject_detail_info`
  - `reject_info_location`

结论：

- 创意审核结果是独立查询接口，不应只靠创意列表页读取
- 拒审信息已细化到元素级定位

### 4.2 组件审核结果
从 `component_review_results/get` 正文可确认：

- 按 `component_id_list` 查询
- 返回字段至少包含：
  - `review_status`
  - `reason`
  - `element_reject_detail_info`
  - `reject_info_location`

结论：

- 组件审核结果同样是独立查询接口
- 组件拒审信息也细化到了元素级

### 4.3 元素申诉复审
从 `element_appeal_review/add` 正文可确认：

- 申诉复审粒度不是“广告整体”，而是元素级
- 必填字段至少包括：
  - `dynamic_creative_id`
  - `component_id`
  - `element_id`
  - `element_type`
  - `element_value`
  - `element_finger_print`
  - `appeal_demand`
  - `appeal_reason`
- 可选字段：
  - `history_approval_component_id`

结论：

- 复审不是简单“重新提交整个广告/创意”
- 官方设计明显偏向“针对被拒元素逐条申诉”

### 4.4 组件元素催审
从 `component_element_urge_review/add` 正文可确认：

- 支持两种催审维度：
  - `URGE_DIMENSION_COMPONENT`
  - `URGE_DIMENSION_ELEMENT`
- 当维度为组件时，值为 `component_id`
- 当维度为元素时，值为 `element_fingerprint`

结论：

- 催审链路也不是广告整体粒度
- 组件与元素是两个不同催审维度

## 5. 当前最重要的判断

- 一期 MVP 若做“拉取 + 编辑 + 复审”，主难点不在广告层，而在创意层
- 创意层复杂度来源不是单个字段多，而是它同时连接：
  - 广告
  - 组件
  - 创意规格
  - 页面结构
  - 审核结果
  - 元素级复审
- 组件层本身也不轻，但它更像“结构化资产层”
- 广告层相对更偏投放配置层

## 6. 当前未确认项

- 官方 `apilist` 中没有直接看到 `components/update` 页面
- 因此当前不能写死“组件编辑一定存在独立统一 update 接口”
- 当前更稳妥的理解是：
  - 组件读取依赖 `components/get` + `component_detail/get`
  - 组件复审依赖审核结果、元素申诉复审、组件/元素催审
  - 组件内容修改是否完全独立，还需要继续查官方页面

## 7. 后续复查建议

下一轮如果继续复查，优先看以下官方页面：

- `dynamic_creatives/update`
- `component_detail/get`
- `dynamic_creative_review_results/get`
- `component_review_results/get`
- `element_appeal_review/add`
- `component_element_urge_review/add`
- `creative_template/get`

## 8. 记录时间

- 记录时间：2026-04-02
