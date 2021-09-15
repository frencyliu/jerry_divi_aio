# 目標 - 自動化營運

1. 每日統計平台業績，EMAIL+LINE通知
2. 每月統計報表，EMAIL+LINE通知
3. 計算好每月要被抽多少錢
4. EMAIL跟LINE附上繳款連結
5. 時間內沒繳款  自動發催繳信跟警告
6. 期限到沒繳款，自動斷網站
7. 第二期限到，自動刪除網站

# 代辦清單

- [ ] 串接電子報
- [ ] 串接歐付寶
- [ ] 串接藍星
- [ ] 簡易模式重購
- [ ] 串接簡訊API


# 教學

## 1.常數

COMMENTS_OPEN - (bool) 是否開啟留言功能

不要更新的PLUGIN：
1. users-customers-import-export-for-wp-woocommerce
因為他的多語系沒有指定text domain，所以更新可能被覆蓋掉