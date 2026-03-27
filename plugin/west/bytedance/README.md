<!--
 * @Date: 2024-10-23 10:23:29
 * @LastEditors: west_ng 457395070@qq.com
 * @LastEditTime: 2024-11-10 15:40:06
 * @FilePath: /bytedance/README.md
-->

<div align="center">
  <h1>精选巨量引擎布局</h1>
</div>

<div align="center">

基于 [MineAdmin 3.0](https://www.mineadmin.com/) 的 前端布局插件。

</div>

# 特性

## 全面

无损开发，不破坏框架源代码，使用更安心！
内置本地化数据保存，让你风格化更风骚。

## 自定义组件

右上角下拉组件均由开发者自行编写，详参考下方说明

# 安装

```sh
php php bin/hyperf.php mine-extension:install west/bytedance --yes
```

# 例子

```typescript
export default defineComponent({
  name: "OcConfig",
  // 案列仅供参考
  setup() {
    // 创建数组
    const data: any[] = [
      {
        id: 1,
        uid: 3598021840864340,
        advertiserId: 1807806434284569,
        shopId: 1800585722406923,
        name: "【蔓越莓】上海***专卖店-4569",
        role: "ROLE_ECP_CHILD_ADVERTISER",
        status: "STATUS_ENABLE",
        licenseNo: 91310113,
        brand: "小店-千川-专营店",
        industry: "休闲零食",
        createdAt: "2024-09-27 12:04:58",
        updatedAt: "2024-10-22 00:00:06",
      },
      {
        id: 2,
        uid: 3598021840864340,
        advertiserId: 1807234900254794,
        shopId: 1800585722406923,
        name: "【软糖】上海***专卖店【主户】4794",
        role: "ROLE_ECP_ADVERTISER",
        status: "STATUS_ENABLE",
        licenseNo: 91310113,
        brand: "小店-千川-专营店",
        industry: "休闲零食",
        createdAt: "2024-09-27 12:04:58",
        updatedAt: "2024-10-22 00:00:06",
      },
    ];

    return () => (
      <div>
        <el-dropdown
          trigger="click"
          v-slots={{
            default: () => (
              <div class="index_header-cc-all">
                【软糖】上海***专卖店【主户】4794
              </div>
            ),
            dropdown: () => (
              <el-dropdown-menu class="w-[258px] overflow-hidden relative">
                {data.map((item: any) => (
                  <el-dropdown-item
                    v-slots={{
                      default: () => (
                        <div class="w-full flex flex-row gap-[10px] overflow-hidden">
                          <el-avatar>A</el-avatar>
                          <div class="flex flex-col justify-between flex-1 w-[148px]">
                            <div class="leading-[18px] text-dark-800 overflow-hidden text-ellipsis whitespace-nowrap">
                              {item.name}
                            </div>
                            <span class="leading-[22px] text-[#999999] overflow-hidden text-ellipsis whitespace-nowrap">
                              ID {item.advertiserId}
                            </span>
                          </div>
                        </div>
                      ),
                    }}
                  />
                ))}
              </el-dropdown-menu>
            ),
          }}
        />
      </div>
    );
  },
});
```

## 开发

1. 在前端目录 src/components 创建 config.tsx 文件。

2. 插件会自动读取你的文件且渲染出来。

## 语言包

```yaml
bytedance:
  configTips: 插件配置内容仅储存在本地，更换设备会造成数据丢失！
  layoutConfig: 布局配置
  title: 插件配置
  backgroundImage: 背景图片
  blurValue: 模糊值
  slogan: 口号标语
  sloganPlaceholder: MineAdmin · 一起探索 运用技术，为公司和品牌创造卓越的价值 · 2024
  upload: 上传
  cancel: 取消
  save: 保存
  description: MineAdmin 3.0布局插件，高度复刻巨量引擎布局
```

# 相关链接

- [MineAdmin 指南](https://doc.mineadmin.com/zh/guide/introduce/mineadmin.html)
- [MineAdmin 前端](https://doc.mineadmin.com/zh/front/base/concept.html)
- [MineAdmin 后端](https://doc.mineadmin.com/zh/backend/)
- [MineAdmin 常见问题](https://doc.mineadmin.com/zh/faq/)

# 界面预览

<img src="https://s21.ax1x.com/2024/10/23/pAdfFrn.md.png" />
<img src="https://s21.ax1x.com/2024/10/23/pAdfiKs.md.png" />
<img src="https://s21.ax1x.com/2024/10/23/pAdfkbq.md.png" />
<img src="https://s21.ax1x.com/2024/10/23/pAdfEV0.md.png" />
