# 目標 - 自動化營運

1. 每日統計平台業績，EMAIL+LINE通知
2. 每月統計報表，EMAIL+LINE通知
3. 計算好每月要被抽多少錢
4. EMAIL跟LINE附上繳款連結
5. 時間內沒繳款  自動發催繳信跟警告
6. 期限到沒繳款，自動斷網站
7. 第二期限到，自動刪除網站

# 代辦清單

- [ ] FB轉換API
- [ ] 串接電子報
- [ ] 串接歐付寶
- [ ] 串接藍星
- [ ] 簡易模式重購
- [ ] 串接簡訊API
- [X] getButton功能

# 教學

## 1.常數

常數             | 型態  | 預設  | 說明
----------------|:-----:|:-----:|------------------------
DEV_ENV         | bool  | false | 是否為開發環境
COMMENTS_OPEN   | bool  | false | 是否開啟留言功能
PROJECT_OPEN    | bool  | false | 是否開啟Project(作品集/案例)
FLUSH_METABOX   | bool  | false | 是否刷新所有使用者的Metabox設定
ONESHOP         | bool  | false | 是否為啟用一頁電商功能
FA_ENABLE       | bool  | true  | 是否載入FontAwesome資源
JDAIO_EXTENSION | bool  | false | 是否開啟擴充模組功能





## 不要更新的PLUGIN

1. users-customers-import-export-for-wp-woocommerce
因為他的多語系沒有指定text domain，所以更新可能被覆蓋掉
2. Divi Mega Pro

# Change Log

時間            | 版本號 | 說明
----------------|:-----:|------------------------
2021-09-17      | 1.1.0 | 新增getButton、oneShop等功能
